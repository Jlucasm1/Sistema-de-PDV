<?php

namespace App\Services\Catalogo;

/**
 * Cálculos comerciais do catálogo. Trabalha apenas com inteiros (centavos e
 * centésimos de percentual) para evitar erros de ponto flutuante.
 * Não acessa banco, sessão, estoque, caixa ou vendas.
 */
class PrecoCalculator
{
    public const MODO_NENHUM      = 'nenhum';
    public const MODO_PERCENTUAL  = 'percentual';
    public const MODO_REAIS       = 'reais';
    public const MODO_PRECO_FINAL = 'preco_final';

    public const MAX_QUANTIDADE = 1000000;
    public const MAX_CENTAVOS   = 9999999999;

    /** Margem sobre o custo (em centésimos de %) abaixo da qual o preço é considerado "perto do custo". 1000 = 10%. */
    public const MARGEM_ALERTA_CUSTO = 1000;

    public const CUSTO_SEM      = 'sem_custo';
    public const CUSTO_ABAIXO   = 'abaixo_custo';
    public const CUSTO_PERTO    = 'perto_custo';
    public const CUSTO_OK       = 'ok';

    public static function paraCentavos($valor): ?int
    {
        if (is_int($valor)) {
            return $valor >= 0 ? $valor * 100 : null;
        }
        if (is_float($valor)) {
            $valor = number_format($valor, 2, '.', '');
        }

        $valor = trim(str_replace(['R$', ' '], '', (string) $valor));
        if ($valor === '') {
            return null;
        }
        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
        if (!preg_match('/^(\d{1,10})(?:\.(\d{1,2}))?$/', $valor, $m)) {
            return null;
        }

        $centavos = ((int) $m[1]) * 100 + (int) str_pad($m[2] ?? '0', 2, '0');
        return $centavos <= self::MAX_CENTAVOS ? $centavos : null;
    }

    public static function percentualParaCentesimos($valor): ?int
    {
        $valor = trim(str_replace(['%', ' '], '', (string) $valor));
        if ($valor === '') {
            return null;
        }
        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
        if (!preg_match('/^(\d{1,3})(?:\.(\d{1,2}))?$/', $valor, $m)) {
            return null;
        }

        $centesimos = ((int) $m[1]) * 100 + (int) str_pad($m[2] ?? '0', 2, '0');
        return $centesimos <= 10000 ? $centesimos : null;
    }

    public static function formatarCentavos(int $centavos): string
    {
        $sinal = $centavos < 0 ? '-' : '';
        $abs   = abs($centavos);
        return sprintf('%s%d.%02d', $sinal, intdiv($abs, 100), $abs % 100);
    }

    public static function formatarPercentual(int $centesimos): string
    {
        return sprintf('%d.%02d', intdiv($centesimos, 100), $centesimos % 100);
    }

    private static function divisaoArredondada(int $numerador, int $denominador): int
    {
        return intdiv(2 * $numerador + $denominador, 2 * $denominador);
    }

    public static function descontoMaximoCentavos(int $precoOficial, int $precoMinimo): int
    {
        return max(0, $precoOficial - $precoMinimo);
    }

    public static function descontoMaximoCentesimos(int $precoOficial, int $precoMinimo): int
    {
        if ($precoOficial <= 0) {
            return 0;
        }
        return self::divisaoArredondada(self::descontoMaximoCentavos($precoOficial, $precoMinimo) * 10000, $precoOficial);
    }

