# Guia de Webhooks - Itaú Boleto PIX

## 📋 Índice

- [Introdução](#introdução)
- [Configuração](#configuração)
- [Eventos Disponíveis](#eventos-disponíveis)
- [Implementação](#implementação)
- [Validação de Assinatura](#validação-de-assinatura)
- [Exemplos Práticos](#exemplos-práticos)
- [Tratamento de Erros](#tratamento-de-erros)
- [Boas Práticas](#boas-práticas)

## 🎯 Introdução

Os webhooks permitem que sua aplicação receba notificações em tempo real sobre mudanças no status dos boletos PIX. Quando um evento ocorre (como pagamento ou cancelamento), o Itaú envia uma requisição HTTP POST para sua URL configurada.

### Vantagens dos Webhooks

- ✅ **Tempo Real** - Notificações instantâneas
- ✅ **Automação** - Processamento automático de eventos
- ✅ **Confiabilidade** - Sistema de retry automático
- ✅ **Segurança** - Validação de assinatura
- ✅ **Escalabilidade** - Processa múltiplos eventos simultaneamente

## ⚙️ Configuração

### 1. Configurar URL do Webhook

No arquivo `.env`:

```env
WEBHOOK_URL=https://seu-dominio.com/webhooks/itau
WEBHOOK_SECRET=seu-secret-para-validacao-de-assinatura
```

### 2. Registrar Webhook na API

```php
use ItauBoletoPix\Gateways\ItauBoletoGateway;

$gateway = new ItauBoletoGateway(
    clientId: $_ENV['ITAU_CLIENT_ID'],
    clientSecret: $_ENV['ITAU_CLIENT_SECRET'],
    certificatePath: $_ENV['ITAU_CERTIFICATE_PATH'],
    certificateKeyPath: $_ENV['ITAU_CERTIFICATE_KEY_PATH'],
    sandbox: true
);

// Configuração do webhook
$webhookConfig = [
    'url' => $_ENV['WEBHOOK_URL'],
    'eventos' => [
        'BAIXA_EFETIVA',        // Pagamento confirmado
        'BAIXA_OPERACIONAL',    // Cancelamento
        'VENCIMENTO',           // Vencimento
        'PROTESTO'              // Protesto
    ],
    'ativo' => true
];

try {
    $response = $gateway->registerWebhook($webhookConfig);
    echo "Webhook registrado com sucesso!\n";
} catch (Exception $e) {
    echo "Erro ao registrar webhook: {$e->getMessage()}\n";
}
```

### 3. Endpoint Receptor

Crie um endpoint em sua aplicação para receber os webhooks:

```php
// webhooks/itau.php
<?php

require_once '../vendor/autoload.php';

use ItauBoletoPix\Webhooks\ItauWebhookHandler;

// Configurar handler
$webhookHandler = new ItauWebhookHandler();

// Receber dados do webhook
$rawPayload = json_decode(file_get_contents('php://input'), true);
$signature = $_SERVER['HTTP_X_ITAU_SIGNATURE'] ?? '';

try {
    // Validar assinatura (opcional mas recomendado)
    if (!$webhookHandler->validateSignature($rawPayload, $signature)) {
        http_response_code(401);
        echo json_encode(['error' => 'Assinatura inválida']);
        exit;
    }
    
    // Processar webhook
    $success = $webhookHandler->handle($rawPayload);
    
    if ($success) {
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Falha no processamento']);
    }
    
} catch (Exception $e) {
    error_log("Erro no webhook: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno']);
}
```

## 📡 Eventos Disponíveis

### BAIXA_EFETIVA
Boleto foi pago com sucesso.

**Payload de exemplo:**
```json
{
    "tipo_notificacao": "BAIXA_EFETIVA",
    "nosso_numero": "00000001",
    "data_pagamento": "2025-01-15",
    "valor_pago": 15000,
    "forma_pagamento": "PIX",
    "dados_adicionais": {
        "txid": "BL55810099403109000000000000001",
        "end_to_end_id": "E12345678202501151234567890123456"
    }
}
```

### BAIXA_OPERACIONAL
Boleto foi cancelado ou baixado operacionalmente.

**Payload de exemplo:**
```json
{
    "tipo_notificacao": "BAIXA_OPERACIONAL",
    "nosso_numero": "00000001",
    "data_baixa": "2025-01-15",
    "motivo_baixa": "CANCELAMENTO_SOLICITADO"
}
```

### VENCIMENTO
Boleto venceu sem pagamento.

**Payload de exemplo:**
```json
{
    "tipo_notificacao": "VENCIMENTO",
    "nosso_numero": "00000001",
    "data_vencimento": "2025-01-15",
    "valor_original": 15000
}
```

### PROTESTO
Boleto foi protestado.

**Payload de exemplo:**
```json
{
    "tipo_notificacao": "PROTESTO",
    "nosso_numero": "00000001",
    "data_protesto": "2025-01-20",
    "cartorio": "1º Tabelionato de Protesto"
}
```

## 🔧 Implementação

### Handler Básico

```php
use ItauBoletoPix\Webhooks\ItauWebhookHandler;

$webhookHandler = new ItauWebhookHandler();

// Registrar listeners para eventos específicos
$webhookHandler->on('paid', function($payload) {
    echo "💰 Boleto pago!\n";
    echo "Nosso Número: {$payload->getOurNumber()}\n";
    echo "Valor: R$ " . number_format($payload->getPaidAmount(), 2, ',', '.') . "\n";
    echo "Data: {$payload->getPaymentDate()}\n";
    
    // Suas ações personalizadas aqui
    updatePaymentStatus($payload->getOurNumber(), 'paid');
    sendConfirmationEmail($payload->getOurNumber());
    releaseAccess($payload->getOurNumber());
});

$webhookHandler->on('cancelled', function($payload) {
    echo "❌ Boleto cancelado: {$payload->getOurNumber()}\n";
    
    // Suas ações personalizadas aqui
    updatePaymentStatus($payload->getOurNumber(), 'cancelled');
    notifyCustomer($payload->getOurNumber(), 'cancelled');
});

$webhookHandler->on('expired', function($payload) {
    echo "⏰ Boleto vencido: {$payload->getOurNumber()}\n";
    
    // Suas ações personalizadas aqui
    updatePaymentStatus($payload->getOurNumber(), 'expired');
    sendReminderEmail($payload->getOurNumber());
});

// Processar webhook recebido
$rawPayload = json_decode(file_get_contents('php://input'), true);
$webhookHandler->handle($rawPayload);
```

### Handler Avançado com Banco de Dados

```php
class CustomWebhookHandler
{
    private PDO $pdo;
    private LoggerInterface $logger;
    
    public function __construct(PDO $pdo, LoggerInterface $logger)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }
    
    public function handlePayment(WebhookPayload $payload): void
    {
        $this->logger->info('Processando pagamento', [
            'our_number' => $payload->getOurNumber(),
            'amount' => $payload->getPaidAmount()
        ]);
        
        try {
            $this->pdo->beginTransaction();
            
            // Atualizar status do boleto
            $stmt = $this->pdo->prepare("
                UPDATE boletos 
                SET status = 'paid', 
                    paid_at = ?, 
                    paid_amount = ? 
                WHERE our_number = ?
            ");
            
            $stmt->execute([
                $payload->getPaymentDate(),
                $payload->getPaidAmount(),
                $payload->getOurNumber()
            ]);
            
            // Buscar dados do cliente
            $stmt = $this->pdo->prepare("
                SELECT customer_id, customer_email, service_id 
                FROM boletos 
                WHERE our_number = ?
            ");
            
            $stmt->execute([$payload->getOurNumber()]);
            $boleto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($boleto) {
                // Ativar serviço
                $this->activateService($boleto['service_id']);
                
                // Enviar email de confirmação
                $this->sendConfirmationEmail(
                    $boleto['customer_email'],
                    $payload->getOurNumber(),
                    $payload->getPaidAmount()
                );
                
                // Registrar log de pagamento
                $this->logPayment($payload);
            }
            
            $this->pdo->commit();
            
            $this->logger->info('Pagamento processado com sucesso', [
                'our_number' => $payload->getOurNumber()
            ]);
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            
            $this->logger->error('Erro ao processar pagamento', [
                'our_number' => $payload->getOurNumber(),
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    private function activateService(int $serviceId): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE services 
            SET status = 'active', activated_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([$serviceId]);
    }
    
    private function sendConfirmationEmail(string $email, string $ourNumber, float $amount): void
    {
        // Implementar envio de email
        // Pode usar PHPMailer, SwiftMailer, etc.
        
        $subject = "Pagamento Confirmado - Boleto #{$ourNumber}";
        $message = "
            Seu pagamento de R$ " . number_format($amount, 2, ',', '.') . " 
            foi confirmado com sucesso!
            
            Número do boleto: {$ourNumber}
            Data: " . date('d/m/Y H:i:s') . "
        ";
        
        mail($email, $subject, $message);
    }
    
    private function logPayment(WebhookPayload $payload): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO payment_logs (
                our_number, 
                event_type, 
                amount, 
                payment_date, 
                raw_data, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $payload->getOurNumber(),
            $payload->getEventType(),
            $payload->getPaidAmount(),
            $payload->getPaymentDate(),
            json_encode($payload->getRawData())
        ]);
    }
}

// Uso do handler customizado
$customHandler = new CustomWebhookHandler($pdo, $logger);

$webhookHandler = new ItauWebhookHandler();
$webhookHandler->on('paid', [$customHandler, 'handlePayment']);
```

## 🔐 Validação de Assinatura

### Configurar Secret

```env
WEBHOOK_SECRET=seu-secret-muito-seguro-aqui
```

### Implementar Validação

```php
class SecureWebhookHandler extends ItauWebhookHandler
{
    private string $secret;
    
    public function __construct(string $secret)
    {
        $this->secret = $secret;
        parent::__construct();
    }
    
    public function validateSignature(array $payload, string $signature): bool
    {
        // Gerar hash esperado
        $expectedSignature = hash_hmac(
            'sha256',
            json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $this->secret
        );
        
        // Comparar de forma segura
        return hash_equals($expectedSignature, $signature);
    }
    
    public function handle(array $rawPayload): bool
    {
        // Obter assinatura do header
        $signature = $_SERVER['HTTP_X_ITAU_SIGNATURE'] ?? '';
        
        // Validar assinatura
        if (!$this->validateSignature($rawPayload, $signature)) {
            throw new WebhookException('Assinatura inválida');
        }
        
        // Processar normalmente
        return parent::handle($rawPayload);
    }
}

// Uso
$secureHandler = new SecureWebhookHandler($_ENV['WEBHOOK_SECRET']);
```

## 📝 Exemplos Práticos

### Sistema de Assinatura

```php
// Webhook para sistema de assinatura mensal
$webhookHandler->on('paid', function($payload) {
    $ourNumber = $payload->getOurNumber();
    
    // Buscar assinatura pelo nosso número
    $subscription = findSubscriptionByOurNumber($ourNumber);
    
    if ($subscription) {
        // Ativar assinatura por 30 dias
        $subscription->activate(30);
        
        // Agendar próxima cobrança
        scheduleNextBilling($subscription, '+1 month');
        
        // Notificar cliente
        sendWelcomeEmail($subscription->customer);
        
        // Log
        logSubscriptionActivation($subscription, $payload);
    }
});

$webhookHandler->on('expired', function($payload) {
    $ourNumber = $payload->getOurNumber();
    
    // Buscar assinatura
    $subscription = findSubscriptionByOurNumber($ourNumber);
    
    if ($subscription) {
        // Suspender serviços
        $subscription->suspend();
        
        // Enviar lembrete
        sendPaymentReminderEmail($subscription->customer);
        
        // Agendar cancelamento em 7 dias
        scheduleSubscriptionCancellation($subscription, '+7 days');
    }
});
```

### E-commerce

```php
// Webhook para e-commerce
$webhookHandler->on('paid', function($payload) {
    $ourNumber = $payload->getOurNumber();
    
    // Buscar pedido
    $order = findOrderByOurNumber($ourNumber);
    
    if ($order) {
        // Confirmar pagamento
        $order->confirmPayment($payload->getPaidAmount());
        
        // Processar pedido
        processOrder($order);
        
        // Enviar produtos digitais
        if ($order->hasDigitalProducts()) {
            sendDigitalProducts($order);
        }
        
        // Notificar estoque
        if ($order->hasPhysicalProducts()) {
            notifyWarehouse($order);
        }
        
        // Email de confirmação
        sendOrderConfirmationEmail($order);
    }
});

$webhookHandler->on('cancelled', function($payload) {
    $ourNumber = $payload->getOurNumber();
    
    // Buscar pedido
    $order = findOrderByOurNumber($ourNumber);
    
    if ($order) {
        // Cancelar pedido
        $order->cancel('Boleto cancelado');
        
        // Restaurar estoque
        restoreStock($order);
        
        // Notificar cliente
        sendOrderCancellationEmail($order);
    }
});
```

### Sistema de Cursos Online

```php
// Webhook para plataforma de cursos
$webhookHandler->on('paid', function($payload) {
    $ourNumber = $payload->getOurNumber();
    
    // Buscar matrícula
    $enrollment = findEnrollmentByOurNumber($ourNumber);
    
    if ($enrollment) {
        // Ativar matrícula
        $enrollment->activate();
        
        // Liberar acesso ao curso
        grantCourseAccess($enrollment->student, $enrollment->course);
        
        // Enviar credenciais de acesso
        sendCourseAccessEmail($enrollment);
        
        // Adicionar ao grupo do curso
        addToDiscordGroup($enrollment->student, $enrollment->course);
        
        // Registrar certificado
        generateCertificateTemplate($enrollment);
    }
});
```

## ⚠️ Tratamento de Erros

### Retry Automático

```php
class ResilientWebhookHandler
{
    private int $maxRetries = 3;
    private int $retryDelay = 5; // segundos
    
    public function handleWithRetry(array $rawPayload): bool
    {
        $attempts = 0;
        
        while ($attempts < $this->maxRetries) {
            try {
                return $this->handle($rawPayload);
                
            } catch (Exception $e) {
                $attempts++;
                
                $this->logger->warning("Tentativa {$attempts} falhou", [
                    'error' => $e->getMessage(),
                    'payload' => $rawPayload
                ]);
                
                if ($attempts >= $this->maxRetries) {
                    // Última tentativa falhou
                    $this->handleFailedWebhook($rawPayload, $e);
                    throw $e;
                }
                
                // Aguardar antes da próxima tentativa
                sleep($this->retryDelay * $attempts);
            }
        }
        
        return false;
    }
    
    private function handleFailedWebhook(array $payload, Exception $error): void
    {
        // Salvar para processamento manual
        $this->saveFailedWebhook($payload, $error);
        
        // Notificar administradores
        $this->notifyAdmins($payload, $error);
    }
    
    private function saveFailedWebhook(array $payload, Exception $error): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO failed_webhooks (
                payload, 
                error_message, 
                created_at
            ) VALUES (?, ?, NOW())
        ");
        
        $stmt->execute([
            json_encode($payload),
            $error->getMessage()
        ]);
    }
}
```

### Idempotência

```php
class IdempotentWebhookHandler
{
    public function handle(array $rawPayload): bool
    {
        $webhookId = $this->generateWebhookId($rawPayload);
        
        // Verificar se já foi processado
        if ($this->wasAlreadyProcessed($webhookId)) {
            $this->logger->info('Webhook já processado', [
                'webhook_id' => $webhookId
            ]);
            return true;
        }
        
        try {
            // Marcar como processando
            $this->markAsProcessing($webhookId);
            
            // Processar webhook
            $result = parent::handle($rawPayload);
            
            // Marcar como processado
            $this->markAsProcessed($webhookId);
            
            return $result;
            
        } catch (Exception $e) {
            // Marcar como falha
            $this->markAsFailed($webhookId, $e->getMessage());
            throw $e;
        }
    }
    
    private function generateWebhookId(array $payload): string
    {
        return md5(json_encode($payload));
    }
    
    private function wasAlreadyProcessed(string $webhookId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT status FROM webhook_processing 
            WHERE webhook_id = ? AND status = 'processed'
        ");
        
        $stmt->execute([$webhookId]);
        return $stmt->rowCount() > 0;
    }
}
```

## 🎯 Boas Práticas

### 1. Resposta Rápida
```php
// ✅ Bom: Resposta rápida
public function handle(array $payload): bool
{
    // Validar rapidamente
    if (!$this->isValidPayload($payload)) {
        return false;
    }
    
    // Processar em background
    $this->queueForProcessing($payload);
    
    // Responder imediatamente
    return true;
}

// ❌ Ruim: Processamento longo
public function handle(array $payload): bool
{
    // Não faça isso - pode causar timeout
    $this->sendEmailToAllCustomers();
    $this->generateComplexReport();
    $this->syncWithExternalAPI();
    
    return true;
}
```

### 2. Logs Detalhados
```php
public function handle(array $payload): bool
{
    $this->logger->info('Webhook recebido', [
        'type' => $payload['tipo_notificacao'] ?? 'unknown',
        'our_number' => $payload['nosso_numero'] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    try {
        $result = $this->processWebhook($payload);
        
        $this->logger->info('Webhook processado com sucesso', [
            'our_number' => $payload['nosso_numero']
        ]);
        
        return $result;
        
    } catch (Exception $e) {
        $this->logger->error('Erro ao processar webhook', [
            'our_number' => $payload['nosso_numero'] ?? 'unknown',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        throw $e;
    }
}
```

### 3. Validação Robusta
```php
private function validatePayload(array $payload): bool
{
    // Campos obrigatórios
    $required = ['tipo_notificacao', 'nosso_numero'];
    
    foreach ($required as $field) {
        if (!isset($payload[$field]) || empty($payload[$field])) {
            $this->logger->warning('Campo obrigatório ausente', [
                'field' => $field,
                'payload' => $payload
            ]);
            return false;
        }
    }
    
    // Validar nosso número
    if (!preg_match('/^\d{8}$/', $payload['nosso_numero'])) {
        $this->logger->warning('Nosso número inválido', [
            'our_number' => $payload['nosso_numero']
        ]);
        return false;
    }
    
    // Validar tipo de notificação
    $validTypes = ['BAIXA_EFETIVA', 'BAIXA_OPERACIONAL', 'VENCIMENTO', 'PROTESTO'];
    if (!in_array($payload['tipo_notificacao'], $validTypes)) {
        $this->logger->warning('Tipo de notificação inválido', [
            'type' => $payload['tipo_notificacao']
        ]);
        return false;
    }
    
    return true;
}
```

### 4. Monitoramento
```php
// Métricas de webhook
class WebhookMetrics
{
    public function recordWebhookReceived(string $type): void
    {
        // Incrementar contador
        $this->incrementCounter("webhook.received.{$type}");
    }
    
    public function recordWebhookProcessed(string $type, float $duration): void
    {
        $this->incrementCounter("webhook.processed.{$type}");
        $this->recordTiming("webhook.duration.{$type}", $duration);
    }
    
    public function recordWebhookFailed(string $type, string $error): void
    {
        $this->incrementCounter("webhook.failed.{$type}");
        $this->incrementCounter("webhook.error.{$error}");
    }
}
```

### 5. Configuração de Ambiente
```php
// Diferentes handlers por ambiente
class WebhookHandlerFactory
{
    public static function create(string $environment): WebhookHandlerInterface
    {
        switch ($environment) {
            case 'production':
                return new ProductionWebhookHandler();
                
            case 'staging':
                return new StagingWebhookHandler();
                
            case 'development':
                return new DevelopmentWebhookHandler();
                
            default:
                throw new InvalidArgumentException("Ambiente inválido: {$environment}");
        }
    }
}

// Uso
$handler = WebhookHandlerFactory::create($_ENV['APP_ENV']);
```

## 🔍 Debug e Testes

### Simular Webhooks Localmente

```php
// test-webhook.php
<?php

require_once 'vendor/autoload.php';

// Simular payload de pagamento
$testPayload = [
    'tipo_notificacao' => 'BAIXA_EFETIVA',
    'nosso_numero' => '00000001',
    'data_pagamento' => date('Y-m-d'),
    'valor_pago' => 15000,
    'forma_pagamento' => 'PIX',
    'dados_adicionais' => [
        'txid' => 'TEST123456789',
        'end_to_end_id' => 'E12345678' . date('YmdHis') . '123456'
    ]
];

// Processar
$webhookHandler = new ItauWebhookHandler();

$webhookHandler->on('paid', function($payload) {
    echo "✅ Teste de pagamento processado!\n";
    echo "Nosso Número: {$payload->getOurNumber()}\n";
    echo "Valor: R$ {$payload->getPaidAmount()}\n";
});

try {
    $success = $webhookHandler->handle($testPayload);
    echo $success ? "Sucesso!\n" : "Falha!\n";
} catch (Exception $e) {
    echo "Erro: {$e->getMessage()}\n";
}
```

### Ferramenta de Debug

```bash
# Usar ngrok para expor localhost
ngrok http 8000

# Sua URL será algo como: https://abc123.ngrok.io
# Configure no .env: WEBHOOK_URL=https://abc123.ngrok.io/webhooks/itau
```

Os webhooks são uma ferramenta poderosa para automatizar seu sistema. Implemente-os com cuidado, sempre validando dados e tratando erros adequadamente.