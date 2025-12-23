<?php

declare(strict_types=1);

namespace ItauBoletoPix\Enums;

/**
 * Etapa do processo de emissão do boleto
 * Process step for boleto issuance
 *
 * Campo API: etapa_processo_boleto
 */
enum ProcessStep: string
{
    /**
     * Apenas valida e simula o boleto, sem registro bancário
     * Validates and simulates the boleto without bank registration
     */
    case SIMULATION = 'Simulacao';

    /**
     * Registra efetivamente o boleto no banco
     * Effectively registers the boleto at the bank
     */
    case REGISTRATION = 'Efetivacao';
}
