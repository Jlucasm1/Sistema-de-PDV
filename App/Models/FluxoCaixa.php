<?php

namespace App\Models;

use System\Model\Model;
use App\Repositories\RelatorioVendasPorPeriodoRepository;

class FluxoCaixa extends Model
{
    protected string $table = 'fluxo_caixa';
    protected bool $timestamps = true;
    protected $retirarPDVdoFluxoDeCaixa = false;
    protected $vendas;

    const TIPO_PERIODO_LANCAMENTO = "lancamento";
    const TIPO_PERIODO_VENCIMENTO = "vencimento";

    public function __construct()
    {
        $this->vendas = new RelatorioVendasPorPeriodoRepository();
        parent::__construct();
    }

    public function fluxoDeCaixa(array $periodo, $idEmpresa)
    {
        $de  = $periodo['de'];
        $ate = $periodo['ate'];

        // Garante objeto padrão com zeros — evita Undefined property em qualquer cenário
        $query = (object) ['entradas' => 0, 'saidas' => 0, 'restante' => 0];
        if ((int) $idEmpresa <= 0) {
            return $query;
        }

        try {
            // Busca entradas e saídas separadamente para evitar GROUP BY vazio
            $entradas = $this->queryGetOne(
                "SELECT COALESCE(SUM(valor), 0) AS total
                 FROM fluxo_caixa
                 WHERE tipo_movimento = 1
                   AND id_empresa = {$idEmpresa}
                   AND DATE(created_at) BETWEEN '{$de}' AND '{$ate}'
                   AND deleted_at IS NULL"
            );

            $saidas = $this->queryGetOne(
                "SELECT COALESCE(SUM(valor), 0) AS total
                 FROM fluxo_caixa
                 WHERE tipo_movimento = 0
                   AND id_empresa = {$idEmpresa}
                   AND DATE(created_at) BETWEEN '{$de}' AND '{$ate}'
                   AND deleted_at IS NULL"
            );

            $query->entradas = (float) ($entradas->total ?? 0);
            $query->saidas   = (float) ($saidas->total   ?? 0);
            $query->restante = $query->entradas - $query->saidas;

        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        // Vendas vindas do PDV
        $vendas = $this->vendas->totalDasVendas($periodo, false, $idEmpresa);

        // Se marcado para retirar PDV do Fluxo de Caixa
        if ($this->getRetirarPDV()) {
            $vendas = null;
        }

        // Incorpora vendas do PDV ao saldo de entradas
        if (!is_null($vendas)) {
            $query->entradas       += (float) $vendas;
            $query->restante        = $query->entradas - $query->saidas;
            $query->entradasVendas  = (float) $vendas;
        }

        return $query;
    }

    public function fluxoDeCaixaDetalhadoPorMes(array $periodo, $tipoPeriodo, $idEmpresa)
    {
        $de  = $periodo['de'];
        $ate = $periodo['ate'];

        if ((int) $idEmpresa <= 0) {
            return [];
        }

        $queryTipoPeriodo = "DATE(created_at)";
        if ($tipoPeriodo == self::TIPO_PERIODO_VENCIMENTO) {
            $queryTipoPeriodo = "DATE(data)";
        }

        $query = $this->query(
            "SELECT * FROM fluxo_caixa
             WHERE id_empresa = {$idEmpresa}
               AND {$queryTipoPeriodo} BETWEEN '{$de}' AND '{$ate}'
               AND deleted_at IS NULL"
        );

        // Se marcado para retirar PDV do Fluxo de Caixa
        if ($this->getRetirarPDV()) {
            return $query;
        }

        $vendas = $this->vendas->totalDasVendas($periodo, false, $idEmpresa);
        if (count($query) > 0 || !is_null($vendas)) {
            $vendaDoPdv                = new \StdClass;
            $vendaDoPdv->id            = 0;
            $vendaDoPdv->id_empresa    = $idEmpresa;
            $vendaDoPdv->id_categoria  = 1;
            $vendaDoPdv->descricao     = 'Total vendas PDV';
            $vendaDoPdv->data          = timestamp();
            $vendaDoPdv->valor         = $vendas ?? 0;
            $vendaDoPdv->tipo_movimento = 1;
            $vendaDoPdv->created_at    = timestamp();
            $vendaDoPdv->updated_at    = null;
            $vendaDoPdv->deleted_at    = null;
            $vendaDoPdv->fromPDV       = true;
            array_push($query, $vendaDoPdv);
        }

        return $query;
    }

    public function resumoOperacionalDosCaixas(array $periodo, int $idEmpresa, ?int $idUsuario = null): object
    {
        $de = $periodo['de'];
        $ate = $periodo['ate'];
        $filtroUsuarioVendas = $idUsuario ? " AND v.id_usuario = {$idUsuario}" : '';
        $filtroUsuarioLogs = $idUsuario ? " AND la.id_usuario = {$idUsuario}" : '';

        $resumo = (object) [
            'caixas_online' => 0,
            'caixas_ativos' => 0,
            'usuarios_conectados' => '',
            'faturamento_total' => 0,
            'quantidade_vendas' => 0,
            'ultima_movimentacao' => null,
        ];

        if ($idEmpresa <= 0) {
            return $resumo;
        }

        $dadosVendas = $this->queryGetOne(
            "SELECT
                COUNT(DISTINCT v.id_usuario) AS caixas_ativos,
                COALESCE(SUM(v.valor), 0) AS faturamento_total,
                COUNT(DISTINCT CASE
                    WHEN v.codigo_venda IS NOT NULL AND v.codigo_venda <> '' THEN v.codigo_venda
                    ELSE CONCAT('VENDA-', v.id)
                END) AS quantidade_vendas,
                MAX(v.created_at) AS ultima_movimentacao,
                GROUP_CONCAT(DISTINCT u.nome ORDER BY u.nome SEPARATOR ', ') AS usuarios_conectados
            FROM vendas v
            INNER JOIN usuarios u ON u.id = v.id_usuario
            WHERE v.id_empresa = {$idEmpresa}
              AND DATE(v.created_at) BETWEEN '{$de}' AND '{$ate}'
              AND v.deleted_at IS NULL
              {$filtroUsuarioVendas}"
        );

        $dadosOnline = $this->queryGetOne(
            "SELECT COUNT(DISTINCT la.id_usuario) AS caixas_online
            FROM log_acessos la
            INNER JOIN usuarios u ON u.id = la.id_usuario
            WHERE la.id_empresa = {$idEmpresa}
              AND la.created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
              AND u.status = 1
              {$filtroUsuarioLogs}"
        );

        $resumo->caixas_online = (int) ($dadosOnline->caixas_online ?? 0);
        $resumo->caixas_ativos = (int) ($dadosVendas->caixas_ativos ?? 0);
        $resumo->usuarios_conectados = $dadosVendas->usuarios_conectados ?? '';
        $resumo->faturamento_total = (float) ($dadosVendas->faturamento_total ?? 0);
        $resumo->quantidade_vendas = (int) ($dadosVendas->quantidade_vendas ?? 0);
        $resumo->ultima_movimentacao = $dadosVendas->ultima_movimentacao ?? null;

        return $resumo;
    }

    public function detalhesDosCaixas(array $periodo, int $idEmpresa, ?int $idUsuario = null): array
    {
        $de = $periodo['de'];
        $ate = $periodo['ate'];
        $filtroUsuario = $idUsuario ? " AND u.id = {$idUsuario}" : '';

        if ($idEmpresa <= 0) {
            return [];
        }

        return $this->query(
            "SELECT
                u.id,
                u.nome,
                u.imagem,
                u.status,
                COALESCE(SUM(v.valor), 0) AS faturado,
                COUNT(DISTINCT CASE
                    WHEN v.id IS NOT NULL AND v.codigo_venda IS NOT NULL AND v.codigo_venda <> '' THEN v.codigo_venda
                    WHEN v.id IS NOT NULL THEN CONCAT('VENDA-', v.id)
                    ELSE NULL
                END) AS quantidade_vendas,
                MAX(v.created_at) AS ultima_venda,
                (
                    SELECT MAX(la.created_at)
                    FROM log_acessos la
                    WHERE la.id_empresa = {$idEmpresa}
                      AND la.id_usuario = u.id
                ) AS ultimo_acesso,
                CASE
                    WHEN (
                        SELECT MAX(la.created_at)
                        FROM log_acessos la
                        WHERE la.id_empresa = {$idEmpresa}
                          AND la.id_usuario = u.id
                    ) >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) THEN 1
                    ELSE 0
                END AS online
            FROM usuarios u
            LEFT JOIN vendas v
                ON v.id_usuario = u.id
               AND v.id_empresa = {$idEmpresa}
               AND DATE(v.created_at) BETWEEN '{$de}' AND '{$ate}'
               AND v.deleted_at IS NULL
            WHERE u.id_empresa = {$idEmpresa}
              AND u.status = 1
              AND (
                    u.deleted_at IS NULL
                    OR CAST(u.deleted_at AS CHAR(19)) = '0000-00-00 00:00:00'
                  )
              {$filtroUsuario}
            GROUP BY u.id, u.nome, u.imagem, u.status
            HAVING quantidade_vendas > 0 OR ultimo_acesso IS NOT NULL
            ORDER BY online DESC, ultima_venda DESC, u.nome ASC"
        );
    }

    public function setRetirarPDV(bool $retirarPDV): void
    {
        $this->retirarPDVdoFluxoDeCaixa = $retirarPDV;
    }

    public function getRetirarPDV(): bool
    {
        return $this->retirarPDVdoFluxoDeCaixa;
    }
}
