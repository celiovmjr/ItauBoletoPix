<?php

declare(strict_types=1);

namespace ItauBoletoPix\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Testes para webhook handler
 */
class WebhookTest extends TestCase
{
    public function testWebhookPayloadParsing(): void
    {
        $handler = new \ItauBoletoPix\Webhooks\ItauWebhookHandler();

        $rawPayload = [
            'tipo_notificacao' => 'BAIXA_EFETIVA',
            'nosso_numero' => '00000001',
            'data_pagamento' => '2025-08-15',
            'valor_pago' => 15000, // centavos
        ];

        $payload = $handler->parsePayload($rawPayload);

        $this->assertEquals('BAIXA_EFETIVA', $payload->getEventType());
        $this->assertEquals('00000001', $payload->getOurNumber());
        $this->assertEquals(150.00, $payload->getPaidAmount());
        $this->assertTrue($payload->isPaid());
    }

    public function testWebhookEventDispatch(): void
    {
        $handler = new \ItauBoletoPix\Webhooks\ItauWebhookHandler();

        $called = false;

        $handler->on('paid', function ($payload) use (&$called): void {
            $called = true;
            $this->assertEquals('00000001', $payload->getOurNumber());
        });

        $rawPayload = [
            'tipo_notificacao' => 'BAIXA_EFETIVA',
            'nosso_numero' => '00000001',
            'data_pagamento' => '2025-08-15',
            'valor_pago' => 15000,
        ];

        $handler->handle($rawPayload);

        $this->assertTrue($called);
    }
}
