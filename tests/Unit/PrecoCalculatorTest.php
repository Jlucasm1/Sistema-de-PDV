<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Catalogo\PrecoCalculator;
use PHPUnit\Framework\TestCase;

class PrecoCalculatorTest extends TestCase
{
    private const OFICIAL = 15000;
    private const MINIMO  = 13000;

    public function testConverteMoedaParaCentavos(): void
    {
        $this->assertSame(15000, PrecoCalculator::paraCentavos('150,00'));
        $this->assertSame(123456, PrecoCalculator::paraCentavos('1.234,56'));
        $this->assertSame(1999, PrecoCalculator::paraCentavos('19.99'));
        $this->assertSame(1990, PrecoCalculator::paraCentavos('19,9'));
        $this->assertNull(PrecoCalculator::paraCentavos('abc'));
        $this->assertNull(PrecoCalculator::paraCentavos('-5,00'));
        $this->assertNull(PrecoCalculator::paraCentavos('1,234'));
    }

    public function testDescontoMaximoDoExemplo(): void
    {
        $this->assertSame(2000, PrecoCalculator::descontoMaximoCentavos(self::OFICIAL, self::MINIMO));
        $this->assertSame('13.33', PrecoCalculator::formatarPercentual(PrecoCalculator::descontoMaximoCentesimos(self::OFICIAL, self::MINIMO)));
    }

    public function testSimulacaoPorDescontoEmReais(): void
    {
        $r = PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 5, PrecoCalculator::MODO_REAIS, '15,00');
        $this->assertTrue($r['ok']);
        $this->assertSame('135.00', $r['preco_final']);
        $this->assertSame('675.00', $r['valor_total']);
        $this->assertSame('10.00', $r['desconto_percentual']);
        $this->assertSame('autorizado', $r['status']);
        $this->assertTrue($r['autorizado']);
    }

    public function testSimulacaoPorPercentual(): void
    {
        $r = PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 2, PrecoCalculator::MODO_PERCENTUAL, '10');
        $this->assertSame('15.00', $r['desconto_reais']);
        $this->assertSame('135.00', $r['preco_final']);
        $this->assertSame('270.00', $r['valor_total']);
    }

    public function testSimulacaoPorPrecoFinal(): void
    {
        $r = PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 1, PrecoCalculator::MODO_PRECO_FINAL, '140,00');
        $this->assertSame('10.00', $r['desconto_reais']);
        $this->assertSame('6.67', $r['desconto_percentual']);
        $this->assertSame('autorizado', $r['status']);
    }

    public function testPrecoIgualAoMinimoEhAutorizado(): void
    {
        $r = PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 1, PrecoCalculator::MODO_PRECO_FINAL, '130,00');
        $this->assertSame('autorizado', $r['status']);
        $this->assertSame('0.00', $r['excesso_unitario']);
    }

    public function testPrecoAbaixoDoMinimoEhBloqueadoEInformaExcesso(): void
    {
        $r = PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 4, PrecoCalculator::MODO_REAIS, '25,00');
        $this->assertSame('bloqueado', $r['status']);
        $this->assertFalse($r['autorizado']);
        $this->assertSame('5.00', $r['excesso_unitario']);
        $this->assertSame('20.00', $r['excesso_total']);
        $this->assertStringContainsString('gerente ou administrador', $r['mensagem']);
    }

    public function testSemPrecoMinimoNaoAutoriza(): void
    {
        $r = PrecoCalculator::simular(self::OFICIAL, 0, 1, PrecoCalculator::MODO_REAIS, '1,00');
        $this->assertSame('sem_minimo', $r['status']);
        $this->assertFalse($r['autorizado']);
    }

    public function testPrecisaoDecimalSemErroDePontoFlutuante(): void
    {
        $r = PrecoCalculator::simular(1999, 1000, 3, PrecoCalculator::MODO_PERCENTUAL, '33,33');
        $this->assertSame('6.66', $r['desconto_reais']);
        $this->assertSame('13.33', $r['preco_final']);
        $this->assertSame('39.99', $r['valor_total']);

        $r = PrecoCalculator::simular(10, 1, 3, PrecoCalculator::MODO_NENHUM);
        $this->assertSame('0.30', $r['valor_total']);
    }

    public function testEntradasInvalidasSaoRejeitadas(): void
    {
        $this->assertFalse(PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 0)['ok']);
        $this->assertFalse(PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 'x')['ok']);
        $this->assertFalse(PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 1, PrecoCalculator::MODO_PERCENTUAL, '101')['ok']);
        $this->assertFalse(PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 1, PrecoCalculator::MODO_REAIS, '150,01')['ok']);
        $this->assertFalse(PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 1, PrecoCalculator::MODO_PRECO_FINAL, '160,00')['ok']);
        $this->assertFalse(PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 1, 'invalido', '1')['ok']);
        $this->assertFalse(PrecoCalculator::simular(0, 0, 1)['ok']);
    }

    public function testAvaliacaoDoCusto(): void
    {
        // custo R$ 100,00
        $this->assertSame(PrecoCalculator::CUSTO_SEM, PrecoCalculator::avaliarCusto(15000, 0)['nivel']);
        $this->assertSame(PrecoCalculator::CUSTO_ABAIXO, PrecoCalculator::avaliarCusto(9999, 10000)['nivel']);
        $this->assertSame(PrecoCalculator::CUSTO_PERTO, PrecoCalculator::avaliarCusto(10000, 10000)['nivel']);
        $this->assertSame(PrecoCalculator::CUSTO_PERTO, PrecoCalculator::avaliarCusto(10999, 10000)['nivel']);
        $this->assertSame(PrecoCalculator::CUSTO_OK, PrecoCalculator::avaliarCusto(11000, 10000)['nivel']);

        $ok = PrecoCalculator::avaliarCusto(15000, 10000);
        $this->assertSame('50.00', $ok['margem_percentual']);
        $this->assertSame('50.00', $ok['lucro_unitario']);

        $prejuizo = PrecoCalculator::avaliarCusto(9000, 10000);
        $this->assertSame('-10.00', $prejuizo['margem_percentual']);
        $this->assertSame('-10.00', $prejuizo['lucro_unitario']);
    }

    public function testSimulacaoAlertaPrecoPertoOuAbaixoDoCusto(): void
    {
        $custo = 12500; // R$ 125,00

        $semDesconto = PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 1, PrecoCalculator::MODO_NENHUM, null, $custo);
        $this->assertSame(PrecoCalculator::CUSTO_OK, $semDesconto['custo_nivel']);
        $this->assertNull($semDesconto['custo_alerta']);

        $perto = PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 1, PrecoCalculator::MODO_PRECO_FINAL, '130,00', $custo);
        $this->assertSame('autorizado', $perto['status']);
        $this->assertSame(PrecoCalculator::CUSTO_PERTO, $perto['custo_nivel']);
        $this->assertNotNull($perto['custo_alerta']);

        $abaixo = PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 1, PrecoCalculator::MODO_PRECO_FINAL, '120,00', $custo);
        $this->assertSame(PrecoCalculator::CUSTO_ABAIXO, $abaixo['custo_nivel']);
        $this->assertSame('-5.00', $abaixo['lucro_unitario']);

        $semCusto = PrecoCalculator::simular(self::OFICIAL, self::MINIMO, 1, PrecoCalculator::MODO_PRECO_FINAL, '120,00');
        $this->assertSame(PrecoCalculator::CUSTO_SEM, $semCusto['custo_nivel']);
        $this->assertNull($semCusto['custo_alerta']);
    }
}
