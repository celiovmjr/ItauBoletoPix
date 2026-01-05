<?php

declare(strict_types=1);

namespace ItauBoletoPix\Enums;

/**
 * Instrumento de cobrança
 * Charge instrument type
 *
 * Campo API: descricao_instrumento_cobranca
 */
enum ChargeInstrument: string
{
    /**
     * Boleto bancário tradicional
     * Traditional bank boleto
     */
    case BOLETO = 'boleto';

    /**
     * Boleto com QR Code PIX integrado
     * Boleto with integrated PIX QR Code
     */
    case BOLETO_PIX = 'boleto_pix';
}
