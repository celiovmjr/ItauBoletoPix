<?php

declare(strict_types=1);

namespace ItauBoletoPix\Contracts;

use ItauBoletoPix\DTOs\WebhookPayload;

/**
 * Interface para processamento de webhooks
 *
 * Define como webhooks de diferentes provedores devem ser tratados
 */
interface WebhookHandlerInterface
{
    /**
     * Processa um webhook recebido
     *
     * @param  array                                      $rawPayload Dados brutos do webhook
     * @return bool                                       True se processado com sucesso
     * @throws \ItauBoletoPix\Exceptions\WebhookException
     */
    public function handle(array $rawPayload): bool;

    /**
     * Valida assinatura do webhook
     *
     * @param  array  $payload   Payload recebido
     * @param  string $signature Assinatura recebida
     * @return bool   True se válida
     */
    public function validateSignature(array $payload, string $signature): bool;

    /**
     * Transforma payload bruto em DTO estruturado
     *
     * @param  array          $rawPayload Dados brutos
     * @return WebhookPayload DTO estruturado
     */
    public function parsePayload(array $rawPayload): WebhookPayload;

    /**
     * Registra listeners para eventos específicos
     *
     * @param string   $event    Tipo de evento (paid, cancelled, etc)
     * @param callable $callback Função a ser executada
     */
    public function on(string $event, callable $callback): void;
}
