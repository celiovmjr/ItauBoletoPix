<?php

declare(strict_types=1);

namespace ItauBoletoPix\Enums;

/**
 * Tipo de juros por atraso
 * Late payment interest type
 *
 * Campo API: codigo_tipo_juros
 * 
 * Conforme documentação Itaú:
 * Body de entrada:
 * '05' - Isento (sem juros após vencimento)
 * '90' - Percentual mensal (dias úteis/corridos conforme cadastro)
 * '91' - Percentual diário (dias úteis/corridos conforme cadastro)
 * '92' - Percentual anual (dias úteis/corridos conforme cadastro)
 * '93' - Valor diário (dias úteis/corridos conforme cadastro)
 * 
 * Body de saída (retorno varia conforme cadastro):
 * Isento: '05' (úteis) / '05' (corridos)
 * Percentual mensal: '08' (úteis) / '03' (corridos)
 * Percentual diário: '07' (úteis) / '02' (corridos)
 * Percentual anual: '09' (úteis) / '04' (corridos)
 * Valor diário: '06' (úteis) / '01' (corridos)
 */
enum InterestType: string
{
    /**
     * Isento de juros
     * No interest applied after due date
     */
    case EXEMPT = '05';

    /**
     * Percentual mensal
     * Monthly percentage interest
     * 
     * Requer: percentual_juros (formato: 000000100000)
     * Usa parâmetros do cadastro do beneficiário para dias úteis/corridos
     */
    case MONTHLY_PERCENTAGE = '90';

    /**
     * Percentual diário
     * Daily percentage interest
     * 
     * Requer: percentual_juros (formato: 000000100000)
     * Usa parâmetros do cadastro do beneficiário para dias úteis/corridos
     */
    case DAILY_PERCENTAGE = '91';

    /**
     * Percentual anual
     * Annual percentage interest
     * 
     * Requer: percentual_juros (formato: 000000100000)
     * Usa parâmetros do cadastro do beneficiário para dias úteis/corridos
     */
    case ANNUAL_PERCENTAGE = '92';

    /**
     * Valor fixo diário
     * Daily fixed amount interest
     * 
     * Requer: valor_juros (formato: 999999999999999.00)
     * Usa parâmetros do cadastro do beneficiário para dias úteis/corridos
     */
    case DAILY_AMOUNT = '93';

    /**
     * Verifica se o tipo requer valor fixo
     */
    public function requiresAmount(): bool
    {
        return $this === self::DAILY_AMOUNT;
    }

    /**
     * Verifica se o tipo requer percentual
     */
    public function requiresPercentage(): bool
    {
        return match($this) {
            self::MONTHLY_PERCENTAGE,
            self::DAILY_PERCENTAGE,
            self::ANNUAL_PERCENTAGE => true,
            default => false,
        };
    }

    /**
     * Verifica se está isento
     */
    public function isExempt(): bool
    {
        return $this === self::EXEMPT;
    }

    /**
     * Retorna descrição do tipo de juros
     */
    public function description(): string
    {
        return match($this) {
            self::EXEMPT => 'Isento de juros após vencimento',
            self::MONTHLY_PERCENTAGE => 'Percentual mensal',
            self::DAILY_PERCENTAGE => 'Percentual diário',
            self::ANNUAL_PERCENTAGE => 'Percentual anual',
            self::DAILY_AMOUNT => 'Valor fixo diário',
        };
    }
}