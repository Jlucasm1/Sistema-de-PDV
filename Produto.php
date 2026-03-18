<?php

namespace App\Models;

use System\Model\Model;

class Produto extends Model
{
    protected string $table      = 'produtos';
    protected bool   $timestamps = true;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Produtos da empresa (com filtro de nome opcional)
     */
    public function produtos(int $idEmpresa, string|bool|null $nome = false): array
    {
        $whereNome = $nome ? "AND nome LIKE '%" . addslashes($nome) . "%'" : '';
        return $this->query("
            SELECT * FROM produtos
            WHERE id_empresa = {$idEmpresa}
            AND deleted_at IS NULL {$whereNome}
            ORDER BY nome
        ");
    }

    /**
     * Produtos visíveis no PDV
     */
    public function produtosNoPdv(int $idEmpresa, string|bool $nome = false): array
    {
        $whereNome = $nome ? "AND nome LIKE '%" . addslashes($nome) . "%'" : '';
        return $this->query("
            SELECT * FROM produtos
            WHERE id_empresa = {$idEmpresa}
            AND deleted_at IS NULL
            AND mostrar_em_vendas = 1
            {$whereNome}
            ORDER BY nome
        ");
    }

    /**
     * Busca por código de barras no PDV
     */
    public function produtosNoPdvFiltrarPorCodigoDeBarra(int $idEmpresa, string|bool $codigo = false): array
    {
        $whereCodigo = $codigo ? "AND codigo LIKE '%" . addslashes($codigo) . "%'" : '';
        return $this->query("
            SELECT * FROM produtos
            WHERE id_empresa = {$idEmpresa}
            AND deleted_at IS NULL
            AND mostrar_em_vendas = 1
            {$whereCodigo}
        ");
    }

    /**
     * Busca produto por coluna específica
     */
    public function getBy(int $idEmpresa, string $column = null, mixed $value = null): array
    {
        $sql  = "SELECT * FROM produtos WHERE id_empresa = :id_empresa AND deleted_at IS NULL";
        $data = [':id_empresa' => $idEmpresa];

        if ($column !== null && $value !== null) {
            $sql  .= " AND {$column} = :colval";
            $data[':colval'] = $value;
        }

        return $this->prepare($sql, $data);
    }

    public function quantidadeDeProdutosCadastrados(int $idEmpresa): object
    {
        $ativos   = $this->queryGetOne("SELECT COUNT(*) qtd FROM produtos WHERE id_empresa = {$idEmpresa} AND deleted_at IS NULL");
        $inativos = $this->queryGetOne("SELECT COUNT(*) qtd FROM produtos WHERE id_empresa = {$idEmpresa} AND deleted_at IS NOT NULL");
        return (object)['ativos' => $ativos->qtd ?? 0, 'inativos' => $inativos->qtd ?? 0];
    }

    public function informacaoesGeraisDosProdutos(int $idEmpresa): object
    {
        return $this->queryGetOne("
            SELECT MAX(preco) AS maisCaro, MIN(preco) AS maisBarato
            FROM produtos WHERE id_empresa = {$idEmpresa} AND deleted_at IS NULL
        ");
    }

    /**
     * Decrementa estoque após venda
     */
    public function decrementaQuantidadeProduto(int $idProduto, int $quantidadeVendida): void
    {
        $produto = $this->find($idProduto);
        if ($produto && $produto->ativar_quantidade && $quantidadeVendida <= $produto->quantidade) {
            $nova = $produto->quantidade - $quantidadeVendida;
            $this->update(['quantidade' => $nova], $idProduto);
        }
    }
}
