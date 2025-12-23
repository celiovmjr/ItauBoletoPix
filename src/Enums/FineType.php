<?php

declare(strict_types=1);

namespace ItauBoletoPix\Enums;

/**
 * Tipo de multa por atraso
 * Late payment fine type
 *
 * Campo API: codigo_tipo_multa
 */
enum FineType: string
{
    /**
     * Multa em percentual sobre o valor do título
     * Percentage-based fine
     */
    case PERCENTAGE = '02';

    /**
     * Multa em valor fixo
     * Fixed amount fine
     */
    case FIXED_AMOUNT = '01';

    /**
     * Isento de multa
     * No fine applied
     */
    case NONE = '00';
}
