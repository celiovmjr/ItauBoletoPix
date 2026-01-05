<?php

declare(strict_types=1);

namespace ItauBoletoPix\Models;

/**
 * Modelo principal do Boleto PIX
 */
class Boleto
{
    private ?string $id = null;
    private ?string $barcode = null;
    private ?string $digitableLine = null;
    private string $status = 'pending';

    public function __construct(
        private Beneficiary $beneficiary,
        private Payer $payer,
        private string $ourNumber,
        private string $yourNumber,
        private float $amount,
        private \DateTimeImmutable $issueDate,
        private \DateTimeImmutable $dueDate,
        private ?Charge $charge = null,
        private string $processStep = 'Efetivacao',
        private string $boletoType = 'a vista',
        private string $walletCode = '109',
        private string $speciesCode = '01'
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->amount <= 0) {
            throw new \InvalidArgumentException('Valor do boleto deve ser maior que zero');
        }

        if ($this->dueDate < $this->issueDate) {
            throw new \InvalidArgumentException('Data de vencimento não pode ser anterior à data de emissão');
        }

        if (strlen($this->ourNumber) > 8) {
            throw new \InvalidArgumentException('Nosso número deve ter no máximo 8 dígitos');
        }
    }

    // Getters
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getBeneficiary(): Beneficiary
    {
        return $this->beneficiary;
    }

    public function getPayer(): Payer
    {
        return $this->payer;
    }

    public function getOurNumber(): string
    {
        return str_pad($this->ourNumber, 8, '0', STR_PAD_LEFT);
    }

    public function getYourNumber(): string
    {
        return $this->yourNumber;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getIssueDate(): \DateTimeImmutable
    {
        return $this->issueDate;
    }

    public function getDueDate(): \DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function getCharge(): ?Charge
    {
        return $this->charge;
    }

    public function getProcessStep(): string
    {
        return $this->processStep;
    }

    public function getBoletoType(): string
    {
        return $this->boletoType;
    }

    public function getWalletCode(): string
    {
        return $this->walletCode;
    }

    public function getSpeciesCode(): string
    {
        return $this->speciesCode;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function getDigitableLine(): ?string
    {
        return $this->digitableLine;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    // Setters para dados retornados pela API
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setBarcode(string $barcode): void
    {
        $this->barcode = $barcode;
    }

    public function setDigitableLine(string $digitableLine): void
    {
        $this->digitableLine = $digitableLine;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    // Métodos auxiliares
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        return $this->dueDate < new \DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'beneficiary' => $this->beneficiary->toArray(),
            'payer' => $this->payer->toArray(),
            'our_number' => $this->getOurNumber(),
            'your_number' => $this->yourNumber,
            'amount' => $this->amount,
            'issue_date' => $this->issueDate->format('Y-m-d'),
            'due_date' => $this->dueDate->format('Y-m-d'),
            'charge' => $this->charge?->toArray(),
            'process_step' => $this->processStep,
            'boleto_type' => $this->boletoType,
            'wallet_code' => $this->walletCode,
            'species_code' => $this->speciesCode,
            'barcode' => $this->barcode,
            'digitable_line' => $this->digitableLine,
            'status' => $this->status,
        ];
    }
}
