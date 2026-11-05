<?php

namespace App\Services\Fiscal;

/**
 * Mocker de impostos para ambiente de desenvolvimento/homologacao.
 * "Mokito" mantido no nome conforme solicitacao.
 */
class MokitoImpostoMocker implements ImpostoMockerInterface
{
    private float $aliquotaIcms;
    private float $aliquotaPis;
    private float $aliquotaCofins;

    public function __construct(float $aliquotaIcms = 18.0, float $aliquotaPis = 1.65, float $aliquotaCofins = 7.6)
    {
        $this->aliquotaIcms = $aliquotaIcms;
        $this->aliquotaPis = $aliquotaPis;
        $this->aliquotaCofins = $aliquotaCofins;
    }

    public function simular(array $itens): array
    {
        $baseCalculo = 0.0;
        $totalItens = 0;

        foreach ($itens as $item) {
            $quantidade = isset($item['quantidade']) ? (float) $item['quantidade'] : 0;
            $total = isset($item['total']) ? (float) $item['total'] : 0;
            $baseCalculo += $total;
            $totalItens += (int) $quantidade;
        }

        $icms = round($baseCalculo * ($this->aliquotaIcms / 100), 2);
        $pis = round($baseCalculo * ($this->aliquotaPis / 100), 2);
        $cofins = round($baseCalculo * ($this->aliquotaCofins / 100), 2);
        $totalImpostos = round($icms + $pis + $cofins, 2);

        return [
            'base_calculo' => round($baseCalculo, 2),
            'total_itens' => $totalItens,
            'aliquotas' => [
                'icms' => $this->aliquotaIcms,
                'pis' => $this->aliquotaPis,
                'cofins' => $this->aliquotaCofins,
            ],
            'valores' => [
                'icms' => $icms,
                'pis' => $pis,
                'cofins' => $cofins,
                'total_impostos' => $totalImpostos,
            ],
        ];
    }
}

