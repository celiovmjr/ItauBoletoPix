<?php

declare(strict_types=1);

namespace ItauBoletoPix\Exceptions;

/**
 * Exceção para erros de gateway/API
 */
class GatewayException extends BoletoException
{
    private ?int $httpCode = null;

    public function __construct(
        string $message,
        int $code = 0,
        ?int $httpCode = null,
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $context, $previous);
        $this->httpCode = $httpCode;
    }

    public function getHttpCode(): ?int
    {
        return $this->httpCode;
    }
}
