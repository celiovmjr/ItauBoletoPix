<?php

declare(strict_types=1);

/**
 * EXEMPLO COMPLETO DE USO DO COMPONENTE DE BOLETOS PIX
 *
 * Este arquivo demonstra como usar o componente em diferentes cenários
 */

require_once 'vendor/autoload.php';

use ItauBoletoPix\DTOs\BoletoRequestDTO as BoletoRequest;
use ItauBoletoPix\Enums\ProcessStep;
use ItauBoletoPix\Enums\WalletCode;
use ItauBoletoPix\Gateways\ItauBoletoGateway;
use ItauBoletoPix\Models\{
    Address,
    Beneficiary,
    Charge,
    Discount,
    Fine,
    Interest,
    LegalPerson,
    Payer,
    PhysicalPerson
};
use ItauBoletoPix\Services\BoletoGenerationService;
use ItauBoletoPix\Services\BoletoSchedulerService;
use ItauBoletoPix\Webhooks\ItauWebhookHandler;

// ============================================================================
// CONFIGURAÇÃO INICIAL
// ============================================================================

// 1. Criar o gateway do Itaú
$gateway = new ItauBoletoGateway(
    clientId: '949d4789-270f-467d-ae6c-4149ebf79cbb',
    clientSecret: '4e85a42a-52ae-4c48-adfd-7f8695e5478c',
    certificatePath: __DIR__ . '/certificados/certificado.crt',
    certificateKeyPath: __DIR__ . '/certificados/chave.key',
    sandbox: true
);

// 2. Criar o serviço de geração de boletos
$boletoService = new BoletoGenerationService($gateway);

// 3. Configurar beneficiário padrão (sua empresa)
$beneficiary = new Beneficiary(
    agency: '1111',
    account: '0022222',
    accountDigit: '3',
    walletCode: WalletCode::REGISTERED_109,
    pixKey: 'empresa@exemplo.com.br'
);

// ============================================================================
// EXEMPLO 1: GERAR BOLETO SIMPLES PARA PESSOA FÍSICA
// ============================================================================

echo "=== EXEMPLO 1: Boleto Simples - Pessoa Física ===\n\n";

// Criar pessoa física (pagador)
$address = new Address(
    street: 'Av Paulista, 1000',
    neighborhood: 'Bela Vista',
    city: 'São Paulo',
    state: 'SP',
    zipCode: '01310-100'
);

$person = new PhysicalPerson(
    name: 'João da Silva Santos',
    document: '123.456.789-00',
    address: $address
);

$payer = new Payer($person);

// Criar request de boleto
$request = new BoletoRequest(
    beneficiary: $beneficiary,
    payer: $payer,
    ourNumber: '00000001',
    yourNumber: '000001',
    amount: 150.00,
    issueDate: new DateTimeImmutable('2025-07-20'),
    dueDate: new DateTimeImmutable('2025-08-20'),
    processStep: ProcessStep::SIMULATION
);

try {
    $response = $boletoService->createBoleto($request);

    echo "✅ Boleto gerado com sucesso!\n";
    echo "ID: {$response->getId()}\n";
    echo "Nosso Número: {$response->getOurNumber()}\n";
    echo "Código de Barras: {$response->getBarcode()}\n";
    echo "Linha Digitável: {$response->getDigitableLine()}\n";
    echo "QR Code PIX: {$response->getPixQrCode()}\n\n";

} catch (Exception $e) {
    echo "❌ Erro: {$e->getMessage()}\n\n";
}

// ============================================================================
// EXEMPLO 2: BOLETO COMPLETO COM JUROS, MULTA E DESCONTO
// ============================================================================

echo "=== EXEMPLO 2: Boleto Completo - Pessoa Jurídica ===\n\n";

// Criar pessoa jurídica
$companyAddress = new Address(
    street: 'Rua das Flores, 123',
    neighborhood: 'Centro',
    city: 'São Paulo',
    state: 'SP',
    zipCode: '01234-567'
);

$company = new LegalPerson(
    name: 'Empresa XYZ LTDA',
    document: '12.345.678/0001-99',
    address: $companyAddress
);

$companyPayer = new Payer($company);

// Configurar juros de 1% ao mês (aproximadamente R$ 5,00 por dia em um boleto de R$ 500)
$interest = new Interest(
    type: '93', // Valor por dia
    amountPerDay: 5.00
);

// Configurar multa de 2%
$fine = new Fine(
    type: '02', // Percentual
    percentage: 2.0
);

// Configurar desconto de 5% se pagar até 10 dias antes
$discount = new Discount(
    type: '02', // Percentual até data
    dueDate: new DateTimeImmutable('2025-08-10'),
    amount: 25.00,
    percentage: 5.0
);

