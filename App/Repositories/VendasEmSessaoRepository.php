<?php

namespace App\Repositories;

use App\Models\Produto;
use System\Session\Session;

/**
 * Repositorio controla produtos na sessao que sao usados em vendas e pedidos
 */
class VendasEmSessaoRepository
{
    private const SESSION_KEY = 'venda';

    public function colocarProdutosNaMesa($idProduto, $quantidade = false)
    {
        $this->normalizarSessaoVenda();
        $venda = $this->obterVendaAtual();

        if ($idProduto) {
            if (!$quantidade) {
                $quantidade = 1;
            }

            $produto = (new Produto())->find($idProduto);

            if (!$produto) {
                return json_encode(['status' => false, 'message' => 'Produto nao encontrado']);
            }

            if (!isset($venda[$idProduto])) {
                $venda[$idProduto] = [
                    'id' => (int) $idProduto,
                    'produto' => $produto->nome,
                    'preco' => (float) $produto->preco,
                    'imagem' => $produto->imagem,
                    'quantidade' => (int) $quantidade,
                    'total' => (float) $produto->preco * (float) $quantidade,
                ];
            }
        }

        $this->salvarVendaAtual($venda);

        return json_encode([
            'status' => true,
            'item' => $this->ultimoProdutoDaMesa(),
            'itens' => array_values($this->obterVendaAtual()),
        ]);
    }

    public function obterProdutosDaMesa($posicaoProduto = false)
    {
        $this->normalizarSessaoVenda();
        $venda = $this->obterVendaAtual();

        if (empty($venda)) {
            return json_encode([]);
        }

        if ($posicaoProduto && $posicaoProduto == 'ultimo') {
            return json_encode($this->ultimoProdutoDaMesa());
        }

        return json_encode(array_values($venda));
    }

    public function idDosProdutosNaMesa()
    {
        $this->normalizarSessaoVenda();
        $ids = [];

        foreach ($this->obterVendaAtual() as $produto) {
            $ids[] = $produto['id'];
        }

        return $ids;
    }

    public function alterarAquantidadeDeUmProdutoNaMesa($idProduto, $quantidade)
    {
        $this->normalizarSessaoVenda();
        $venda = $this->obterVendaAtual();

        if (isset($venda[$idProduto])) {
            $venda[$idProduto]['quantidade'] = (int) $quantidade;
            $venda[$idProduto]['total'] = (float) $quantidade * (float) $venda[$idProduto]['preco'];
            $this->salvarVendaAtual($venda);
        }
    }

    public function retirarProdutoDaMesa($idProduto)
    {
        $this->normalizarSessaoVenda();
        $venda = $this->obterVendaAtual();

        if (isset($venda[$idProduto])) {
            unset($venda[$idProduto]);
            $this->salvarVendaAtual($venda);
        }
    }

    public function obterValorTotalDosProdutosNaMesa()
    {
        $this->normalizarSessaoVenda();
        $total = 0;

        foreach ($this->obterVendaAtual() as $produto) {
            $total += (float) $produto['total'];
        }

        return json_encode(['total' => $total]);
    }

    public function limparSessao()
    {
        Session::delete(self::SESSION_KEY);
    }

    public function colocarProdutosVindosDoBancoDeDadosNaMesa($produto)
    {
        $venda = $this->obterVendaAtual();

        $venda[$produto->id] = [
            'id' => (int) $produto->id,
            'produto' => $produto->produto,
            'preco' => (float) $produto->preco,
            'imagem' => $produto->imagem,
            'quantidade' => (int) $produto->quantidade,
            'total' => (float) $produto->total,
            'id_pedido' => $produto->id_pedido,
        ];

        $this->salvarVendaAtual($venda);
    }

    public function calcularTroco($valorRecebido)
    {
        $valorTotalDosProdutosSelecionados = (double) json_decode($this->obterValorTotalDosProdutosNaMesa())->total;
        $valorRecebido = (double) $valorRecebido;

        if ($valorRecebido < $valorTotalDosProdutosSelecionados) {
            return json_encode(['valor' => 0.00, 'message' => 'O valor pago e insuficiente.']);
        }

        $troco = (double) $valorRecebido - $valorTotalDosProdutosSelecionados;
        return json_encode(['valor' => $troco, 'message' => false]);
    }

    public function possuiItensNaVenda(): bool
    {
        $this->normalizarSessaoVenda();
        return !empty($this->obterVendaAtual());
    }

    private function normalizarSessaoVenda(): void
    {
        $venda = Session::get(self::SESSION_KEY);

        if (!is_array($venda)) {
            $this->salvarVendaAtual([]);
            return;
        }

        foreach ($venda as $id => $item) {
            if (!$this->itemDaVendaValido($item)) {
                unset($venda[$id]);
                continue;
            }

            $venda[$id]['id'] = (int) $item['id'];
            $venda[$id]['produto'] = (string) $item['produto'];
            $venda[$id]['preco'] = (float) $item['preco'];
            $venda[$id]['quantidade'] = (int) $item['quantidade'];
            $venda[$id]['total'] = (float) $item['total'];
            $venda[$id]['imagem'] = $item['imagem'] ?? null;
        }

        $this->salvarVendaAtual($venda);
    }

    private function itemDaVendaValido($item): bool
    {
        if (!is_array($item)
            || !isset($item['id'], $item['produto'], $item['preco'], $item['quantidade'], $item['total'])) {
            return false;
        }

        $id = filter_var($item['id'], FILTER_VALIDATE_INT);
        $quantidade = filter_var($item['quantidade'], FILTER_VALIDATE_INT);
        $preco = filter_var($item['preco'], FILTER_VALIDATE_FLOAT);
        $total = filter_var($item['total'], FILTER_VALIDATE_FLOAT);
        $produto = trim((string) $item['produto']);

        if ($id === false || $id <= 0) {
            return false;
        }

        if ($quantidade === false || $quantidade <= 0) {
            return false;
        }

        if ($preco === false || $total === false) {
            return false;
        }

        if ($produto === '' || strtolower($produto) === 'undefined') {
            return false;
        }

        return true;
    }

    private function ultimoProdutoDaMesa(): ?array
    {
        $venda = $this->obterVendaAtual();

        if (empty($venda)) {
            return null;
        }

        $itens = array_values($venda);
        return end($itens) ?: null;
    }

    private function obterVendaAtual(): array
    {
        $venda = Session::get(self::SESSION_KEY);
        return is_array($venda) ? $venda : [];
    }

    private function salvarVendaAtual(array $venda): void
    {
        Session::set(self::SESSION_KEY, $venda);
    }
}
