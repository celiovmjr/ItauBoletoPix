<?php

declare(strict_types=1);

namespace ItauBoletoPix\Enums;

/**
 * Tipo de juros por atraso
 * Late payment interest type
 *
 * Campo API: codigo_tipo_juros
 */
enum InterestType: string
{
    /**
     * Juros em valor fixo por dia de atraso
     * Fixed daily interest amount
     */
    case DAILY_AMOUNT = '93';

    /**
     * Juros em percentual mensal
     * Monthly percentage interest
     */
    case MONTHLY_PERCENTAGE = '90';

    /**
     * Isento de juros
     * No interest applied
     */
    case NONE = '00';
}
