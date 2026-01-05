# Exemplos Práticos - Itaú Boleto PIX

## 📋 Índice

- [Configuração Inicial](#configuração-inicial)
- [Exemplos Básicos](#exemplos-básicos)
- [Exemplos Avançados](#exemplos-avançados)
- [Casos de Uso Reais](#casos-de-uso-reais)
- [Integração com Frameworks](#integração-com-frameworks)
- [Testes e Debug](#testes-e-debug)

## ⚙️ Configuração Inicial

### Setup Básico

```php
<?php

require_once 'vendor/autoload.php';

use Dotenv\Dotenv;
use ItauBoletoPix\Gateways\ItauBoletoGateway;
use ItauBoletoPix\Services\BoletoGenerationService;

// Carregar variáveis de ambiente
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configurar gateway
$gateway = new ItauBoletoGateway(
    clientId: $_ENV['ITAU_CLIENT_ID'],
    clientSecret: $_ENV['ITAU_CLIENT_SECRET'],
    certificatePath: $_ENV['ITAU_CERTIFICATE_PATH'],
    certificateKeyPath: $_ENV['ITAU_CERTIFICATE_KEY_PATH'],
    sandbox: (bool)$_ENV['ITAU_SANDBOX']
);

// Configurar serviço
$boletoService = new BoletoGenerationService($gateway);
```

## 🎯 Exemplos Básicos

### 1. Boleto Simples - Pessoa Física

```php
<?php

use ItauBoletoPix\DTOs\BoletoRequestDTO;
use ItauBoletoPix\Enums\ProcessStep;
use ItauBoletoPix\Models\{Address, Beneficiary, Payer, PhysicalPerson};

// Beneficiário (sua empresa)
$beneficiary = new Beneficiary(
    agency: $_ENV['ITAU_BENEFICIARY_AGENCY'],
    account: $_ENV['ITAU_BENEFICIARY_ACCOUNT'],
    accountDigit: $_ENV['ITAU_BENEFICIARY_ACCOUNT_DIGIT'],
    pixKey: $_ENV['ITAU_PIX_KEY']
);

// Endereço do pagador
$address = new Address(
    street: 'Rua das Flores, 123',
    neighborhood: 'Centro',
    city: 'São Paulo',
    state: 'SP',
    zipCode: '01234-567'
);

// Pessoa física
$person = new PhysicalPerson(
    name: 'João da Silva Santos',
    document: '123.456.789-00',
    address: $address
);

$payer = new Payer($person);

// Request do boleto
$request = new BoletoRequestDTO(
    beneficiary: $beneficiary,
    payer: $payer,
    ourNumber: str_pad((string)random_int(1, 99999999), 8, '0', STR_PAD_LEFT),
    yourNumber: 'DOC-' . date('Ymd') . '-001',
    amount: 150.00,
    issueDate: new DateTimeImmutable(),
    dueDate: new DateTimeImmutable('+30 days'),
    processStep: ProcessStep::REGISTRATION
);

// Gerar boleto
try {
    $response = $boletoService->createBoleto($request);
    
    echo "✅ Boleto gerado com sucesso!\n";
    echo "ID: {$response->id}\n";
    echo "Nosso Número: {$response->ourNumber}\n";
    echo "Valor: R$ " . number_format(150.00, 2, ',', '.') . "\n";
    echo "Vencimento: " . (new DateTimeImmutable('+30 days'))->format('d/m/Y') . "\n";
    echo "PIX Copia e Cola: {$response->pixCopyPaste}\n";
    
} catch (Exception $e) {
    echo "❌ Erro: {$e->getMessage()}\n";
}
```

### 2. Boleto para Pessoa Jurídica

```php
<?php

use ItauBoletoPix\Models\LegalPerson;

// Endereço da empresa
$companyAddress = new Address(
    street: 'Av. Paulista, 1000 - Sala 1001',
    neighborhood: 'Bela Vista',
    city: 'São Paulo',
    state: 'SP',
    zipCode: '01310-100'
);

// Pessoa jurídica
$company = new LegalPerson(
    name: 'Empresa XYZ Tecnologia LTDA',
    document: '12.345.678/0001-99',
    address: $companyAddress
);

$payer = new Payer($company);

// Request (mesmo processo do exemplo anterior)
$request = new BoletoRequestDTO(
    beneficiary: $beneficiary,
    payer: $payer,
    ourNumber: str_pad((string)random_int(1, 99999999), 8, '0', STR_PAD_LEFT),
    yourNumber: 'CNPJ-' . date('Ymd') . '-001',
    amount: 2500.00,
    issueDate: new DateTimeImmutable(),
    dueDate: new DateTimeImmutable('+15 days'),
    processStep: ProcessStep::REGISTRATION
);

try {
    $response = $boletoService->createBoleto($request);
    echo "✅ Boleto empresarial gerado: {$response->id}\n";
} catch (Exception $e) {
    echo "❌ Erro: {$e->getMessage()}\n";
}
```

### 3. Boleto Apenas para Simulação

```php
<?php

// Para testar sem registrar efetivamente
$request = new BoletoRequestDTO(
    beneficiary: $beneficiary,
    payer: $payer,
    ourNumber: '99999999', // Número de teste
    yourNumber: 'TESTE-001',
    amount: 10.00,
    issueDate: new DateTimeImmutable(),
    dueDate: new DateTimeImmutable('+7 days'),
    processStep: ProcessStep::SIMULATION // Apenas simula
);

try {
    $response = $boletoService->createBoleto($request);
    echo "✅ Simulação realizada com sucesso!\n";
    echo "Dados retornados (não registrado): {$response->id}\n";
} catch (Exception $e) {
    echo "❌ Erro na simulação: {$e->getMessage()}\n";
}
```

## 🔧 Exemplos Avançados

### 1. Boleto com Juros, Multa e Desconto

```php
<?php

use ItauBoletoPix\Models\{Charge, Interest, Fine, Discount};

// Configurar juros de R$ 2,00 por dia
$interest = new Interest(
    type: '93', // Valor por dia
    amountPerDay: 2.00
);

// Configurar multa de 2%
$fine = new Fine(
    type: '02', // Percentual
    percentage: 2.0
);

// Configurar desconto de 10% até 10 dias antes do vencimento
$discount = new Discount(
    type: '02', // Percentual até data
    dueDate: new DateTimeImmutable('+20 days'), // 10 dias antes do vencimento
    amount: 50.00, // Valor fixo do desconto
    percentage: 10.0 // 10%
);

// Mensagens no boleto
$messages = [
    'Não receber após o vencimento',
    'Desconto de 10% até ' . (new DateTimeImmutable('+20 days'))->format('d/m/Y'),
    'Juros de R$ 2,00 por dia de atraso',
    'Multa de 2% após vencimento'
];

// Criar configuração de cobrança
$charge = new Charge(
    interest: $interest,
    fine: $fine,
    discount: $discount,
    messages: $messages
);

// Request com cobrança
$request = new BoletoRequestDTO(
    beneficiary: $beneficiary,
    payer: $payer,
    ourNumber: str_pad((string)random_int(1, 99999999), 8, '0', STR_PAD_LEFT),
    yourNumber: 'ADV-' . date('Ymd') . '-001',
    amount: 500.00,
    issueDate: new DateTimeImmutable(),
    dueDate: new DateTimeImmutable('+30 days'),
    charge: $charge,
    processStep: ProcessStep::REGISTRATION
);

try {
    $response = $boletoService->createBoleto($request);
    
    echo "✅ Boleto avançado gerado!\n";
    echo "ID: {$response->id}\n";
    echo "Valor original: R$ 500,00\n";
    echo "Desconto até " . (new DateTimeImmutable('+20 days'))->format('d/m/Y') . ": R$ 50,00 (10%)\n";
    echo "Juros após vencimento: R$ 2,00/dia\n";
    echo "Multa após vencimento: 2%\n";
    
} catch (Exception $e) {
    echo "❌ Erro: {$e->getMessage()}\n";
}
```

### 2. Geração em Lote

```php
<?php

/**
 * Gerar múltiplos boletos de uma vez
 */
class BoletoLoteGenerator
{
    private BoletoGenerationService $boletoService;
    private Beneficiary $beneficiary;
    
    public function __construct(BoletoGenerationService $boletoService, Beneficiary $beneficiary)
    {
        $this->boletoService = $boletoService;
        $this->beneficiary = $beneficiary;
    }
    
    public function generateBatch(array $customers): array
    {
        $results = [];
        $ourNumberCounter = $this->getLastOurNumber() + 1;
        
        foreach ($customers as $customer) {
            try {
                // Criar endereço
                $address = new Address(
                    street: $customer['street'],
                    neighborhood: $customer['neighborhood'],
                    city: $customer['city'],
                    state: $customer['state'],
                    zipCode: $customer['zipcode']
                );
                
                // Criar pessoa (física ou jurídica)
                if (strlen($customer['document']) === 11) {
                    $person = new PhysicalPerson(
                        name: $customer['name'],
                        document: $customer['document'],
                        address: $address
                    );
                } else {
                    $person = new LegalPerson(
                        name: $customer['name'],
                        document: $customer['document'],
                        address: $address
                    );
                }
                
                $payer = new Payer($person);
                
                // Criar request
                $request = new BoletoRequestDTO(
                    beneficiary: $this->beneficiary,
                    payer: $payer,
                    ourNumber: str_pad((string)$ourNumberCounter, 8, '0', STR_PAD_LEFT),
                    yourNumber: $customer['reference'],
                    amount: $customer['amount'],
                    issueDate: new DateTimeImmutable(),
                    dueDate: new DateTimeImmutable($customer['due_date']),
                    processStep: ProcessStep::REGISTRATION
                );
                
                // Gerar boleto
                $response = $this->boletoService->createBoleto($request);
                
                $results[] = [
                    'success' => true,
                    'customer' => $customer['name'],
                    'our_number' => $response->ourNumber,
                    'id' => $response->id,
                    'pix_copy_paste' => $response->pixCopyPaste
                ];
                
                $ourNumberCounter++;
                
                // Delay para não sobrecarregar a API
                usleep(500000); // 0.5 segundos
                
            } catch (Exception $e) {
                $results[] = [
                    'success' => false,
                    'customer' => $customer['name'],
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    private function getLastOurNumber(): int
    {
        // Implementar busca do último nosso número usado
        // Pode ser do banco de dados, arquivo, etc.
        return 1000; // Exemplo
    }
}

// Dados dos clientes
$customers = [
    [
        'name' => 'João da Silva',
        'document' => '12345678900',
        'reference' => 'MENSALIDADE-JAN-2025',
        'amount' => 150.00,
        'due_date' => '+30 days',
        'street' => 'Rua A, 123',
        'neighborhood' => 'Centro',
        'city' => 'São Paulo',
        'state' => 'SP',
        'zipcode' => '01234-567'
    ],
    [
        'name' => 'Maria Santos',
        'document' => '98765432100',
        'reference' => 'MENSALIDADE-JAN-2025',
        'amount' => 150.00,
        'due_date' => '+30 days',
        'street' => 'Rua B, 456',
        'neighborhood' => 'Vila Nova',
        'city' => 'São Paulo',
        'state' => 'SP',
        'zipcode' => '01234-568'
    ]
];

// Gerar lote
$generator = new BoletoLoteGenerator($boletoService, $beneficiary);
$results = $generator->generateBatch($customers);

// Exibir resultados
foreach ($results as $result) {
    if ($result['success']) {
        echo "✅ {$result['customer']}: {$result['our_number']}\n";
    } else {
        echo "❌ {$result['customer']}: {$result['error']}\n";
    }
}
```

### 3. Sistema de Cobrança Recorrente

```php
<?php

/**
 * Sistema de cobrança mensal automática
 */
class RecurringBillingSystem
{
    private BoletoGenerationService $boletoService;
    private Beneficiary $beneficiary;
    private PDO $pdo;
    
    public function __construct(BoletoGenerationService $boletoService, Beneficiary $beneficiary, PDO $pdo)
    {
        $this->boletoService = $boletoService;
        $this->beneficiary = $beneficiary;
        $this->pdo = $pdo;
    }
    
    public function generateMonthlyBoletos(): array
    {
        $results = ['success' => [], 'failed' => []];
        
        // Buscar assinaturas ativas
        $subscriptions = $this->getActiveSubscriptions();
        
        foreach ($subscriptions as $subscription) {
            try {
                // Verificar se já foi gerado este mês
                if ($this->wasGeneratedThisMonth($subscription['id'])) {
                    continue;
                }
                
                // Criar endereço
                $address = new Address(
                    street: $subscription['street'],
                    neighborhood: $subscription['neighborhood'],
                    city: $subscription['city'],
                    state: $subscription['state'],
                    zipCode: $subscription['zipcode']
                );
                
                // Criar pessoa
                $person = new PhysicalPerson(
                    name: $subscription['customer_name'],
                    document: $subscription['customer_document'],
                    address: $address
                );
                
                $payer = new Payer($person);
                
                // Configurar desconto para pagamento antecipado
                $discount = new Discount(
                    type: '02',
                    dueDate: new DateTimeImmutable('+5 days'),
                    amount: $subscription['amount'] * 0.05, // 5% de desconto
                    percentage: 5.0
                );
                
                // Configurar juros e multa
                $interest = new Interest('93', 2.00);
                $fine = new Fine('02', 2.0);
                
                $charge = new Charge(
                    interest: $interest,
                    fine: $fine,
                    discount: $discount,
                    messages: [
                        'Mensalidade ' . date('m/Y'),
                        'Desconto de 5% até ' . (new DateTimeImmutable('+5 days'))->format('d/m/Y'),
                        'Não receber após 30 dias do vencimento'
                    ]
                );
                
                // Gerar nosso número único
                $ourNumber = $this->generateOurNumber($subscription['id']);
                
                // Request
                $request = new BoletoRequestDTO(
                    beneficiary: $this->beneficiary,
                    payer: $payer,
                    ourNumber: $ourNumber,
                    yourNumber: "MENS-{$subscription['id']}-" . date('Ym'),
                    amount: $subscription['amount'],
                    issueDate: new DateTimeImmutable(),
                    dueDate: new DateTimeImmutable('+10 days'),
                    charge: $charge,
                    processStep: ProcessStep::REGISTRATION
                );
                
                // Gerar boleto
                $response = $this->boletoService->createBoleto($request);
                
                // Salvar no banco
                $this->saveBoleto($subscription['id'], $response);
                
                // Enviar por email
                $this->sendBoletoEmail($subscription, $response);
                
                $results['success'][] = [
                    'subscription_id' => $subscription['id'],
                    'customer_name' => $subscription['customer_name'],
                    'our_number' => $response->ourNumber,
                    'amount' => $subscription['amount']
                ];
                
            } catch (Exception $e) {
                $results['failed'][] = [
                    'subscription_id' => $subscription['id'],
                    'customer_name' => $subscription['customer_name'],
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    private function getActiveSubscriptions(): array
    {
        $stmt = $this->pdo->query("
            SELECT s.*, c.name as customer_name, c.document as customer_document,
                   c.street, c.neighborhood, c.city, c.state, c.zipcode
            FROM subscriptions s
            JOIN customers c ON s.customer_id = c.id
            WHERE s.status = 'active'
            AND s.next_billing_date <= CURDATE()
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function wasGeneratedThisMonth(int $subscriptionId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM boletos 
            WHERE subscription_id = ? 
            AND YEAR(created_at) = YEAR(CURDATE())
            AND MONTH(created_at) = MONTH(CURDATE())
        ");
        
        $stmt->execute([$subscriptionId]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function generateOurNumber(int $subscriptionId): string
    {
        // Gerar baseado no ID da assinatura + timestamp
        $base = str_pad((string)$subscriptionId, 4, '0', STR_PAD_LEFT);
        $suffix = substr(time(), -4);
        return $base . $suffix;
    }
    
    private function saveBoleto(int $subscriptionId, BoletoResponseDTO $response): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO boletos (
                subscription_id, our_number, boleto_id, 
                barcode, digitable_line, pix_copy_paste, 
                amount, due_date, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $subscriptionId,
            $response->ourNumber,
            $response->id,
            $response->barcode,
            $response->digitableLine,
            $response->pixCopyPaste,
            $response->amount,
            $response->dueDate
        ]);
    }
    
    private function sendBoletoEmail(array $subscription, BoletoResponseDTO $response): void
    {
        // Implementar envio de email com o boleto
        // Pode usar PHPMailer, SwiftMailer, etc.
        
        $to = $subscription['customer_email'];
        $subject = "Mensalidade " . date('m/Y') . " - Boleto Disponível";
        
        $message = "
        Olá {$subscription['customer_name']},
        
        Sua mensalidade de " . date('m/Y') . " está disponível.
        
        Valor: R$ " . number_format($subscription['amount'], 2, ',', '.') . "
        Vencimento: " . (new DateTimeImmutable('+10 days'))->format('d/m/Y') . "
        
        PIX Copia e Cola:
        {$response->pixCopyPaste}
        
        Linha Digitável:
        {$response->digitableLine}
        
        Desconto de 5% para pagamento até " . (new DateTimeImmutable('+5 days'))->format('d/m/Y') . "
        ";
        
        mail($to, $subject, $message);
    }
}

// Uso do sistema (executar via cron no dia 01 de cada mês)
if (date('d') === '01') {
    $recurringSystem = new RecurringBillingSystem($boletoService, $beneficiary, $pdo);
    $results = $recurringSystem->generateMonthlyBoletos();
    
    echo "📊 Relatório de Geração Mensal:\n";
    echo "✅ Sucessos: " . count($results['success']) . "\n";
    echo "❌ Falhas: " . count($results['failed']) . "\n";
    
    if (!empty($results['failed'])) {
        echo "\nFalhas:\n";
        foreach ($results['failed'] as $failed) {
            echo "- {$failed['customer_name']}: {$failed['error']}\n";
        }
    }
}
```

## 🏢 Casos de Uso Reais

### 1. E-commerce

```php
<?php

/**
 * Integração com sistema de e-commerce
 */
class EcommerceBoletoIntegration
{
    public function generateBoletoForOrder(array $order): BoletoResponseDTO
    {
        // Buscar dados do cliente
        $customer = $this->getCustomer($order['customer_id']);
        
        // Criar endereço de cobrança
        $billingAddress = new Address(
            street: $customer['billing_street'],
            neighborhood: $customer['billing_neighborhood'],
            city: $customer['billing_city'],
            state: $customer['billing_state'],
            zipCode: $customer['billing_zipcode']
        );
        
        // Criar pessoa
        $person = new PhysicalPerson(
            name: $customer['name'],
            document: $customer['document'],
            address: $billingAddress
        );
        
        $payer = new Payer($person);
        
        // Configurar desconto para pagamento à vista
        $discount = null;
        if ($order['payment_method'] === 'boleto_discount') {
            $discount = new Discount(
                type: '02',
                dueDate: new DateTimeImmutable('+1 day'),
                amount: $order['total'] * 0.03, // 3% de desconto
                percentage: 3.0
            );
        }
        
        $charge = $discount ? new Charge(discount: $discount) : null;
        
        // Request
        $request = new BoletoRequestDTO(
            beneficiary: $this->beneficiary,
            payer: $payer,
            ourNumber: str_pad((string)$order['id'], 8, '0', STR_PAD_LEFT),
            yourNumber: "PEDIDO-{$order['id']}",
            amount: $order['total'],
            issueDate: new DateTimeImmutable(),
            dueDate: new DateTimeImmutable('+3 days'), // Prazo curto para e-commerce
            charge: $charge,
            processStep: ProcessStep::REGISTRATION
        );
        
        // Gerar boleto
        $response = $this->boletoService->createBoleto($request);
        
        // Atualizar pedido
        $this->updateOrderWithBoleto($order['id'], $response);
        
        return $response;
    }
    
    private function updateOrderWithBoleto(int $orderId, BoletoResponseDTO $response): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE orders SET 
                boleto_our_number = ?,
                boleto_id = ?,
                boleto_barcode = ?,
                boleto_digitable_line = ?,
                boleto_pix_copy_paste = ?,
                payment_status = 'waiting_payment',
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $response->ourNumber,
            $response->id,
            $response->barcode,
            $response->digitableLine,
            $response->pixCopyPaste,
            $orderId
        ]);
    }
}
```

### 2. Sistema de Assinatura/SaaS

```php
<?php

/**
 * Sistema de assinatura mensal
 */
class SaasSubscriptionBilling
{
    public function generateSubscriptionBoleto(int $subscriptionId): BoletoResponseDTO
    {
        $subscription = $this->getSubscription($subscriptionId);
        $customer = $this->getCustomer($subscription['customer_id']);
        $plan = $this->getPlan($subscription['plan_id']);
        
        // Criar endereço
        $address = new Address(
            street: $customer['street'],
            neighborhood: $customer['neighborhood'],
            city: $customer['city'],
            state: $customer['state'],
            zipCode: $customer['zipcode']
        );
        
        // Pessoa física ou jurídica
        if (strlen($customer['document']) === 11) {
            $person = new PhysicalPerson(
                name: $customer['name'],
                document: $customer['document'],
                address: $address
            );
        } else {
            $person = new LegalPerson(
                name: $customer['name'],
                document: $customer['document'],
                address: $address
            );
        }
        
        $payer = new Payer($person);
        
        // Configurar cobrança com desconto anual
        $discount = null;
        if ($subscription['billing_cycle'] === 'yearly') {
            // 20% de desconto para pagamento anual
            $discount = new Discount(
                type: '02',
                dueDate: new DateTimeImmutable('+5 days'),
                amount: $plan['monthly_price'] * 12 * 0.20,
                percentage: 20.0
            );
        }
        
        // Juros e multa para atraso
        $interest = new Interest('93', 1.00); // R$ 1,00 por dia
        $fine = new Fine('02', 2.0); // 2% de multa
        
        $charge = new Charge(
            interest: $interest,
            fine: $fine,
            discount: $discount,
            messages: [
                "Assinatura {$plan['name']} - " . date('m/Y'),
                $subscription['billing_cycle'] === 'yearly' ? 'Desconto de 20% para pagamento anual' : '',
                'Acesso suspenso após 5 dias do vencimento'
            ]
        );
        
        // Calcular valor
        $amount = $subscription['billing_cycle'] === 'yearly' 
            ? $plan['monthly_price'] * 12 
            : $plan['monthly_price'];
        
        // Request
        $request = new BoletoRequestDTO(
            beneficiary: $this->beneficiary,
            payer: $payer,
            ourNumber: $this->generateSubscriptionOurNumber($subscriptionId),
            yourNumber: "SUB-{$subscriptionId}-" . date('Ym'),
            amount: $amount,
            issueDate: new DateTimeImmutable(),
            dueDate: new DateTimeImmutable('+10 days'),
            charge: $charge,
            processStep: ProcessStep::REGISTRATION
        );
        
        $response = $this->boletoService->createBoleto($request);
        
        // Salvar boleto da assinatura
        $this->saveSubscriptionBoleto($subscriptionId, $response);
        
        // Agendar suspensão automática
        $this->scheduleSubscriptionSuspension($subscriptionId, '+15 days');
        
        return $response;
    }
    
    private function generateSubscriptionOurNumber(int $subscriptionId): string
    {
        // Prefixo 99 para assinaturas + ID da assinatura (6 dígitos)
        return '99' . str_pad((string)$subscriptionId, 6, '0', STR_PAD_LEFT);
    }
}
```

### 3. Sistema Educacional

```php
<?php

/**
 * Sistema de mensalidades escolares
 */
class SchoolBillingSystem
{
    public function generateMonthlyTuition(int $studentId, int $month, int $year): BoletoResponseDTO
    {
        $student = $this->getStudent($studentId);
        $course = $this->getCourse($student['course_id']);
        
        // Endereço do responsável
        $address = new Address(
            street: $student['guardian_street'],
            neighborhood: $student['guardian_neighborhood'],
            city: $student['guardian_city'],
            state: $student['guardian_state'],
            zipCode: $student['guardian_zipcode']
        );
        
        // Responsável financeiro
        $guardian = new PhysicalPerson(
            name: $student['guardian_name'],
            document: $student['guardian_document'],
            address: $address
        );
        
        $payer = new Payer($guardian);
        
        // Configurar desconto para pagamento antecipado
        $discount = new Discount(
            type: '02',
            dueDate: new DateTimeImmutable("first day of {$year}-{$month}"), // Até dia 1º
            amount: $course['monthly_fee'] * 0.10, // 10% de desconto
            percentage: 10.0
        );
        
        // Juros e multa
        $interest = new Interest('93', 3.00); // R$ 3,00 por dia
        $fine = new Fine('02', 2.0); // 2% de multa
        
        $charge = new Charge(
            interest: $interest,
            fine: $fine,
            discount: $discount,
            messages: [
                "Mensalidade {$course['name']} - " . sprintf('%02d/%d', $month, $year),
                "Aluno: {$student['name']}",
                "Desconto de 10% para pagamento até dia 1º",
                "Após 30 dias de atraso, matrícula será cancelada"
            ]
        );
        
        // Request
        $request = new BoletoRequestDTO(
            beneficiary: $this->beneficiary,
            payer: $payer,
            ourNumber: $this->generateTuitionOurNumber($studentId, $month, $year),
            yourNumber: "MENS-{$studentId}-{$year}{$month}",
            amount: $course['monthly_fee'],
            issueDate: new DateTimeImmutable(),
            dueDate: new DateTimeImmutable("last day of {$year}-{$month}"), // Último dia do mês
            charge: $charge,
            processStep: ProcessStep::REGISTRATION
        );
        
        $response = $this->boletoService->createBoleto($request);
        
        // Salvar mensalidade
        $this->saveTuition($studentId, $month, $year, $response);
        
        return $response;
    }
    
    private function generateTuitionOurNumber(int $studentId, int $month, int $year): string
    {
        // Formato: AAMMSSSS (AA=ano, MM=mês, SSSS=ID do aluno)
        $yearSuffix = substr((string)$year, -2);
        $monthPadded = str_pad((string)$month, 2, '0', STR_PAD_LEFT);
        $studentPadded = str_pad((string)$studentId, 4, '0', STR_PAD_LEFT);
        
        return $yearSuffix . $monthPadded . $studentPadded;
    }
}
```

## 🔌 Integração com Frameworks

### Laravel

```php
<?php

// app/Services/ItauBoletoService.php
namespace App\Services;

use ItauBoletoPix\Gateways\ItauBoletoGateway;
use ItauBoletoPix\Services\BoletoGenerationService;

class ItauBoletoService
{
    private BoletoGenerationService $boletoService;
    
    public function __construct()
    {
        $gateway = new ItauBoletoGateway(
            clientId: config('itau.client_id'),
            clientSecret: config('itau.client_secret'),
            certificatePath: config('itau.certificate_path'),
            certificateKeyPath: config('itau.certificate_key_path'),
            sandbox: config('itau.sandbox')
        );
        
        $this->boletoService = new BoletoGenerationService($gateway);
    }
    
    public function createBoleto(BoletoRequestDTO $request): BoletoResponseDTO
    {
        return $this->boletoService->createBoleto($request);
    }
}

// app/Http/Controllers/BoletoController.php
namespace App\Http\Controllers;

use App\Services\ItauBoletoService;
use Illuminate\Http\Request;

class BoletoController extends Controller
{
    public function __construct(
        private ItauBoletoService $boletoService
    ) {}
    
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required|date|after:today'
        ]);
        
        try {
            $customer = Customer::find($validated['customer_id']);
            
            // Criar request do boleto
            $boletoRequest = $this->buildBoletoRequest($customer, $validated);
            
            // Gerar boleto
            $response = $this->boletoService->createBoleto($boletoRequest);
            
            return response()->json([
                'success' => true,
                'data' => $response->toArray()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

// config/itau.php
return [
    'client_id' => env('ITAU_CLIENT_ID'),
    'client_secret' => env('ITAU_CLIENT_SECRET'),
    'certificate_path' => env('ITAU_CERTIFICATE_PATH'),
    'certificate_key_path' => env('ITAU_CERTIFICATE_KEY_PATH'),
    'sandbox' => env('ITAU_SANDBOX', true),
    'beneficiary' => [
        'agency' => env('ITAU_BENEFICIARY_AGENCY'),
        'account' => env('ITAU_BENEFICIARY_ACCOUNT'),
        'account_digit' => env('ITAU_BENEFICIARY_ACCOUNT_DIGIT'),
        'pix_key' => env('ITAU_PIX_KEY'),
    ]
];
```

### Symfony

```php
<?php

// src/Service/ItauBoletoService.php
namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ItauBoletoService
{
    private BoletoGenerationService $boletoService;
    
    public function __construct(ParameterBagInterface $params)
    {
        $gateway = new ItauBoletoGateway(
            clientId: $params->get('itau.client_id'),
            clientSecret: $params->get('itau.client_secret'),
            certificatePath: $params->get('itau.certificate_path'),
            certificateKeyPath: $params->get('itau.certificate_key_path'),
            sandbox: $params->get('itau.sandbox')
        );
        
        $this->boletoService = new BoletoGenerationService($gateway);
    }
}

// config/services.yaml
parameters:
    itau.client_id: '%env(ITAU_CLIENT_ID)%'
    itau.client_secret: '%env(ITAU_CLIENT_SECRET)%'
    itau.certificate_path: '%env(ITAU_CERTIFICATE_PATH)%'
    itau.certificate_key_path: '%env(ITAU_CERTIFICATE_KEY_PATH)%'
    itau.sandbox: '%env(bool:ITAU_SANDBOX)%'

services:
    App\Service\ItauBoletoService:
        arguments:
            $params: '@parameter_bag'
```

## 🧪 Testes e Debug

### Teste de Conectividade

```php
<?php

// test-connection.php
require_once 'vendor/autoload.php';

use ItauBoletoPix\Gateways\ItauBoletoGateway;

$gateway = new ItauBoletoGateway(
    clientId: $_ENV['ITAU_CLIENT_ID'],
    clientSecret: $_ENV['ITAU_CLIENT_SECRET'],
    certificatePath: $_ENV['ITAU_CERTIFICATE_PATH'],
    certificateKeyPath: $_ENV['ITAU_CERTIFICATE_KEY_PATH'],
    sandbox: true
);

echo "🔄 Testando conectividade...\n";

try {
    if ($gateway->testConnection()) {
        echo "✅ Conexão estabelecida com sucesso!\n";
        
        // Testar autenticação
        $token = $gateway->authenticate();
        echo "✅ Token obtido: " . substr($token, 0, 20) . "...\n";
        
    } else {
        echo "❌ Falha na conexão\n";
    }
} catch (Exception $e) {
    echo "❌ Erro: {$e->getMessage()}\n";
}
```

### Debug de Requisições

```php
<?php

// debug-requests.php
class DebugItauGateway extends ItauBoletoGateway
{
    public function sendBoletoRequest(array $payload): array
    {
        echo "📤 Enviando payload:\n";
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        
        $response = parent::sendBoletoRequest($payload);
        
        echo "📥 Resposta recebida:\n";
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        
        return $response;
    }
}

// Usar o gateway de debug
$debugGateway = new DebugItauGateway(
    clientId: $_ENV['ITAU_CLIENT_ID'],
    clientSecret: $_ENV['ITAU_CLIENT_SECRET'],
    certificatePath: $_ENV['ITAU_CERTIFICATE_PATH'],
    certificateKeyPath: $_ENV['ITAU_CERTIFICATE_KEY_PATH'],
    sandbox: true
);

$boletoService = new BoletoGenerationService($debugGateway);
```

### Validação de Dados

```php
<?php

// validate-data.php
use ItauBoletoPix\Utils\DocumentValidator;

// Testar validação de documentos
$cpfs = ['123.456.789-00', '000.000.000-00', '111.111.111-11'];
$cnpjs = ['12.345.678/0001-99', '00.000.000/0000-00'];

echo "🔍 Testando validação de CPFs:\n";
foreach ($cpfs as $cpf) {
    $valid = DocumentValidator::isValidCPF($cpf);
    echo ($valid ? "✅" : "❌") . " {$cpf}\n";
}

echo "\n🔍 Testando validação de CNPJs:\n";
foreach ($cnpjs as $cnpj) {
    $valid = DocumentValidator::isValidCNPJ($cnpj);
    echo ($valid ? "✅" : "❌") . " {$cnpj}\n";
}
```

### Simulação de Webhooks

```php
<?php

// simulate-webhook.php
use ItauBoletoPix\Webhooks\ItauWebhookHandler;

$webhookHandler = new ItauWebhookHandler();

// Simular diferentes tipos de webhook
$webhooks = [
    // Pagamento
    [
        'tipo_notificacao' => 'BAIXA_EFETIVA',
        'nosso_numero' => '00000001',
        'data_pagamento' => date('Y-m-d'),
        'valor_pago' => 15000,
        'forma_pagamento' => 'PIX'
    ],
    // Cancelamento
    [
        'tipo_notificacao' => 'BAIXA_OPERACIONAL',
        'nosso_numero' => '00000002',
        'data_baixa' => date('Y-m-d'),
        'motivo_baixa' => 'CANCELAMENTO_SOLICITADO'
    ],
    // Vencimento
    [
        'tipo_notificacao' => 'VENCIMENTO',
        'nosso_numero' => '00000003',
        'data_vencimento' => date('Y-m-d'),
        'valor_original' => 15000
    ]
];

// Configurar handlers
$webhookHandler->on('paid', function($payload) {
    echo "💰 Pagamento simulado: {$payload->getOurNumber()}\n";
});

$webhookHandler->on('cancelled', function($payload) {
    echo "❌ Cancelamento simulado: {$payload->getOurNumber()}\n";
});

$webhookHandler->on('expired', function($payload) {
    echo "⏰ Vencimento simulado: {$payload->getOurNumber()}\n";
});

// Processar webhooks de teste
foreach ($webhooks as $webhook) {
    echo "🔄 Processando webhook: {$webhook['tipo_notificacao']}\n";
    try {
        $webhookHandler->handle($webhook);
        echo "✅ Processado com sucesso\n\n";
    } catch (Exception $e) {
        echo "❌ Erro: {$e->getMessage()}\n\n";
    }
}
```

Estes exemplos cobrem os principais cenários de uso da biblioteca. Adapte-os conforme suas necessidades específicas!