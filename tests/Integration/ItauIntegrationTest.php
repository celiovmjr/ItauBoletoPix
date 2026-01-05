<?php

declare(strict_types=1);

namespace ItauBoletoPix\Tests\Integration;

use ItauBoletoPix\DTOs\BoletoRequestDTO as BoletoRequest;
use ItauBoletoPix\Enums\WalletCode;
use ItauBoletoPix\Gateways\ItauBoletoGateway;
use ItauBoletoPix\Models\{
    Address,
    Beneficiary,
    Charge,
    Fine,
    Interest,
    Payer,
    PhysicalPerson
};
use ItauBoletoPix\Services\BoletoGenerationService;
use PHPUnit\Framework\TestCase;

/**
 * Testes de integração com a API do Itaú
 *
 * IMPORTANTE: Estes testes fazem chamadas reais à API em sandbox
 * Configure as variáveis de ambiente antes de executar
 */
class ItauIntegrationTest extends TestCase
{
    private ItauBoletoGateway $gateway;
    private BoletoGenerationService $service;
    private Beneficiary $beneficiary;

    protected function setUp(): void
    {
        parent::setUp();

        // Pular testes se não houver credenciais configuradas
        if (! getenv('ITAU_CLIENT_ID')) {
            $this->markTestSkipped('Credenciais do Itaú não configuradas');
        }

        $this->gateway = new ItauBoletoGateway(
            clientId: getenv('ITAU_CLIENT_ID'),
            clientSecret: getenv('ITAU_CLIENT_SECRET'),
            certificatePath: getenv('ITAU_CERTIFICATE_PATH'),
            certificateKeyPath: getenv('ITAU_CERTIFICATE_KEY_PATH'),
            sandbox: true
        );

        $this->service = new BoletoGenerationService($this->gateway);

        $this->beneficiary = new Beneficiary(
            agency: getenv('ITAU_BENEFICIARY_AGENCY'),
            account: getenv('ITAU_BENEFICIARY_ACCOUNT'),
            accountDigit: getenv('ITAU_BENEFICIARY_ACCOUNT_DIGIT'),
            walletCode: WalletCode::REGISTERED_109,
            pixKey: getenv('ITAU_PIX_KEY')
        );
    }

    /**
     * @test
     */
    public function it_can_authenticate_with_itau_api(): void
    {
        $token = $this->gateway->authenticate();

        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }

