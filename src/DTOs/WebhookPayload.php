<?php

declare(strict_types=1);

namespace ItauBoletoPix\DTOs;

/**
 * DTO para payload de webhooks
 */
class WebhookPayload
{
    public function __construct(
        private string $eventType,
        private string $ourNumber,
        private ?string $paymentDate = null,
        private ?float $paidAmount = null,
        private array $rawData = []
    ) {
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getOurNumber(): string
    {
        return $this->ourNumber;
    }

    public function getPaymentDate(): ?string
    {
        return $this->paymentDate;
    }

    public function getPaidAmount(): ?float
    {
        return $this->paidAmount;
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }

    public function isPaid(): bool
    {
        return $this->eventType === 'BAIXA_EFETIVA';
    }

    public function isCancelled(): bool
    {
        return $this->eventType === 'BAIXA_OPERACIONAL';
    }

    public function toArray(): array
    {
        return [
            'event_type' => $this->eventType,
            'our_number' => $this->ourNumber,
            'payment_date' => $this->paymentDate,
            'paid_amount' => $this->paidAmount,
            'raw_data' => $this->rawData,
        ];
    }
}
