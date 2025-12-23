<?php

declare(strict_types=1);

namespace ItauBoletoPix\Tests\Unit;

use ItauBoletoPix\Enums\WalletCode;
use ItauBoletoPix\Models\{Address, Beneficiary, Payer, PhysicalPerson};
use PHPUnit\Framework\TestCase;

/**
 * Testes para DTOs
 */
class DTOTest extends TestCase
{
    public function testBoletoRequestCreation(): void
    {
        $beneficiary = $this->createBeneficiary();
        $payer = $this->createPayer();

        $request = new \ItauBoletoPix\DTOs\BoletoRequestDTO(
            beneficiary: $beneficiary,
            payer: $payer,
            ourNumber: '00000001',
            yourNumber: '000001',
            amount: 150.00,
            issueDate: new \DateTimeImmutable('2025-07-20'),
            dueDate: new \DateTimeImmutable('2025-08-20')
        );

        $this->assertEquals('00000001', $request->ourNumber);
        $this->assertEquals(150.00, $request->amount);
        $this->assertEquals('Efetivacao', $request->processStep);
    }

    public function testBoletoResponseCreation(): void
    {
        $response = new \ItauBoletoPix\DTOs\BoletoResponseDTO(
            id: 'BOLETO123',
            ourNumber: '00000001',
            barcode: '12345678901234567890123456789012345678901234',
            digitableLine: '12345.67890 12345.678901 12345.678901 1 23456789012345',
            pixCopyPaste: 'PIX_COPY_PASTE_CODE_DATA',
            pixQrCode: 'PIX_QR_CODE_DATA',
            pixTxid: 'PIX_TXID_DATA',
            amount: '150.00',
            dueDate: ''
        );

        $this->assertEquals('BOLETO123', $response->id);
        $this->assertEquals('00000001', $response->ourNumber);
        $this->assertNotEmpty($response->barcode);
    }

    private function createBeneficiary()
    {
        return new Beneficiary(
            agency: '1111',
            account: '0022222',
            accountDigit: '3',
            walletCode: WalletCode::REGISTERED_109,
            pixKey: 'test@example.com'
        );
    }

    private function createPayer()
    {
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

        return new Payer($person);
    }
}
