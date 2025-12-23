<?php

declare(strict_types=1);

namespace ItauBoletoPix\Exceptions;

/**
 * Exceção base para erros de boleto
 */
class BoletoException extends \Exception
{
    private array $context = [];

    public function __construct(
        string $message,
        int $code = 0,
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getDetailedMessage(): string
    {
        $message = $this->getMessage();

        if (! empty($this->context)) {
            $message .= "\n\nContexto:\n" . json_encode(
                $this->context,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );
        }

        return $message;
    }
}
