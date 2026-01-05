<?php

declare(strict_types=1);

namespace ItauBoletoPix\Models;

use ItauBoletoPix\Enums\FineType;

/**
 * Multa do Boleto
 */
class Fine
{
    public function __construct(
        private FineType|string $type,
        private ?float $percentage = null,
        private ?float $amount = null,
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
            $this->type = FineType::from($this->type);
        }

        // Valida campos obrigatórios conforme o tipo
        if ($this->type->requiresAmount() && $this->amount === null) {
            throw new \InvalidArgumentException(
                "Tipo de multa '{$this->type->value}' requer valor fixo (amount)"
            );
        }

        if ($this->type->requiresPercentage() && $this->percentage === null) {
            throw new \InvalidArgumentException(
                "Tipo de multa '{$this->type->value}' requer percentual (percentage)"
            );
        }

        if ($this->type === FineType::EXEMPT && ($this->percentage !== null || $this->amount !== null)) {
            throw new \InvalidArgumentException(
                "Tipo de multa isento não deve ter valores configurados"
            );
        }
    }

    public function getType(): FineType|string
    {
        return $this->type;
    }

    public function getPercentage(): ?float
    {
        return $this->percentage;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
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
        $typeValue = is_object($this->type) ? $this->type->value : $this->type;
        
        return array_filter([
            'type' => $typeValue,
            'percentage' => $this->percentage,
            'amount' => $this->amount,
            'start_date' => $this->startDate?->format('Y-m-d'),
            'days_after_due' => $this->daysAfterDue,
        ], fn($value) => $value !== null);
    }

    /**
     * Factory method: Cria instância isenta de multa
     */
    public static function exempt(): self
    {
        return new self(FineType::EXEMPT);
    }

    /**
     * Factory method: Cria instância com percentual
     */
    public static function percentage(
        float $percentage,
        ?\DateTimeImmutable $startDate = null,
        ?int $daysAfterDue = null
    ): self {
        return new self(
            type: FineType::PERCENTAGE,
            percentage: $percentage,
            startDate: $startDate,
            daysAfterDue: $daysAfterDue
        );
    }

    /**
     * Factory method: Cria instância com valor fixo
     */
    public static function fixedAmount(
        float $amount,
        ?\DateTimeImmutable $startDate = null,
        ?int $daysAfterDue = null
    ): self {
        return new self(
            type: FineType::FIXED_AMOUNT,
            amount: $amount,
            startDate: $startDate,
            daysAfterDue: $daysAfterDue
        );
    }
}