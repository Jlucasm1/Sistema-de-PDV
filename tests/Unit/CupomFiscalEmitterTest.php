<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Fiscal\CupomFiscalEmitter;
use App\Services\Fiscal\ImpostoMockerInterface;
use PHPUnit\Framework\TestCase;

class CupomFiscalEmitterTest extends TestCase
{
    public function testDeveEmitirCupomComImpostosVindosDoMocker(): void
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

        $emitter = new CupomFiscalEmitter($mocker);
        $cupom = $emitter->emitir($itens, 'VENDA123', 9, 7, 2);

        $this->assertSame('emitido_mock', $cupom['status']);
        $this->assertSame('VENDA123', $cupom['codigo_venda']);
        $this->assertSame(20.00, $cupom['resumo']['total_bruto']);
        $this->assertSame(5.45, $cupom['resumo']['total_impostos']);
        $this->assertSame(14.55, $cupom['resumo']['total_liquido']);
        $this->assertNotEmpty($cupom['numero_cupom']);
        $this->assertNotEmpty($cupom['chave_fiscal']);
    }
}

