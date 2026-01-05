<?php

declare(strict_types=1);

namespace ItauBoletoPix\Models;

/**
 * Desconto do Boleto
 */
class Discount
{
    public function __construct(
        private string $type, // '02' = percentual até data
        private \DateTimeImmutable $dueDate,
        private float $amount,
        private float $percentage
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDueDate(): \DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getPercentage(): float
    {
        return $this->percentage;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'due_date' => $this->dueDate->format('Y-m-d'),
            'amount' => $this->amount,
            'percentage' => $this->percentage,
        ];
    }
}