// Criar configuração de cobrança
$charge = new Charge(
    interest: $interest,
    fine: $fine,
    discount: $discount,
    messages: [
        'Não receber após o vencimento',
        'Juros de R$ 5,00 por dia de atraso',
        'Multa de 2% após vencimento',
        'Desconto de 5% até 10/08/2025',
    ]
);

$requestComplete = new BoletoRequest(
    beneficiary: $beneficiary,
    payer: $companyPayer,
    ourNumber: '00000002',
    yourNumber: '000002',
    amount: 500.00,
    issueDate: new DateTimeImmutable('2025-07-20'),
    dueDate: new DateTimeImmutable('2025-08-20'),
    charge: $charge,
    processStep: ProcessStep::SIMULATION
);

try {
    $responseComplete = $boletoService->createBoleto($requestComplete);

    echo "✅ Boleto completo gerado com sucesso!\n";
    echo "ID: {$responseComplete->getId()}\n";
    echo "Valor: R$ 500,00\n";
    echo "Desconto até 10/08: R$ 25,00 (5%)\n";
    echo "Juros após vencimento: R$ 5,00/dia\n";
    echo "Multa após vencimento: 2%\n\n";

} catch (Exception $e) {
    echo "❌ Erro: {$e->getMessage()}\n\n";
}

// ============================================================================
// EXEMPLO 3: GERAÇÃO AUTOMÁTICA MENSAL (DIA 01)
// ============================================================================

echo "=== EXEMPLO 3: Geração Automática Mensal ===\n\n";

// Implementar seu repositório de usuários
class MyUserRepository implements \ItauBoletoPix\Services\UserRepositoryInterface
{
    public function getActiveUsers(): array
    {
        // Retornar usuários do banco de dados
        return []; // Exemplo vazio
    }

    public function getLastOurNumber(): int
    {
        // Buscar do banco de dados
        return 1000;
    }

    public function saveLastOurNumber(int $number): void
    {
        // Salvar no banco de dados
    }
}

$userRepository = new MyUserRepository();

$scheduler = new BoletoSchedulerService(
    boletoService: $boletoService,
    userRepository: $userRepository,
    defaultBeneficiary: $beneficiary
);

// Executar geração mensal (idealmente via cron no dia 01)
if ((int)date('d') === 1) {
    echo "📅 Hoje é dia 01 - Gerando boletos mensais...\n\n";

    try {
        $results = $scheduler->generateMonthlyBoletos();

        echo "✅ Geração concluída!\n";
        echo "Total de usuários: {$results['total']}\n";
        echo 'Sucessos: ' . count($results['success']) . "\n";
        echo 'Falhas: ' . count($results['failed']) . "\n\n";

        if (! empty($results['failed'])) {
            echo "❌ Falhas:\n";
            foreach ($results['failed'] as $failed) {
                echo "- {$failed['user_name']}: {$failed['error']}\n";
            }
        }

    } catch (Exception $e) {
        echo "❌ Erro na geração mensal: {$e->getMessage()}\n\n";
    }
}

// ============================================================================
// EXEMPLO 4: CONFIGURAR WEBHOOKS
// ============================================================================

echo "=== EXEMPLO 4: Configurar Webhooks ===\n\n";

$webhookHandler = new ItauWebhookHandler();

// Registrar listener para pagamentos
$webhookHandler->on('paid', static function ($payload): void {
    echo "💰 Boleto pago!\n";
    echo "Nosso Número: {$payload->getOurNumber()}\n";
    echo "Valor: R$ {$payload->getPaidAmount()}\n";
    echo "Data: {$payload->getPaymentDate()}\n";

    // Aqui você pode:
    // - Atualizar status no banco de dados
    // - Enviar email de confirmação
    // - Liberar acesso ao sistema
    // - etc
});

// Registrar listener para cancelamentos
$webhookHandler->on('cancelled', static function ($payload): void {
    echo "❌ Boleto cancelado!\n";
    echo "Nosso Número: {$payload->getOurNumber()}\n";

    // Ações para cancelamento
});

// Processar webhook recebido
$webhookData = [
    'tipo_notificacao' => 'BAIXA_EFETIVA',
    'nosso_numero' => '00000001',
    'data_pagamento' => '2025-08-15',
    'valor_pago' => 15000, // Em centavos
];

try {
    $webhookHandler->handle($webhookData);
    echo "✅ Webhook processado!\n\n";
} catch (Exception $e) {
    echo "❌ Erro ao processar webhook: {$e->getMessage()}\n\n";
}

// ============================================================================
// EXEMPLO 5: TESTAR CONEXÃO
// ============================================================================

echo "=== EXEMPLO 5: Testar Conexão ===\n\n";

if ($gateway->testConnection()) {
    echo "✅ Conexão com API do Itaú estabelecida com sucesso!\n";
} else {
    echo "❌ Falha na conexão com API do Itaú\n";
}
