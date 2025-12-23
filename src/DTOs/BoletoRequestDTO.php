<?php

declare(strict_types=1);

namespace ItauBoletoPix\DTOs;

use ItauBoletoPix\Enums\ProcessStep;
use ItauBoletoPix\Models\Beneficiary;
use ItauBoletoPix\Models\Charge;
use ItauBoletoPix\Models\Payer;

/**
 * DTO para requisição de criação de boleto
 */
readonly class BoletoRequestDTO
{
    public function __construct(
        public Beneficiary $beneficiary,
        public Payer $payer,
        public string $ourNumber,
        public string $yourNumber,
        public float $amount,
        public \DateTimeImmutable $issueDate,
        public \DateTimeImmutable $dueDate,
        public ?Charge $charge = null,
        public ProcessStep $processStep = ProcessStep::REGISTRATION
    ) {
    }

    public function toArray(): array
    {
        return [
            'beneficiary' => $this->beneficiary->toArray(),
            'payer' => $this->payer->toArray(),
            'our_number' => $this->ourNumber,
            'your_number' => $this->yourNumber,
            'amount' => $this->amount,
            'issue_date' => $this->issueDate->format('Y-m-d'),
            'due_date' => $this->dueDate->format('Y-m-d'),
            'charge' => $this->charge?->toArray(),
            'process_step' => $this->processStep->value,
        ];
    }
}
