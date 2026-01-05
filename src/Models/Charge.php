<?php

declare(strict_types=1);

namespace ItauBoletoPix\Models;

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
