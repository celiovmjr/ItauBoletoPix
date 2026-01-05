<?php

declare(strict_types=1);

namespace ItauBoletoPix\Enums;

/**
 * Tipo de boleto
 * Boleto type
 *
 * Campo API: tipo_boleto
 */
enum BoletoType: string
{
    /**
     * Pagamento à vista
     * At sight payment
     */
    case AT_SIGHT = 'a vista';
}
