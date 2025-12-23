<?php

declare(strict_types=1);

namespace ItauBoletoPix\Tests\Unit;

use ItauBoletoPix\Models\{
    Address,
    Beneficiary,
    Payer,
    PhysicalPerson
};
use PHPUnit\Framework\TestCase;

/**
 * Testes para serviços (com mock)
 */
class ServiceTest extends TestCase
{
    public function testBoletoGenerationWithMockedGateway(): void
    {
        // Mock do gateway
        $mockGateway = $this->createMock(\ItauBoletoPix\Contracts\PaymentGatewayInterface::class);

        $mockGateway
            ->expects($this->once())
            ->method('sendBoletoRequest')
            ->willReturn([
                'id_boleto' => 'BOLETO123',
                'codigo_barras' => '12345678901234567890',
                'linha_digitavel' => '12345.67890 12345.678901',
                'qr_code_pix' => 'PIX_DATA',
            ]);

        // Criar serviço com mock
        $service = new \ItauBoletoPix\Services\BoletoGenerationService($mockGateway);

        // Criar request
        $request = $this->createBoletoRequest();

        // Executar
        $response = $service->createBoleto($request);

        // Verificar
        $this->assertEquals('BOLETO123', $response->getId());
        $this->assertNotEmpty($response->getBarcode());
    }

    private function createBoletoRequest()
    {
        $beneficiary = new Beneficiary(
            '111100222223',
            '1111',
            '0022222',
            '3',
            '109',
            'test@test.com'
        );

        $address = new Address(
            'Rua X',
            'Bairro',
            'Cidade',
            'SP',
            '12345678'
        );

        $person = new PhysicalPerson(
            'João Silva',
            '12345678900',
            $address
        );

        $payer = new Payer($person);

        return new \ItauBoletoPix\DTOs\BoletoRequestDTO(
            beneficiary: $beneficiary,
            payer: $payer,
            ourNumber: '00000001',
            yourNumber: '000001',
            amount: 150.00,
            issueDate: new \DateTimeImmutable(),
            dueDate: new \DateTimeImmutable('+30 days')
        );
    }
}
