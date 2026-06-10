<?php

namespace App\Services\RelatorioPDFService;

use Dompdf\Dompdf;

class GerarRelatorioDeVendasPorPeriodoPDFService
{
    protected $titulo;
    protected $nomeDoArquivo;
    protected $diretorio;
    protected $periodo = [];
    protected $empresa;
    protected $totalVendas;

    public function setTotalVendas($totalVendas)
    {
        $this->totalVendas = $totalVendas;
    }

    public function getTotalVendas()
    {
        return $this->totalVendas;
    }

    public function setEmpresa($empresa)
    {
        $this->empresa = $empresa;
    }

    public function getEmpresa()
    {
        return $this->empresa;
    }

    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    }

    public function setNomeDoArquivo($nomeDoArquivo)
    {
        $this->nomeDoArquivo = $nomeDoArquivo . '.pdf';
    }

    public function setPeriodo(array $periodo)
    {
        $this->periodo = $periodo;
    }

    public function setDiretorio($diretorio)
    {
        $this->diretorio = $diretorio;
    }

    public function gerarPDF($vendas)
    {
        $titulo = "Relatorio de vendas por periodo. {$this->periodo['de']} a {$this->periodo['ate']}";
        $head = ['#', 'Usuario', 'Produto', 'Preco', 'QTD', 'Total', 'PAG', 'Data'];
        $dados = [];
        $resumo = $this->gerarResumo($vendas);
        $graficos = $this->gerarGraficos($vendas);
        $produtosEspecificacoes = $this->gerarEspecificacoesDosProdutos($vendas);

        $i = 0;
        foreach ($vendas as $venda) {
            $i++;
            $dados[] = [
                $i,
                stringAbreviation(ucfirst((string) ($venda->nomeUsuario ?? 'Nao informado')), 14, '...'),
                stringAbreviation(ucfirst((string) ($venda->produtoNome ?? 'Nao informado')), 18, '...'),
                ((float) ($venda->preco ?? 0) > 0) ? 'R$ ' . number_format((float) $venda->preco, 2, ',', '.') : 'Nao consta',
                (!is_null($venda->quantidade) && $venda->quantidade !== '') ? $venda->quantidade : 'Nao consta',
                'R$ ' . number_format((float) ($venda->valor ?? 0), 2, ',', '.'),
                (string) ($venda->legenda ?? 'Nao informado'),
                (string) (($venda->data ?? '') . ' ' . ($venda->hora ?? ''))
            ];
        }

        $dompdf = new Dompdf();
        $file = __DIR__ . '/../../Views/relatorio/relatorioPDF.php';
        define('DOMPDF_ENABLE_PHP', true);
        $dompdf->set_option('chroot', $file);

        ob_start();
        include($file);
        $file = ob_get_clean();

        $dompdf->load_html($file);
        $dompdf->render();

        return $dompdf->stream($this->nomeDoArquivo);
    }

    private function gerarResumo(array $vendas)
    {
        $quantidadeVendas = count($vendas);
        $quantidadeItens = 0;
        $total = (float) ($this->getTotalVendas() ?? 0);
        $produtosUnicos = [];

        foreach ($vendas as $venda) {
            $qtd = is_numeric($venda->quantidade ?? null) ? (float) $venda->quantidade : 1;
            $quantidadeItens += $qtd;

            if (!empty($venda->idProduto)) {
                $produtosUnicos[$venda->idProduto] = true;
            }
        }

        return [
            'quantidadeVendas' => $quantidadeVendas,
            'quantidadeItens' => $quantidadeItens,
            'produtosUnicos' => count($produtosUnicos),
            'ticketMedio' => $quantidadeVendas > 0 ? ($total / $quantidadeVendas) : 0,
        ];
    }

    private function gerarGraficos(array $vendas)
    {
        $porMeioPagamento = [];
        $porProduto = [];

        foreach ($vendas as $venda) {
            $valor = (float) ($venda->valor ?? 0);
            $meio = (string) ($venda->legenda ?? 'Nao informado');
            $produto = trim((string) ($venda->produtoNome ?? 'Produto nao identificado'));

            if (!isset($porMeioPagamento[$meio])) {
                $porMeioPagamento[$meio] = 0.0;
            }
            $porMeioPagamento[$meio] += $valor;

            if (!isset($porProduto[$produto])) {
                $porProduto[$produto] = 0.0;
            }
            $porProduto[$produto] += $valor;
        }

        arsort($porMeioPagamento);
        arsort($porProduto);

        $porMeioPagamento = $this->normalizarGrafico($porMeioPagamento, 6);
        $porProduto = $this->normalizarGrafico($porProduto, 7);

        return [
            'meiosPagamento' => $porMeioPagamento,
            'topProdutos' => $porProduto,
        ];
    }

    private function normalizarGrafico(array $dados, $limite = 6)
    {
        $dados = array_slice($dados, 0, (int) $limite, true);
        $maior = 0;
        foreach ($dados as $valor) {
            if ($valor > $maior) {
                $maior = $valor;
            }
        }

        $saida = [];
        foreach ($dados as $legenda => $valor) {
            $saida[] = [
                'legenda' => stringAbreviation((string) $legenda, 28, '...'),
                'valor' => $valor,
                'valorFormatado' => 'R$ ' . number_format((float) $valor, 2, ',', '.'),
                'percentual' => $maior > 0 ? round(($valor / $maior) * 100, 2) : 0,
            ];
        }

        return $saida;
    }

    private function gerarEspecificacoesDosProdutos(array $vendas)
    {
        $produtos = [];

        foreach ($vendas as $venda) {
            $chave = !empty($venda->idProduto) ? (string) $venda->idProduto : ('sem_id_' . md5((string) ($venda->produtoNome ?? '')));
            if (!isset($produtos[$chave])) {
                $produtos[$chave] = [
                    'nome' => trim((string) ($venda->produtoNome ?? 'Nao informado')),
                    'codigo' => trim((string) ($venda->produtoCodigo ?? '')),
                    'descricao' => trim(strip_tags((string) ($venda->produtoDescricao ?? ''))),
                    'precoBase' => (float) ($venda->preco ?? 0),
                    'quantidadeVendida' => 0,
                    'valorTotal' => 0.0,
                ];
            }

            $produtos[$chave]['quantidadeVendida'] += is_numeric($venda->quantidade ?? null) ? (float) $venda->quantidade : 1;
            $produtos[$chave]['valorTotal'] += (float) ($venda->valor ?? 0);
        }

        usort($produtos, function ($a, $b) {
            if ($a['valorTotal'] === $b['valorTotal']) {
                return 0;
            }
            return ($a['valorTotal'] > $b['valorTotal']) ? -1 : 1;
        });

        return array_slice($produtos, 0, 15);
    }
}