    /**
     * Compara um preço de venda com o custo do produto.
     * Margem = (preço - custo) / custo, em centésimos de percentual.
     *
     * @return array{nivel:string, margem_percentual:?string, lucro_unitario:?string}
     */
    public static function avaliarCusto(int $precoVenda, int $custo): array
    {
        if ($custo <= 0) {
            return ['nivel' => self::CUSTO_SEM, 'margem_percentual' => null, 'lucro_unitario' => null];
        }

        $lucro  = $precoVenda - $custo;
        $margem = intdiv(($lucro) * 10000, $custo);

        if ($lucro < 0) {
            $nivel = self::CUSTO_ABAIXO;
        } elseif ($margem < self::MARGEM_ALERTA_CUSTO) {
            $nivel = self::CUSTO_PERTO;
        } else {
            $nivel = self::CUSTO_OK;
        }

        $sinal = $margem < 0 ? '-' : '';
        return [
            'nivel'             => $nivel,
            'margem_percentual' => $sinal . self::formatarPercentual(abs($margem)),
            'lucro_unitario'    => self::formatarCentavos($lucro),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public static function simular(int $precoOficial, int $precoMinimo, $quantidade, string $modo = self::MODO_NENHUM, $valor = null, int $custo = 0): array
    {
        $erro = static fn(string $mensagem): array => ['ok' => false, 'erro' => $mensagem];

        if ($precoOficial <= 0) {
            return $erro('Este produto ainda não tem preço oficial definido.');
        }

        $qtd = filter_var($quantidade, FILTER_VALIDATE_INT);
        if ($qtd === false || $qtd < 1 || $qtd > self::MAX_QUANTIDADE) {
            return $erro('Informe uma quantidade inteira maior que zero.');
        }

        $descontoMaxCent = self::descontoMaximoCentavos($precoOficial, $precoMinimo);
        $descontoMaxPct  = self::descontoMaximoCentesimos($precoOficial, $precoMinimo);

        $desconto = 0;
        switch ($modo) {
            case self::MODO_NENHUM:
                break;
            case self::MODO_PERCENTUAL:
                $pct = self::percentualParaCentesimos($valor);
                if ($pct === null) {
                    return $erro('Desconto percentual inválido (use de 0 a 100).');
                }
                $desconto = self::divisaoArredondada($precoOficial * $pct, 10000);
                break;
            case self::MODO_REAIS:
                $desconto = self::paraCentavos($valor);
                if ($desconto === null) {
                    return $erro('Desconto em reais inválido.');
                }
                break;
            case self::MODO_PRECO_FINAL:
                $final = self::paraCentavos($valor);
                if ($final === null) {
                    return $erro('Preço final inválido.');
                }
                if ($final > $precoOficial) {
                    return $erro('O preço final não pode ser maior que o preço oficial.');
                }
                $desconto = $precoOficial - $final;
                break;
            default:
                return $erro('Modalidade de simulação inválida.');
        }

        if ($desconto > $precoOficial) {
            return $erro('O desconto não pode ser maior que o preço oficial.');
        }

        $precoFinal    = $precoOficial - $desconto;
        $descontoPct   = self::divisaoArredondada($desconto * 10000, $precoOficial);
        $total         = $precoFinal * $qtd;
        $semMinimo     = $precoMinimo <= 0;
        $abaixo        = !$semMinimo && $precoFinal < $precoMinimo;
        $excessoUnit   = $abaixo ? $precoMinimo - $precoFinal : 0;
        $autorizado    = !$semMinimo && !$abaixo;

        if ($semMinimo) {
            $status   = 'sem_minimo';
            $mensagem = 'Preço mínimo não cadastrado para este produto. Consulte o gerente ou administrador antes de negociar.';
        } elseif ($abaixo) {
            $status   = 'bloqueado';
            $mensagem = 'Preço abaixo do mínimo autorizado em R$ ' . self::formatarCentavos($excessoUnit)
                . ' por unidade. É necessária a autorização do gerente ou administrador.';
        } else {
            $status   = 'autorizado';
            $mensagem = 'Preço dentro do limite autorizado.';
        }

        $custoAval = self::avaliarCusto($precoFinal, $custo);
        if ($custoAval['nivel'] === self::CUSTO_ABAIXO) {
            $alertaCusto = 'Atenção: preço abaixo do custo do produto (venda com prejuízo). Consulte o gerente ou administrador.';
        } elseif ($custoAval['nivel'] === self::CUSTO_PERTO) {
            $alertaCusto = 'Atenção: preço muito próximo do custo do produto (margem abaixo de '
                . intdiv(self::MARGEM_ALERTA_CUSTO, 100) . '%).';
        } else {
            $alertaCusto = null;
        }

        return [
            'ok'                         => true,
            'status'                     => $status,
            'autorizado'                 => $autorizado,
            'mensagem'                   => $mensagem,
            'quantidade'                 => $qtd,
            'preco_oficial'              => self::formatarCentavos($precoOficial),
            'preco_minimo'               => self::formatarCentavos($precoMinimo),
            'desconto_maximo_reais'      => self::formatarCentavos($descontoMaxCent),
            'desconto_maximo_percentual' => self::formatarPercentual($descontoMaxPct),
            'desconto_reais'             => self::formatarCentavos($desconto),
            'desconto_percentual'        => self::formatarPercentual($descontoPct),
            'preco_final'                => self::formatarCentavos($precoFinal),
            'valor_total'                => self::formatarCentavos($total),
            'excesso_unitario'           => self::formatarCentavos($excessoUnit),
            'excesso_total'              => self::formatarCentavos($excessoUnit * $qtd),
            'custo_nivel'                => $custoAval['nivel'],
            'custo_alerta'               => $alertaCusto,
            'margem_percentual'          => $custoAval['margem_percentual'],
            'lucro_unitario'             => $custoAval['lucro_unitario'],
        ];
    }
}
