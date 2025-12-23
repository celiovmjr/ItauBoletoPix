<?php

declare(strict_types=1);

namespace ItauBoletoPix\Models;

/**
 * Modelo de Endereço
 */
class Address
{
    public function __construct(
        private string $street,
        private string $neighborhood,
        private string $city,
        private string $state,
        private string $zipCode
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (strlen($this->state) !== 2) {
            throw new \InvalidArgumentException("Estado deve ter 2 caracteres: {$this->state}");
        }

        $zipCode = preg_replace('/[^0-9]/', '', $this->zipCode);
        if (strlen($zipCode) !== 8) {
            throw new \InvalidArgumentException("CEP inválido: {$this->zipCode}");
        }
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getNeighborhood(): string
    {
        return $this->neighborhood;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getState(): string
    {
        return strtoupper($this->state);
    }

    public function getZipCode(): string
    {
        return preg_replace('/[^0-9]/', '', $this->zipCode);
    }

    public function format(): string
    {
        return sprintf(
            '%s, %s, %s - %s, %s',
            $this->street,
            $this->neighborhood,
            $this->city,
            $this->state,
            $this->zipCode
        );
    }

    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'state' => $this->getState(),
            'zip_code' => $this->getZipCode(),
        ];
    }
}
