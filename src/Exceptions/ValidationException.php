<?php

declare(strict_types=1);

namespace ItauBoletoPix\Exceptions;

/**
 * Exceção para erros de validação
 */
class ValidationException extends BoletoException
{
    private array $errors = [];

    public function __construct(
        string $message,
        array $errors = [],
        array $context = []
    ) {
        parent::__construct($message, 0, $context);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
