<?php

declare(strict_types=1);

namespace ItauBoletoPix\Enums;

/**
 * Espécie do título
 * Title species
 *
 * Campo API: codigo_especie
 */
enum TitleSpecies: string
{
    /**
     * Duplicata mercantil
     * Merchant duplicate
     */
    case MERCHANT_DUPLICATE = '01';

    /**
     * Nota promissória
     * Promissory note
     */
    case PROMISSORY_NOTE = '02';

    /**
     * Recibo
     * Receipt
     */
    case RECEIPT = '05';
}
