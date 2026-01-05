<?php

declare(strict_types=1);

namespace ItauBoletoPix\Models;

/**
 * Pessoa Física
 */
class PhysicalPerson extends Person
{
    protected function validate(): void
    {
        if (! $this->isValidCPF($this->document)) {
            throw new \InvalidArgumentException("CPF inválido: {$this->document}");
        }
    }

    public function getDocumentType(): string
    {
        return 'F';
    }

    private function isValidCPF(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        // Validação básica - implementar validação completa se necessário
        return ! preg_match('/^(\d)\1{10}$/', $cpf);
    }
}
