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

    public function setRetirarPDV(bool $retirarPDV): void
    {
        $this->retirarPDVdoFluxoDeCaixa = $retirarPDV;
    }

    public function getRetirarPDV(): bool
    {
        return $this->retirarPDVdoFluxoDeCaixa;
    }
}
