# Guia de Solução de Problemas - Itaú Boleto PIX

## 📋 Índice

- [Problemas de Instalação](#problemas-de-instalação)
- [Problemas de Autenticação](#problemas-de-autenticação)
- [Problemas de Certificados](#problemas-de-certificados)
- [Problemas de API](#problemas-de-api)
- [Problemas de Webhooks](#problemas-de-webhooks)
- [Problemas de Validação](#problemas-de-validação)
- [Logs e Debug](#logs-e-debug)
- [FAQ](#faq)

## 🔧 Problemas de Instalação

### Erro: "Package not found"

**Problema:**
```bash
composer require zukpay/itau-boleto-pix
Package zukpay/itau-boleto-pix not found
```

**Soluções:**
1. Verificar se o repositório está configurado corretamente
2. Instalar manualmente via Git:
```bash
git clone https://github.com/zukpay/itau-boleto-pix.git
cd itau-boleto-pix
composer install
```

### Erro: "PHP version requirement"

**Problema:**
```
Your PHP version (8.2.x) does not satisfy requirement ^8.3
```

**Solução:**
Atualize o PHP para versão 8.3 ou superior:
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php8.3

# CentOS/RHEL
sudo yum install php83

# Windows
# Baixe do site oficial: https://windows.php.net/download/
```

### Erro: "Extension not found"

**Problema:**
```
Extension curl is missing from your system
Extension json is missing from your system
```

**Soluções:**

**Ubuntu/Debian:**
```bash
sudo apt install php8.3-curl php8.3-json
sudo systemctl restart apache2  # ou nginx
```

**CentOS/RHEL:**
```bash
sudo yum install php83-curl php83-json
sudo systemctl restart httpd
```

**Windows:**
Descomente no `php.ini`:
```ini
extension=curl
extension=json
```

## 🔐 Problemas de Autenticação

### Erro: "Token não retornado na resposta"

**Problema:**
```php
AuthenticationException: Token não retornado na resposta
```

**Diagnóstico:**
```php
// Verificar resposta da API
$gateway = new ItauBoletoGateway(/* ... */);
try {
    $token = $gateway->authenticate();
} catch (AuthenticationException $e) {
    $lastResponse = $gateway->getLastResponse();
    var_dump($lastResponse); // Ver resposta completa
}
```

**Soluções:**
1. **Verificar credenciais:**
```php
// Confirmar se as credenciais estão corretas
echo "Client ID: " . $_ENV['ITAU_CLIENT_ID'] . "\n";
echo "Client Secret: " . substr($_ENV['ITAU_CLIENT_SECRET'], 0, 10) . "...\n";
```

2. **Verificar ambiente:**
```php
// Sandbox vs Produção
$gateway = new ItauBoletoGateway(
    // ...
    sandbox: true  // Confirmar se está correto
);
```

3. **Verificar certificados:**
```bash
# Testar certificado
openssl x509 -in certificado.crt -text -noout
openssl rsa -in chave.key -check
```

### Erro: "Invalid client credentials"

**Problema:**
```json
{
    "error": "invalid_client",
    "error_description": "Invalid client credentials"
}
```

**Soluções:**
1. **Regenerar credenciais** no portal do Itaú
2. **Verificar encoding** das credenciais:
```php
// Remover espaços em branco
$clientId = trim($_ENV['ITAU_CLIENT_ID']);
$clientSecret = trim($_ENV['ITAU_CLIENT_SECRET']);
```

3. **Verificar expiração** do certificado:
```bash
openssl x509 -in certificado.crt -dates -noout
```

## 📜 Problemas de Certificados

### Erro: "Certificado não encontrado"

**Problema:**
```
GatewayException: Certificado não encontrado: /path/to/cert.crt
```

**Soluções:**
1. **Verificar caminho:**
```php
$certPath = $_ENV['ITAU_CERTIFICATE_PATH'];
echo "Certificado existe: " . (file_exists($certPath) ? "Sim" : "Não") . "\n";
echo "Caminho: {$certPath}\n";
```

2. **Usar caminho absoluto:**
```env
# .env
ITAU_CERTIFICATE_PATH=/var/www/certificates/certificado.crt
ITAU_CERTIFICATE_KEY_PATH=/var/www/certificates/chave.key
```

3. **Verificar permissões:**
```bash
ls -la certificates/
chmod 644 certificates/certificado.crt
chmod 600 certificates/chave.key
```

### Erro: "SSL certificate problem"

**Problema:**
```
cURL error 60: SSL certificate problem: unable to get local issuer certificate
```

**Soluções:**
1. **Atualizar CA bundle:**
```bash
# Ubuntu/Debian
sudo apt update && sudo apt install ca-certificates

# CentOS/RHEL
sudo yum update ca-certificates
```

2. **Configurar cURL:**
```php
// Temporariamente para debug (NÃO usar em produção)
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
```

3. **Verificar certificado:**
```bash
# Testar conectividade SSL
openssl s_client -connect secure.api.itau:443 -servername secure.api.itau
```

### Erro: "Private key does not match certificate"

**Problema:**
```
SSL: private key does not match the certificate public key
```

**Solução:**
Verificar se certificado e chave correspondem:
```bash
# Comparar hashes
openssl x509 -noout -modulus -in certificado.crt | openssl md5
openssl rsa -noout -modulus -in chave.key | openssl md5
# Os hashes devem ser iguais
```

## 🌐 Problemas de API

### Erro: "HTTP 400 Bad Request"

**Problema:**
```
GatewayException: Erro na API: HTTP 400
```

**Diagnóstico:**
```php
class DebugGateway extends ItauBoletoGateway
{
    public function sendBoletoRequest(array $payload): array
    {
        echo "📤 Payload enviado:\n";
        echo json_encode($payload, JSON_PRETTY_PRINT) . "\n";
        
        try {
            return parent::sendBoletoRequest($payload);
        } catch (GatewayException $e) {
            echo "📥 Resposta de erro:\n";
            var_dump($this->getLastResponse());
            throw $e;
        }
    }
}
```

**Soluções comuns:**
1. **Validar dados obrigatórios:**
```php
// Verificar se todos os campos estão preenchidos
$required = ['beneficiario', 'pagador', 'dado_boleto'];
foreach ($required as $field) {
    if (!isset($payload[$field])) {
        echo "Campo obrigatório ausente: {$field}\n";
    }
}
```

2. **Verificar formato de datas:**
```php
// Formato correto: Y-m-d
$issueDate = (new DateTimeImmutable())->format('Y-m-d');
$dueDate = (new DateTimeImmutable('+30 days'))->format('Y-m-d');
```

3. **Verificar formato de valores:**
```php
use ItauBoletoPix\Utils\MoneyFormatter;

// Valor deve ter 15 dígitos
$formattedAmount = MoneyFormatter::format(150.00); // 00000000000015000
```

### Erro: "HTTP 401 Unauthorized"

**Problema:**
```
GatewayException: Erro na API: HTTP 401
```

**Soluções:**
1. **Renovar token:**
```php
// Forçar nova autenticação
$gateway = new ItauBoletoGateway(/* ... */);
$token = $gateway->authenticate(); // Força novo token
```

2. **Verificar headers:**
```php
// Verificar se o token está sendo enviado corretamente
$headers = [
    'Authorization: Bearer ' . $token,
    'x-itau-apikey: ' . $this->clientId,
    // ...
];
```

### Erro: "HTTP 403 Forbidden"

**Problema:**
```
GatewayException: Erro na API: HTTP 403
```

**Soluções:**
1. **Verificar permissões** da conta no Itaú
2. **Verificar ambiente** (sandbox vs produção)
3. **Verificar IP** se há whitelist configurada

### Erro: "HTTP 429 Too Many Requests"

**Problema:**
```
GatewayException: Erro na API: HTTP 429
```

**Solução:**
Implementar retry com backoff:
```php
class RateLimitedGateway extends ItauBoletoGateway
{
    public function sendBoletoRequest(array $payload): array
    {
        $maxRetries = 3;
        $delay = 1; // segundos
        
        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                return parent::sendBoletoRequest($payload);
            } catch (GatewayException $e) {
                if ($e->getHttpCode() === 429 && $i < $maxRetries - 1) {
                    sleep($delay * ($i + 1)); // Backoff exponencial
                    continue;
                }
                throw $e;
            }
        }
    }
}
```

## 🔗 Problemas de Webhooks

### Webhook não está sendo recebido

**Diagnóstico:**
1. **Verificar URL:**
```bash
# Testar se a URL está acessível
curl -X POST https://seu-dominio.com/webhooks/itau \
  -H "Content-Type: application/json" \
  -d '{"test": true}'
```

2. **Verificar logs do servidor:**
```bash
# Apache
tail -f /var/log/apache2/access.log
tail -f /var/log/apache2/error.log

# Nginx
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

**Soluções:**
1. **Usar ngrok para desenvolvimento:**
```bash
ngrok http 8000
# Use a URL gerada: https://abc123.ngrok.io/webhooks/itau
```

2. **Verificar firewall:**
```bash
# Permitir porta 80/443
sudo ufw allow 80
sudo ufw allow 443
```

3. **Testar endpoint manualmente:**
```php
// test-webhook-endpoint.php
<?php
$payload = json_decode(file_get_contents('php://input'), true);
error_log('Webhook recebido: ' . json_encode($payload));
echo json_encode(['status' => 'ok']);
```

### Erro: "Assinatura inválida"

**Problema:**
```
WebhookException: Assinatura inválida
```

**Debug:**
```php
// Verificar assinatura recebida vs calculada
$receivedSignature = $_SERVER['HTTP_X_ITAU_SIGNATURE'] ?? '';
$payload = json_decode(file_get_contents('php://input'), true);

$expectedSignature = hash_hmac(
    'sha256',
    json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    $_ENV['WEBHOOK_SECRET']
);

echo "Recebida: {$receivedSignature}\n";
echo "Esperada: {$expectedSignature}\n";
echo "Payload: " . json_encode($payload) . "\n";
```

**Soluções:**
1. **Verificar secret:**
```env
# Confirmar se o secret está correto
WEBHOOK_SECRET=seu-secret-configurado-no-itau
```

2. **Verificar encoding:**
```php
// Usar mesmo encoding do Itaú
$payload = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
```

### Webhook processado múltiplas vezes

**Problema:**
Mesmo webhook sendo processado várias vezes.

**Solução:**
Implementar idempotência:
```php
class IdempotentWebhookHandler
{
    private PDO $pdo;
    
    public function handle(array $payload): bool
    {
        $webhookId = $this->generateWebhookId($payload);
        
        // Verificar se já foi processado
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM processed_webhooks 
            WHERE webhook_id = ?
        ");
        $stmt->execute([$webhookId]);
        
        if ($stmt->fetchColumn() > 0) {
            return true; // Já processado
        }
        
        // Marcar como processado
        $stmt = $this->pdo->prepare("
            INSERT INTO processed_webhooks (webhook_id, processed_at) 
            VALUES (?, NOW())
        ");
        $stmt->execute([$webhookId]);
        
        // Processar webhook
        return $this->processWebhook($payload);
    }
    
    private function generateWebhookId(array $payload): string
    {
        return md5(json_encode($payload));
    }
}
```

## ✅ Problemas de Validação

### Erro: "CPF inválido"

**Problema:**
```
ValidationException: CPF inválido
```

**Debug:**
```php
use ItauBoletoPix\Utils\DocumentValidator;

$cpf = '123.456.789-00';
$cleanCpf = DocumentValidator::clean($cpf);

echo "CPF original: {$cpf}\n";
echo "CPF limpo: {$cleanCpf}\n";
echo "É válido: " . (DocumentValidator::isValidCPF($cpf) ? 'Sim' : 'Não') . "\n";
```

**Soluções:**
1. **Usar CPF válido para testes:**
```php
// CPFs válidos para teste
$validCpfs = [
    '11144477735',
    '22233344456',
    '33322211123'
];
```

2. **Implementar gerador de CPF para testes:**
```php
function generateValidCPF(): string
{
    $cpf = '';
    for ($i = 0; $i < 9; $i++) {
        $cpf .= mt_rand(0, 9);
    }
    
    // Calcular dígitos verificadores
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += (int)$cpf[$i] * (10 - $i);
    }
    $digit1 = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);
    
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += (int)$cpf[$i] * (11 - $i);
    }
    $sum += $digit1 * 2;
    $digit2 = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);
    
    return $cpf . $digit1 . $digit2;
}
```

### Erro: "Data de vencimento inválida"

**Problema:**
```
InvalidArgumentException: Data de vencimento não pode ser anterior à data de emissão
```

**Solução:**
```php
// Garantir que vencimento seja posterior à emissão
$issueDate = new DateTimeImmutable();
$dueDate = $issueDate->modify('+30 days'); // Sempre 30 dias após emissão

// Verificar se é dia útil
while (in_array($dueDate->format('N'), [6, 7])) { // 6=sábado, 7=domingo
    $dueDate = $dueDate->modify('+1 day');
}
```

### Erro: "Valor inválido"

**Problema:**
```
InvalidArgumentException: Valor do boleto deve ser maior que zero
```

**Soluções:**
1. **Validar valor:**
```php
$amount = 150.00;
if ($amount <= 0) {
    throw new InvalidArgumentException('Valor deve ser maior que zero');
}
if ($amount > 999999.99) {
    throw new InvalidArgumentException('Valor muito alto');
}
```

2. **Formatar corretamente:**
```php
// Garantir 2 casas decimais
$amount = round($amount, 2);
```

## 📊 Logs e Debug

### Habilitar Logs Detalhados

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

// Configurar logger
$logger = new Logger('itau-boleto');
$logger->pushHandler(new StreamHandler('logs/itau.log', Logger::DEBUG));
$logger->pushHandler(new RotatingFileHandler('logs/itau-daily.log', 0, Logger::INFO));

// Usar no serviço
$boletoService = new BoletoGenerationService($gateway, $logger);
```

### Debug de Requests HTTP

```php
class VerboseItauGateway extends ItauBoletoGateway
{
    protected function makeRequest(string $url, ?array $data, array $headers, string $method = 'POST'): array
    {
        echo "🔗 URL: {$url}\n";
        echo "📤 Method: {$method}\n";
        echo "📋 Headers:\n";
        foreach ($headers as $header) {
            echo "  {$header}\n";
        }
        
        if ($data) {
            echo "📦 Data:\n";
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
        
        $start = microtime(true);
        $response = parent::makeRequest($url, $data, $headers, $method);
        $duration = microtime(true) - $start;
        
        echo "⏱️ Duration: " . round($duration * 1000, 2) . "ms\n";
        echo "📥 Response:\n";
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        echo str_repeat('-', 80) . "\n";
        
        return $response;
    }
}
```

### Capturar Todas as Exceções

```php
try {
    $response = $boletoService->createBoleto($request);
} catch (AuthenticationException $e) {
    echo "🔐 Erro de autenticação:\n";
    echo "Mensagem: {$e->getMessage()}\n";
    echo "Contexto: " . json_encode($e->getContext(), JSON_PRETTY_PRINT) . "\n";
} catch (ValidationException $e) {
    echo "✅ Erro de validação:\n";
    echo "Mensagem: {$e->getMessage()}\n";
    echo "Erros: " . json_encode($e->getErrors(), JSON_PRETTY_PRINT) . "\n";
} catch (GatewayException $e) {
    echo "🌐 Erro de gateway:\n";
    echo "Mensagem: {$e->getMessage()}\n";
    echo "HTTP Code: {$e->getHttpCode()}\n";
    echo "Contexto: " . json_encode($e->getContext(), JSON_PRETTY_PRINT) . "\n";
} catch (BoletoException $e) {
    echo "📄 Erro de boleto:\n";
    echo "Mensagem detalhada: {$e->getDetailedMessage()}\n";
} catch (Exception $e) {
    echo "❌ Erro geral:\n";
    echo "Mensagem: {$e->getMessage()}\n";
    echo "Arquivo: {$e->getFile()}:{$e->getLine()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
}
```

## ❓ FAQ

### P: Como testar sem registrar boletos reais?

**R:** Use `ProcessStep::SIMULATION`:
```php
$request = new BoletoRequestDTO(
    // ... outros parâmetros
    processStep: ProcessStep::SIMULATION
);
```

### P: Posso usar a mesma instância do gateway para múltiplas requisições?

**R:** Sim, o gateway mantém o token em cache:
```php
$gateway = new ItauBoletoGateway(/* ... */);
$boletoService = new BoletoGenerationService($gateway);

// Múltiplas chamadas reutilizam o mesmo token
$response1 = $boletoService->createBoleto($request1);
$response2 = $boletoService->createBoleto($request2);
```

### P: Como gerar nosso número único?

**R:** Várias estratégias:
```php
// Sequencial (requer controle de estado)
$ourNumber = str_pad((string)$lastNumber + 1, 8, '0', STR_PAD_LEFT);

// Baseado em timestamp
$ourNumber = substr(time(), -8);

// Baseado em ID do banco de dados
$ourNumber = str_pad((string)$orderId, 8, '0', STR_PAD_LEFT);

// Híbrido (prefixo + sequencial)
$ourNumber = '99' . str_pad((string)$sequence, 6, '0', STR_PAD_LEFT);
```

### P: Como lidar com timeout de rede?

**R:** Configure timeout no cURL:
```php
class TimeoutGateway extends ItauBoletoGateway
{
    protected function makeRequest(/* ... */): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            // ... outras opções
            CURLOPT_TIMEOUT => 30,         // 30 segundos total
            CURLOPT_CONNECTTIMEOUT => 10,  // 10 segundos para conectar
        ]);
        
        // ... resto da implementação
    }
}
```

### P: Como implementar retry automático?

**R:**
```php
class RetryableService extends BoletoGenerationService
{
    public function createBoleto(BoletoRequestDTO $request): BoletoResponseDTO
    {
        $maxRetries = 3;
        $delay = 1;
        
        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                return parent::createBoleto($request);
            } catch (GatewayException $e) {
                if ($i === $maxRetries - 1) {
                    throw $e; // Última tentativa
                }
                
                sleep($delay * ($i + 1)); // Backoff
            }
        }
    }
}
```

### P: Como monitorar a saúde da integração?

**R:**
```php
class HealthChecker
{
    public function checkItauIntegration(): array
    {
        $results = [];
        
        try {
            // Testar conectividade
            $gateway = new ItauBoletoGateway(/* ... */);
            $connected = $gateway->testConnection();
            $results['connectivity'] = $connected ? 'OK' : 'FAIL';
            
            // Testar autenticação
            $token = $gateway->authenticate();
            $results['authentication'] = !empty($token) ? 'OK' : 'FAIL';
            
            // Testar certificados
            $certValid = file_exists($_ENV['ITAU_CERTIFICATE_PATH']);
            $keyValid = file_exists($_ENV['ITAU_CERTIFICATE_KEY_PATH']);
            $results['certificates'] = ($certValid && $keyValid) ? 'OK' : 'FAIL';
            
        } catch (Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
}
```

Se você ainda tiver problemas após seguir este guia, verifique:
1. Os logs detalhados da aplicação
2. Os logs do servidor web
3. A documentação oficial do Itaú
4. Entre em contato com o suporte técnico