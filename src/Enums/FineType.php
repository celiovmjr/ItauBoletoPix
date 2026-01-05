<?php

declare(strict_types=1);

namespace ItauBoletoPix\Enums;

/**
 * Tipo de multa por atraso
 * Late payment fine type
 *
 * Campo API: codigo_tipo_multa
 * 
 * Conforme documentação Itaú:
 * '01' - Valor fixo de multa após o vencimento
 * '02' - Percentual do valor do título após o vencimento
 * '03' - Isento (sem multa)
 */
enum FineType: string
{
    /**
     * Multa em valor fixo
     * Fixed amount fine after due date
     * 
     * Requer: valor_multa (formato: 999999999999999.00)
     */
    case FIXED_AMOUNT = '01';

    /**
     * Multa em percentual sobre o valor do título
     * Percentage-based fine after due date
     * 
     * Requer: percentual_multa (formato: 9999999.00000)
     */
    case PERCENTAGE = '02';

    /**
     * Isento de multa
     * No fine applied
     */
    case EXEMPT = '03';

    /**
     * Verifica se o tipo requer valor fixo
     */
    public function requiresAmount(): bool
    {
        return $this === self::FIXED_AMOUNT;
    }

    /**
     * Verifica se o tipo requer percentual
     */
    public function requiresPercentage(): bool
    {
        return $this === self::PERCENTAGE;
    }

    /**
     * Retorna descrição do tipo de multa
     */
    public function description(): string
    {
        return match($this) {
            self::FIXED_AMOUNT => 'Valor fixo de multa após vencimento',
            self::PERCENTAGE => 'Percentual do valor do título',
            self::EXEMPT => 'Isento de multa',
        };
    }
}