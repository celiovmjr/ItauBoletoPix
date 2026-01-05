<?php

declare(strict_types=1);

namespace ItauBoletoPix\Utils;

/**
 * Formatador de valores monetários para o padrão do Itaú
 *
 * O Itaú requer valores com 15 dígitos, representando centavos:
 * R$ 0,10 = 00000000000000010
 * R$ 100,00 = 00000000000010000
 * R$ 1.500,75 = 00000000000150075
 */
class MoneyFormatter
{
    /**
     * Formata valor em reais para o padrão do Itaú
     *
     * @param  float  $amount Valor em reais (ex: 100.50)
     * @return string Valor formatado com 15 dígitos (ex: 00000000000010050)
     */
    public static function format(float $amount): string
    {
        // Converte para centavos
        $cents = (int)round($amount * 100);

        // Formata com 15 dígitos
        return str_pad((string)$cents, 15, '0', STR_PAD_LEFT);
    }

    /**
     * Converte valor do formato Itaú para float
     *
     * @param  string $formattedValue Valor no formato Itaú
     * @return float  Valor em reais
     */
    public static function parse(string $formattedValue): float
    {
        $cents = (int)$formattedValue;

        return $cents / 100;
    }
}
