<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Fiscal\ImpostoMockerInterface;
use App\Services\Fiscal\NotaFiscalEmitter;
use PHPUnit\Framework\TestCase;

class NotaFiscalEmitterTest extends TestCase
{
    public function testDeveEmitirNotaFiscalComImpostosVindosDoMocker(): void
    {
        $itens = [
            ['id' => 1, 'produto' => 'Cafe', 'quantidade' => 2, 'total' => 20.00],
        ];

        $impostosMockados = [
            'base_calculo' => 20.00,
            'total_itens' => 2,
            'aliquotas' => ['icms' => 18.0, 'pis' => 1.65, 'cofins' => 7.6],
            'valores' => ['icms' => 3.60, 'pis' => 0.33, 'cofins' => 1.52, 'total_impostos' => 5.45],
        ];

        $mocker = $this->createMock(ImpostoMockerInterface::class);
        $mocker->expects($this->once())
            ->method('simular')
            ->with($itens)
            ->willReturn($impostosMockados);

        $emitter = new NotaFiscalEmitter($mocker);
        $notaFiscal = $emitter->emitir($itens, 'VENDA123', 9, 7, 2);

        $this->assertSame('emitida_nf_mock', $notaFiscal['status']);
        $this->assertSame('NFC-e', $notaFiscal['modelo']);
        $this->assertSame('VENDA123', $notaFiscal['codigo_venda']);
        $this->assertSame(20.00, $notaFiscal['totais']['total_produtos']);
        $this->assertSame(5.45, $notaFiscal['totais']['total_impostos']);
        $this->assertSame(20.00, $notaFiscal['totais']['total_nota']);
        $this->assertSame('Goiânia', $notaFiscal['emitente']['municipio']);
        $this->assertSame('GO', $notaFiscal['emitente']['uf']);
        $this->assertSame('Consumidor não identificado', $notaFiscal['consumidor']['nome']);
        $this->assertNotEmpty($notaFiscal['numero_nf']);
        $this->assertMatchesRegularExpression('/^\d{44}$/', $notaFiscal['chave_acesso']);
    }
}
