<?php

declare(strict_types=1);

namespace ItauBoletoPix\Models;

use ItauBoletoPix\Enums\InterestType;

/**
 * Juros do Boleto
 */
class Interest
{
    public function __construct(
        private InterestType|string $type,
        private ?float $amountPerDay = null,
        private ?float $percentage = null,
        private ?\DateTimeImmutable $startDate = null,
        private ?int $daysAfterDue = null
    ) {
        $this->validateType();
    }

    /**
     * Valida o tipo e converte string para enum se necessário
     */
    private function validateType(): void
    {
        // Converte string para enum se necessário
        if (\is_string($this->type)) {
            $this->type = InterestType::from($this->type);
        }

        // Valida campos obrigatórios conforme o tipo
        if ($this->type->requiresAmount() && $this->amountPerDay === null) {
            throw new \InvalidArgumentException(
                "Tipo de juros '{$this->type->value}' requer valor diário (amountPerDay)"
            );
        }

        if ($this->type->requiresPercentage() && $this->percentage === null) {
            throw new \InvalidArgumentException(
                "Tipo de juros '{$this->type->value}' requer percentual (percentage)"
            );
        }

        if ($this->type->isExempt() && ($this->percentage !== null || $this->amountPerDay !== null)) {
            throw new \InvalidArgumentException(
                "Tipo de juros isento não deve ter valores configurados"
            );
        }
    }

    public function getType(): InterestType|string
    {
        return $this->type;
    }

    public function getAmountPerDay(): ?float
    {
        return $this->amountPerDay;
    }

    public function getPercentage(): ?float
    {
        return $this->percentage;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getDaysAfterDue(): ?int
    {
        return $this->daysAfterDue;
    }

    public function toArray(): array
    {
        $typeValue = \is_object($this->type) ? $this->type->value : $this->type;
        
        return array_filter([
            'type' => $typeValue,
            'amount_per_day' => $this->amountPerDay,
            'percentage' => $this->percentage,
            'start_date' => $this->startDate?->format('Y-m-d'),
            'days_after_due' => $this->daysAfterDue,
        ], fn($value) => $value !== null);
    }

    /**
     * Factory method: Cria instância isenta de juros
     */
    public static function exempt(): self
    {
        return new self(InterestType::EXEMPT);
    }

    /**
     * Factory method: Cria instância com percentual mensal
     */
    public static function monthlyPercentage(
        float $percentage,
        ?\DateTimeImmutable $startDate = null,
        ?int $daysAfterDue = null
    ): self {
        return new self(
            type: InterestType::MONTHLY_PERCENTAGE,
            percentage: $percentage,
            startDate: $startDate,
            daysAfterDue: $daysAfterDue
        );
    }

    /**
     * Factory method: Cria instância com percentual diário
     */
    public static function dailyPercentage(
        float $percentage,
        ?\DateTimeImmutable $startDate = null,
        ?int $daysAfterDue = null
    ): self {
        return new self(
            type: InterestType::DAILY_PERCENTAGE,
            percentage: $percentage,
            startDate: $startDate,
            daysAfterDue: $daysAfterDue
        );
    }

    /**
     * Factory method: Cria instância com percentual anual
     */
    public static function annualPercentage(
        float $percentage,
        ?\DateTimeImmutable $startDate = null,
        ?int $daysAfterDue = null
    ): self {
        return new self(
            type: InterestType::ANNUAL_PERCENTAGE,
            percentage: $percentage,
            startDate: $startDate,
            daysAfterDue: $daysAfterDue
        );
    }

    /**
     * Factory method: Cria instância com valor fixo diário
     */
    public static function dailyAmount(
        float $amountPerDay,
        ?\DateTimeImmutable $startDate = null,
        ?int $daysAfterDue = null
    ): self {
        return new self(
            type: InterestType::DAILY_AMOUNT,
            amountPerDay: $amountPerDay,
            startDate: $startDate,
            daysAfterDue: $daysAfterDue
        );
    }
}