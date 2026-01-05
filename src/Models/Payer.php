<?php

declare(strict_types=1);

namespace ItauBoletoPix\Models;

use ItauBoletoPix\Contracts\PersonInterface;

/**
 * Modelo do Pagador (quem paga o boleto)
 */
class Payer
{
    public function __construct(
        private PersonInterface $person
    ) {
    }

    public function getPerson(): PersonInterface
    {
        return $this->person;
    }

    public function getName(): string
    {
        return $this->person->getName();
    }

    public function getDocument(): string
    {
        return $this->person->getDocument();
    }

    public function getDocumentType(): string
    {
        return $this->person->getDocumentType();
    }

    public function getAddress(): Address
    {
        return $this->person->getAddress();
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'document' => $this->getDocument(),
            'document_type' => $this->getDocumentType(),
            'address' => $this->getAddress()->toArray(),
        ];
    }
}
