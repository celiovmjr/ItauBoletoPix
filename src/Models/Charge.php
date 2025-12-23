<?php

declare(strict_types=1);

namespace ItauBoletoPix\Models;

/**
 * Juros do Boleto
 */
class Interest
{
    public function __construct(
        private string $type, // '93' = valor por dia
        private float $amountPerDay
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAmountPerDay(): float
    {
        return $this->amountPerDay;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'amount_per_day' => $this->amountPerDay,
        ];
    }
}

/**
 * Multa do Boleto
 */
class Fine
{
    public function __construct(
        private string $type, // '02' = percentual
        private float $percentage
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPercentage(): float
    {
        return $this->percentage;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'percentage' => $this->percentage,
        ];
    }
}

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

/**
 * Configurações de cobrança do Boleto
 */
class Charge
{
    public function __construct(
        private ?Interest $interest = null,
        private ?Fine $fine = null,
        private ?Discount $discount = null,
        private array $messages = []
    ) {
    }

    public function getInterest(): ?Interest
    {
        return $this->interest;
    }

    public function getFine(): ?Fine
    {
        return $this->fine;
    }

    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function hasInterest(): bool
    {
        return $this->interest !== null;
    }

    public function hasFine(): bool
    {
        return $this->fine !== null;
    }

    public function hasDiscount(): bool
    {
        return $this->discount !== null;
    }

    public function toArray(): array
    {
        return [
            'interest' => $this->interest?->toArray(),
            'fine' => $this->fine?->toArray(),
            'discount' => $this->discount?->toArray(),
            'messages' => $this->messages,
        ];
    }
}
