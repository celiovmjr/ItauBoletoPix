<?php

declare(strict_types=1);

/**
 * EXEMPLO BÁSICO - USO MAIS SIMPLES POSSÍVEL (COM ENUMS)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use ItauBoletoPix\DTOs\BoletoRequestDTO;
use ItauBoletoPix\Enums\{ProcessStep, WalletCode};
use ItauBoletoPix\Gateways\ItauBoletoGateway;
use ItauBoletoPix\Models\{Address, Beneficiary, Payer, PhysicalPerson};
use ItauBoletoPix\Services\BoletoGenerationService;

// ============================================================================
// LOAD ENV
// ============================================================================

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// ============================================================================
// 1. CONFIGURAR GATEWAY
// ============================================================================

$gateway = new ItauBoletoGateway(
    clientId: $_ENV['ITAU_CLIENT_ID'],
    clientSecret: $_ENV['ITAU_CLIENT_SECRET'],
    certificatePath: $_ENV['ITAU_CERTIFICATE_PATH'],
    certificateKeyPath: $_ENV['ITAU_CERTIFICATE_KEY_PATH'],
    sandbox: true
);

$boletoService = new BoletoGenerationService($gateway);

// ============================================================================
// 2. CONFIGURAR BENEFICIÁRIO (SUA EMPRESA)
// ============================================================================

$beneficiary = new Beneficiary(
    agency: $_ENV['ITAU_BENEFICIARY_AGENCY'],
    account: $_ENV['ITAU_BENEFICIARY_ACCOUNT'],
    accountDigit: $_ENV['ITAU_BENEFICIARY_ACCOUNT_DIGIT'],
    walletCode: WalletCode::REGISTERED_109,
    pixKey: $_ENV['ITAU_PIX_KEY']
);

// ============================================================================
// 3. CRIAR PAGADOR
// ============================================================================

$address = new Address(
    street: 'Av Paulista, 1000',
    neighborhood: 'Bela Vista',
    city: 'São Paulo',
    state: 'SP',
    zipCode: '01310-100'
);

$person = new PhysicalPerson(
    name: 'Célio Vieira de Magalhães Junior',
    document: '16622752770',
    address: $address
);

$payer = new Payer($person);

// ============================================================================
// 4. CRIAR REQUEST DO BOLETO
// ============================================================================

$request = new BoletoRequestDTO(
    beneficiary: $beneficiary,
    payer: $payer,
    ourNumber: str_pad((string) random_int(1, 99999999), 8, '0', STR_PAD_LEFT),
    yourNumber: str_pad((string) random_int(1, 99999999), 8, '0', STR_PAD_LEFT),
    amount: 150.00,
    issueDate: new DateTimeImmutable(),
    dueDate: new DateTimeImmutable('+30 days'),
    processStep: ProcessStep::SIMULATION
);

// ============================================================================
// 5. GERAR BOLETO
// ============================================================================

try {
    $response = $boletoService->createBoleto($request);

    echo "✅ BOLETO PIX GERADO COM SUCESSO!\n\n";

    echo "ID Interno: {$response->id}\n";
    echo "Nosso Número: {$response->ourNumber}\n";
    echo "Código de Barras: {$response->barcode}\n";
    echo "Linha Digitável: {$response->digitableLine}\n";

    echo "\n--- PIX ---\n";
    echo "PIX Copia e Cola: {$response->pixCopyPaste}\n";
    echo "PIX TXID: {$response->pixTxid}\n";
    echo "QR Code PIX: {$response->pixQrCode}\n";

    echo "\n--- BOLETO ---\n";
    echo "Valor: {$response->amount}\n";
    echo "Vencimento: {$response->dueDate}\n";

} catch (Exception $e) {
    echo "❌ ERRO: {$e->getMessage()}\n";
}
