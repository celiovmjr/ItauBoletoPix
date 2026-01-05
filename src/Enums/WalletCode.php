<?php

declare(strict_types=1);

namespace ItauBoletoPix\Enums;

/**
 * Código da carteira de cobrança
 * Collection wallet code
 *
 * Campo API: codigo_carteira
 */
enum WalletCode: string
{
    /**
     * Carteira registrada Itaú (109)
     * Itaú registered wallet (109)
     */
    case REGISTERED_109 = '109';
}
