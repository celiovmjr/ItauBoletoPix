<?php

declare(strict_types=1);

namespace ItauBoletoPix\Webhooks;

use ItauBoletoPix\Contracts\WebhookHandlerInterface;
use ItauBoletoPix\DTOs\WebhookPayload;
use ItauBoletoPix\Exceptions\WebhookException;
use Psr\Log\LoggerInterface;

/**
 * Handler para webhooks do Itaú
 */
class ItauWebhookHandler implements WebhookHandlerInterface
{
    private array $listeners = [];

    public function __construct(
        private ?LoggerInterface $logger = null
    ) {
    }

    public function handle(array $rawPayload): bool
    {
        try {
            $this->logger?->info('Processando webhook', [
                'payload' => $rawPayload,
            ]);

            // Valida estrutura básica
            $this->validatePayload($rawPayload);

            // Transforma em DTO
            $payload = $this->parsePayload($rawPayload);

            // Dispara evento correspondente
            $this->dispatchEvent($payload);

            $this->logger?->info('Webhook processado com sucesso', [
                'event_type' => $payload->getEventType(),
                'our_number' => $payload->getOurNumber(),
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger?->error('Erro ao processar webhook', [
                'error' => $e->getMessage(),
                'payload' => $rawPayload,
            ]);

            throw new WebhookException(
                "Falha ao processar webhook: {$e->getMessage()}",
                0,
                ['payload' => $rawPayload],
                $e
            );
        }
    }

    public function validateSignature(array $payload, string $signature): bool
    {
        // Implementar validação de assinatura conforme documentação do Itaú
        // Por enquanto, retorna true
        return true;
    }

    public function parsePayload(array $rawPayload): WebhookPayload
    {
        return new WebhookPayload(
            eventType: $rawPayload['tipo_notificacao'] ?? 'UNKNOWN',
            ourNumber: $rawPayload['nosso_numero'] ?? '',
            paymentDate: $rawPayload['data_pagamento'] ?? null,
            paidAmount: isset($rawPayload['valor_pago'])
                ? (float)$rawPayload['valor_pago'] / 100
                : null,
            rawData: $rawPayload
        );
    }

    public function on(string $event, callable $callback): void
    {
        if (! isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $callback;
    }

    /**
     * Valida estrutura do payload
     */
    private function validatePayload(array $payload): void
    {
        $requiredFields = ['tipo_notificacao', 'nosso_numero'];

        foreach ($requiredFields as $field) {
            if (! isset($payload[$field])) {
                throw new WebhookException(
                    "Campo obrigatório ausente: {$field}",
                    0,
                    ['payload' => $payload]
                );
            }
        }
    }

    /**
     * Dispara evento para listeners registrados
     */
    private function dispatchEvent(WebhookPayload $payload): void
    {
        $eventType = $payload->getEventType();

        // Dispara listeners específicos do tipo de evento
        if (isset($this->listeners[$eventType])) {
            foreach ($this->listeners[$eventType] as $callback) {
                $callback($payload);
            }
        }

        // Dispara listeners genéricos (all)
        if (isset($this->listeners['all'])) {
            foreach ($this->listeners['all'] as $callback) {
                $callback($payload);
            }
        }

        // Eventos específicos com aliases
        if ($payload->isPaid() && isset($this->listeners['paid'])) {
            foreach ($this->listeners['paid'] as $callback) {
                $callback($payload);
            }
        }

        if ($payload->isCancelled() && isset($this->listeners['cancelled'])) {
            foreach ($this->listeners['cancelled'] as $callback) {
                $callback($payload);
            }
        }
    }
}
