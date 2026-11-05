<?php

namespace App\Services\Fiscal;

class CupomFiscalEmitter
{
    private ImpostoMockerInterface $impostoMocker;

    public function __construct(ImpostoMockerInterface $impostoMocker)
    {
        $this->impostoMocker = $impostoMocker;
    }

    /**
     * @param array<int, array<string, mixed>> $itens
     * @return array<string, mixed>
     */
    public function emitir(array $itens, string $codigoVenda, int $idEmpresa, int $idUsuario, int $idMeioPagamento): array
    {
        $impostos = $this->impostoMocker->simular($itens);
        $totalBruto = (float) ($impostos['base_calculo'] ?? 0);
        $totalImpostos = (float) ($impostos['valores']['total_impostos'] ?? 0);
        $totalLiquido = round($totalBruto - $totalImpostos, 2);

        return [
            'status' => 'emitido_mock',
            'ambiente' => 'homologacao',
            'numero_cupom' => $this->gerarNumeroCupom($idEmpresa),
            'chave_fiscal' => $this->gerarChaveFiscal($codigoVenda, $idEmpresa, $idUsuario),
            'codigo_venda' => $codigoVenda,
            'id_empresa' => $idEmpresa,
            'id_usuario' => $idUsuario,
            'id_meio_pagamento' => $idMeioPagamento,
            'emitido_em' => date('Y-m-d H:i:s'),
            'itens' => $itens,
            'impostos' => $impostos,
            'resumo' => [
                'total_bruto' => round($totalBruto, 2),
                'total_impostos' => round($totalImpostos, 2),
                'total_liquido' => $totalLiquido,
            ],
        ];
    }

    private function gerarNumeroCupom(int $idEmpresa): string
    {
        return date('YmdHis') . str_pad((string) $idEmpresa, 4, '0', STR_PAD_LEFT);
    }

    private function gerarChaveFiscal(string $codigoVenda, int $idEmpresa, int $idUsuario): string
    {
        return strtoupper(hash('sha256', $codigoVenda . '|' . $idEmpresa . '|' . $idUsuario . '|' . microtime(true)));
    }
}

