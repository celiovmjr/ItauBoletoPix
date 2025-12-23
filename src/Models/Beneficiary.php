<?php

declare(strict_types=1);

namespace ItauBoletoPix\Models;

use ItauBoletoPix\Enums\WalletCode;

/**
 * Modelo do Beneficiário (quem recebe o pagamento)
 */
class Beneficiary
{
    public function __construct(
        private string $agency,
        private string $account,
        private string $accountDigit,
        private string $pixKey,
        private WalletCode $walletCode = WalletCode::REGISTERED_109,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (\strlen($this->agency) !== 4) {
            throw new \InvalidArgumentException('Agência deve ter 4 dígitos');
        }

        if (\strlen($this->account) !== 7) {
            throw new \InvalidArgumentException('Conta deve ter 7 dígitos');
        }

        if (\strlen($this->accountDigit) !== 1) {
            throw new \InvalidArgumentException('Dígito da conta deve ter 1 caractere');
        }
    }

    public function getId(): string
    {
        return "{$this->agency}{$this->account}{$this->accountDigit}";
    }

    public function getAgency(): string
    {
        return $this->agency;
    }

    public function getAccount(): string
    {
        return $this->account;
    }

    public function getAccountDigit(): string
    {
        return $this->accountDigit;
    }

    public function getWalletCode(): string
    {
        return $this->walletCode->value;
    }

    public function getPixKey(): string
    {
        return $this->pixKey;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'agency' => $this->getAgency(),
            'account' => $this->getAccount(),
            'account_digit' => $this->getAccountDigit(),
            'wallet_code' => $this->getWalletCode(),
            'pix_key' => $this->getPixKey(),
        ];
    }
}
