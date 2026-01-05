<?php

declare(strict_types=1);

namespace ItauBoletoPix\Models;

use ItauBoletoPix\Contracts\PersonInterface;

/**
 * Classe abstrata para pessoa
 */
abstract class Person implements PersonInterface
{
    public function __construct(
        protected string $name,
        protected string $document,
        protected Address $address
    ) {
        $this->validate();
    }

    abstract protected function validate(): void;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDocument(bool $unmasked = true): ?string
    {
        return $unmasked
            ? preg_replace('/\D/', '', $this->document)
            : $this->document;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * Serializa para array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'document' => $this->document,
            'document_type' => $this->getDocumentType(),
            'address' => $this->address->toArray(),
        ];
    }
}
