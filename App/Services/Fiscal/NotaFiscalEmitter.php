<?php

namespace App\Services\Fiscal;

use App\Models\Empresa;

class NotaFiscalEmitter
{
    private ImpostoMockerInterface $impostoMocker;
    private string $modelo;
    private string $serie;

    public function __construct(ImpostoMockerInterface $impostoMocker, string $modelo = 'NFC-e', string $serie = '1')
    {
        $this->impostoMocker = $impostoMocker;
        $this->modelo = $modelo;
        $this->serie = $serie;
    }

    /**
     * @param array<int, array<string, mixed>> $itens
     * @return array<string, mixed>
     */
    public function emitir(array $itens, string $codigoVenda, int $idEmpresa, int $idUsuario, int $idMeioPagamento): array
    {
        $impostos = $this->impostoMocker->simular($itens);
        $totalProdutos = (float) ($impostos['base_calculo'] ?? 0);
        $totalImpostos = (float) ($impostos['valores']['total_impostos'] ?? 0);
        $numeroNota = $this->gerarNumeroNota($idEmpresa);
        $chaveAcesso = $this->gerarChaveAcesso($codigoVenda, $idEmpresa, $numeroNota);

        return [
            'status' => 'emitida_nf_mock',
            'ambiente' => 'homologacao',
            'modelo' => $this->modelo,
            'serie' => $this->serie,
            'numero_nf' => $numeroNota,
            'chave_acesso' => $chaveAcesso,
            'natureza_operacao' => 'Venda de mercadorias',
            'emitente' => $this->buscarEmitente($idEmpresa),
            'consumidor' => [
                'nome' => 'Consumidor não identificado',
            ],
            'codigo_venda' => $codigoVenda,
            'id_empresa' => $idEmpresa,
            'id_usuario' => $idUsuario,
            'id_meio_pagamento' => $idMeioPagamento,
            'emitida_em' => date('Y-m-d H:i:s'),
            'itens' => $itens,
            'impostos' => $impostos,
            'totais' => [
                'total_produtos' => round($totalProdutos, 2),
                'total_impostos' => round($totalImpostos, 2),
                'total_nota' => round($totalProdutos, 2),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buscarEmitente(int $idEmpresa): array
    {
        $empresa = (new Empresa())->find($idEmpresa);

        return [
            'nome' => normalizaTexto((string) ($empresa->nome ?? 'Empresa não informada')),
            'email' => normalizaTexto((string) ($empresa->email ?? '')),
            'telefone' => normalizaTexto((string) (($empresa->telefone ?? '') ?: ($empresa->celular ?? ''))),
            'municipio' => 'Goiânia',
            'uf' => 'GO',
        ];
    }

    private function gerarNumeroNota(int $idEmpresa): string
    {
        return date('ymdHis') . str_pad((string) $idEmpresa, 4, '0', STR_PAD_LEFT);
    }

    private function gerarChaveAcesso(string $codigoVenda, int $idEmpresa, string $numeroNota): string
    {
        $hash = preg_replace('/\D/', '', hash('sha256', $codigoVenda . '|' . $idEmpresa . '|' . $numeroNota));
        $base = str_pad(substr($hash, 0, 43), 43, '0');

        return $base . $this->calcularDigitoVerificador($base);
    }

    private function calcularDigitoVerificador(string $chave): int
    {
        $peso = 2;
        $soma = 0;

        for ($i = strlen($chave) - 1; $i >= 0; $i--) {
            $soma += ((int) $chave[$i]) * $peso;
            $peso = $peso === 9 ? 2 : $peso + 1;
        }

        $resto = $soma % 11;
        $digito = 11 - $resto;

        return $digito >= 10 ? 0 : $digito;
    }
}
