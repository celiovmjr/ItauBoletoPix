<?php

declare(strict_types=1);

namespace ItauBoletoPix\Enums;

/**
 * Tipo de desconto
 * Discount type
 *
 * Campo API: codigo_tipo_desconto
 */
enum DiscountType: string
{
    /**
     * Desconto em valor fixo até determinada data
     * Fixed amount discount until a specific date
     */
    case FIXED_AMOUNT_UNTIL_DATE = '01';

    /**
     * Desconto em percentual até determinada data
     * Percentage discount until a specific date
     */
    case PERCENTAGE_UNTIL_DATE = '02';

    /**
     * Sem desconto
     * No discount applied
     */
    case NONE = '00';
}