    /**
     * @test
     */
    public function it_can_test_connection(): void
    {
        $result = $this->gateway->testConnection();

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_can_create_simple_boleto(): void
    {
        $request = $this->createSimpleBoletoRequest();

        $response = $this->service->createBoleto($request);

        $this->assertNotEmpty($response->id);
        $this->assertNotEmpty($response->barcode);
        $this->assertNotEmpty($response->digitableLine);
        $this->assertNotEmpty($response->pixQrCode);
    }

    /**
     * @test
     */
    public function it_can_create_boleto_with_interest_and_fine(): void
    {
        $interest = new Interest(
            type: '93',
            amountPerDay: 2.00
        );

        $fine = new Fine(
            type: '02',
            percentage: 2.0
        );

        $charge = new Charge(
            interest: $interest,
            fine: $fine,
            messages: [
                'Juros de R$ 2,00 por dia',
                'Multa de 2%',
            ]
        );

        $request = $this->createBoletoRequestWithCharge($charge);

        $response = $this->service->createBoleto($request);

        $this->assertNotEmpty($response->id);
    }

    /**
     * @test
     */
    public function it_handles_invalid_credentials(): void
    {
        $this->expectException(\ItauBoletoPix\Exceptions\AuthenticationException::class);

        $invalidGateway = new ItauBoletoGateway(
            clientId: 'invalid',
            clientSecret: 'invalid',
            certificatePath: getenv('ITAU_CERTIFICATE_PATH'),
            certificateKeyPath: getenv('ITAU_CERTIFICATE_KEY_PATH'),
            sandbox: true
        );

        $invalidGateway->authenticate();
    }

    /**
     * @test
     */
    public function it_validates_required_fields(): void
    {
        $this->expectException(\ItauBoletoPix\Exceptions\ValidationException::class);

        // Criar request inválido (sem pagador)
        $request = new BoletoRequest(
            beneficiary: $this->beneficiary,
            payer: null, // Inválido!
            ourNumber: '00000001',
            yourNumber: '000001',
            amount: 100.00,
            issueDate: new \DateTimeImmutable(),
            dueDate: new \DateTimeImmutable('+30 days')
        );

        $this->service->createBoleto($request);
    }

    /**
     * @test
     */
    public function it_formats_values_correctly(): void
    {
        $request = $this->createSimpleBoletoRequest();

        // Valor deve ser formatado internamente
        $this->assertEquals(150.00, $request->amount);

        $response = $this->service->createBoleto($request);

        $this->assertNotEmpty($response->barcode);
    }

    /**
     * @test
     */
    public function it_generates_unique_boletos(): void
    {
        $request1 = $this->createBoletoRequest('00000001', '000001');
        $request2 = $this->createBoletoRequest('00000002', '000002');

        $response1 = $this->service->createBoleto($request1);
        $response2 = $this->service->createBoleto($request2);

        $this->assertNotEquals($response1->id, $response2->id);
        $this->assertNotEquals($response1->ourNumber, $response2->ourNumber);
    }

    // Helper methods

    private function createSimpleBoletoRequest(): BoletoRequest
    {
        return $this->createBoletoRequest('00000001', '000001');
    }

    private function createBoletoRequest(
        string $ourNumber,
        string $yourNumber
    ): BoletoRequest {
        $address = new Address(
            street: 'Av Paulista, 1000',
            neighborhood: 'Bela Vista',
            city: 'São Paulo',
            state: 'SP',
            zipCode: '01310100'
        );

        $person = new PhysicalPerson(
            name: 'João Silva Santos',
            document: '12345678900',
            address: $address
        );

        $payer = new Payer($person);

        return new BoletoRequest(
            beneficiary: $this->beneficiary,
            payer: $payer,
            ourNumber: $ourNumber,
            yourNumber: $yourNumber,
            amount: 150.00,
            issueDate: new \DateTimeImmutable(),
            dueDate: new \DateTimeImmutable('+30 days')
        );
    }

    private function createBoletoRequestWithCharge(Charge $charge): BoletoRequest
    {
        $request = $this->createBoletoRequest('00000003', '000003');

        return new BoletoRequest(
            beneficiary: $request->beneficiary,
            payer: $request->payer,
            ourNumber: $request->ourNumber,
            yourNumber: $request->yourNumber,
            amount: $request->amount,
            issueDate: $request->issueDate,
            dueDate: $request->dueDate,
            charge: $charge
        );
    }
}

/**
 * Para executar os testes de integração:
 *
 * 1. Configure as variáveis de ambiente no phpunit.xml:
 *
 * <php>
 *     <env name="ITAU_CLIENT_ID" value="seu-client-id"/>
 *     <env name="ITAU_CLIENT_SECRET" value="seu-client-secret"/>
 *     <env name="ITAU_CERTIFICATE_PATH" value="/path/to/cert.crt"/>
 *     <env name="ITAU_CERTIFICATE_KEY_PATH" value="/path/to/key.key"/>
 *     <env name="ITAU_BENEFICIARY_ID" value="111100222223"/>
 *     <env name="ITAU_BENEFICIARY_AGENCY" value="1111"/>
 *     <env name="ITAU_BENEFICIARY_ACCOUNT" value="0022222"/>
 *     <env name="ITAU_BENEFICIARY_ACCOUNT_DIGIT" value="3"/>
 *     <env name="ITAU_PIX_KEY" value="test@example.com"/>
 * </php>
 *
 * 2. Execute os testes:
 *
 * ./vendor/bin/phpunit tests/Integration/ItauIntegrationTest.php
 *
 * NOTA: Estes testes fazem chamadas reais à API em sandbox.
 *       Use com moderação para não exceder limites de rate.
 */
