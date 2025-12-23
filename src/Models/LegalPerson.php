<?php

declare(strict_types=1);

namespace ItauBoletoPix\Models;

/**
 * Pessoa Jurídica
 */
class LegalPerson extends Person
{
    protected function validate(): void
    {
        if (! $this->isValidCNPJ($this->document)) {
            throw new \InvalidArgumentException("CNPJ inválido: {$this->document}");
        }
    }

    public function getDocumentType(): string
    {
        return 'J';
    }

    private function isValidCNPJ(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Validação básica - implementar validação completa se necessário
        return ! preg_match('/^(\d)\1{13}$/', $cnpj);
    }
}
