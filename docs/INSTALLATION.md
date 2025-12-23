# Guia de Instalação - Itaú Boleto PIX

## 📋 Pré-requisitos

### Requisitos do Sistema
- **PHP**: 8.3 ou superior
- **Extensões PHP**:
  - `curl` - Para comunicação com APIs
  - `json` - Para manipulação de dados JSON
- **Composer**: Para gerenciamento de dependências
- **Certificados Itaú**: Fornecidos pelo banco

### Credenciais Necessárias
Você precisará obter do Itaú:
- Client ID
- Client Secret  
- Certificado digital (.crt)
- Chave privada (.key)
- Dados da conta (agência, conta, dígito)
- Chave PIX cadastrada

## 🚀 Instalação

### 1. Via Composer (Recomendado)

```bash
composer require zukpay/itau-boleto-pix
```

### 2. Instalação Manual

```bash
# Clone o repositório
git clone https://github.com/zukpay/itau-boleto-pix.git

# Entre no diretório
cd itau-boleto-pix

# Instale as dependências
composer install
```

## ⚙️ Configuração

### 1. Configurar Variáveis de Ambiente

Copie o arquivo de exemplo:
```bash
cp .env.example .env
```

Edite o arquivo `.env`:
```env
# =============================================================================
# CREDENCIAIS ITAÚ
# =============================================================================
ITAU_CLIENT_ID=seu-client-id-fornecido-pelo-itau
ITAU_CLIENT_SECRET=seu-client-secret-fornecido-pelo-itau

# =============================================================================
# CERTIFICADOS
# =============================================================================
ITAU_CERTIFICATE_PATH=/caminho/completo/para/certificado.crt
ITAU_CERTIFICATE_KEY_PATH=/caminho/completo/para/chave.key

# =============================================================================
# AMBIENTE
# =============================================================================
# true = Sandbox (desenvolvimento)
# false = Produção
ITAU_SANDBOX=true

# =============================================================================
# DADOS DO BENEFICIÁRIO (SUA EMPRESA)
# =============================================================================
ITAU_BENEFICIARY_AGENCY=1111
ITAU_BENEFICIARY_ACCOUNT=0022222
ITAU_BENEFICIARY_ACCOUNT_DIGIT=3
ITAU_BENEFICIARY_WALLET_CODE=109
ITAU_PIX_KEY=sua-chave@pix.com.br

# =============================================================================
# WEBHOOK (OPCIONAL)
# =============================================================================
WEBHOOK_URL=https://seu-dominio.com/webhooks/itau
WEBHOOK_SECRET=seu-secret-para-validacao-de-assinatura

# =============================================================================
# LOGS (OPCIONAL)
# =============================================================================
LOG_LEVEL=info
LOG_PATH=/var/log/itau-boleto/
```

### 2. Configurar Certificados

#### Estrutura Recomendada
```
projeto/
├── certificates/
│   ├── certificado.crt
│   ├── chave.key
│   └── credencial.txt (backup das credenciais)
├── .env
└── ...
```

#### Permissões dos Certificados
```bash
# Definir permissões seguras
chmod 600 certificates/chave.key
chmod 644 certificates/certificado.crt

# Verificar se os arquivos existem
ls -la certificates/
```

### 3. Autoload do Composer

Certifique-se de incluir o autoload em seu projeto:

```php
<?php
require_once 'vendor/autoload.php';

// Carregar variáveis de ambiente (se usando vlucas/phpdotenv)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
```

## 🧪 Teste da Instalação

### Teste Básico de Conexão

Crie um arquivo `test-connection.php`:

```php
<?php

require_once 'vendor/autoload.php';

use ItauBoletoPix\Gateways\ItauBoletoGateway;

// Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Criar gateway
    $gateway = new ItauBoletoGateway(
        clientId: $_ENV['ITAU_CLIENT_ID'],
        clientSecret: $_ENV['ITAU_CLIENT_SECRET'],
        certificatePath: $_ENV['ITAU_CERTIFICATE_PATH'],
        certificateKeyPath: $_ENV['ITAU_CERTIFICATE_KEY_PATH'],
        sandbox: (bool)$_ENV['ITAU_SANDBOX']
    );

    // Testar conexão
    echo "🔄 Testando conexão com API do Itaú...\n";
    
    if ($gateway->testConnection()) {
        echo "✅ Conexão estabelecida com sucesso!\n";
        echo "🎯 Ambiente: " . ($_ENV['ITAU_SANDBOX'] ? 'Sandbox' : 'Produção') . "\n";
    } else {
        echo "❌ Falha na conexão\n";
    }

} catch (Exception $e) {
    echo "❌ Erro: {$e->getMessage()}\n";
    
    // Verificações adicionais
    echo "\n🔍 Verificações:\n";
    
    // Verificar certificados
    if (!file_exists($_ENV['ITAU_CERTIFICATE_PATH'])) {
        echo "❌ Certificado não encontrado: {$_ENV['ITAU_CERTIFICATE_PATH']}\n";
    } else {
        echo "✅ Certificado encontrado\n";
    }
    
    if (!file_exists($_ENV['ITAU_CERTIFICATE_KEY_PATH'])) {
        echo "❌ Chave privada não encontrada: {$_ENV['ITAU_CERTIFICATE_KEY_PATH']}\n";
    } else {
        echo "✅ Chave privada encontrada\n";
    }
    
    // Verificar extensões
    if (!extension_loaded('curl')) {
        echo "❌ Extensão CURL não instalada\n";
    } else {
        echo "✅ Extensão CURL disponível\n";
    }
    
    if (!extension_loaded('json')) {
        echo "❌ Extensão JSON não instalada\n";
    } else {
        echo "✅ Extensão JSON disponível\n";
    }
}
```

Execute o teste:
```bash
php test-connection.php
```

### Teste de Geração de Boleto

Crie um arquivo `test-boleto.php`:

```php
<?php

require_once 'vendor/autoload.php';

use ItauBoletoPix\DTOs\BoletoRequestDTO;
use ItauBoletoPix\Enums\ProcessStep;
use ItauBoletoPix\Gateways\ItauBoletoGateway;
use ItauBoletoPix\Models\{Address, Beneficiary, Payer, PhysicalPerson};
use ItauBoletoPix\Services\BoletoGenerationService;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    echo "🔄 Testando geração de boleto...\n";

    // Configurar serviços
    $gateway = new ItauBoletoGateway(
        clientId: $_ENV['ITAU_CLIENT_ID'],
        clientSecret: $_ENV['ITAU_CLIENT_SECRET'],
        certificatePath: $_ENV['ITAU_CERTIFICATE_PATH'],
        certificateKeyPath: $_ENV['ITAU_CERTIFICATE_KEY_PATH'],
        sandbox: true
    );

    $boletoService = new BoletoGenerationService($gateway);

    // Configurar beneficiário
    $beneficiary = new Beneficiary(
        agency: $_ENV['ITAU_BENEFICIARY_AGENCY'],
        account: $_ENV['ITAU_BENEFICIARY_ACCOUNT'],
        accountDigit: $_ENV['ITAU_BENEFICIARY_ACCOUNT_DIGIT'],
        pixKey: $_ENV['ITAU_PIX_KEY']
    );

    // Criar pagador de teste
    $address = new Address(
        street: 'Rua de Teste, 123',
        neighborhood: 'Centro',
        city: 'São Paulo',
        state: 'SP',
        zipCode: '01234-567'
    );

    $person = new PhysicalPerson(
        name: 'João da Silva Teste',
        document: '123.456.789-00',
        address: $address
    );

    $payer = new Payer($person);

    // Criar request de teste
    $request = new BoletoRequestDTO(
        beneficiary: $beneficiary,
        payer: $payer,
        ourNumber: str_pad((string)random_int(1, 99999999), 8, '0', STR_PAD_LEFT),
        yourNumber: 'TESTE001',
        amount: 10.00,
        issueDate: new DateTimeImmutable(),
        dueDate: new DateTimeImmutable('+30 days'),
        processStep: ProcessStep::SIMULATION // Apenas simula, não registra
    );

    // Gerar boleto
    $response = $boletoService->createBoleto($request);

    echo "✅ Boleto de teste gerado com sucesso!\n";
    echo "📄 ID: {$response->id}\n";
    echo "🔢 Nosso Número: {$response->ourNumber}\n";
    echo "💰 Valor: R$ 10,00\n";
    echo "📅 Vencimento: " . (new DateTimeImmutable('+30 days'))->format('d/m/Y') . "\n";
    echo "🎯 PIX disponível: " . (!empty($response->pixCopyPaste) ? 'Sim' : 'Não') . "\n";

} catch (Exception $e) {
    echo "❌ Erro no teste: {$e->getMessage()}\n";
}
```

Execute o teste:
```bash
php test-boleto.php
```

## 🔧 Solução de Problemas

### Problemas Comuns

#### 1. Erro de Certificado
```
Erro: Certificado não encontrado
```
**Solução**: Verifique se o caminho está correto e se o arquivo existe:
```bash
ls -la /caminho/para/certificado.crt
```

#### 2. Erro de Permissão
```
Erro: Permission denied
```
**Solução**: Ajuste as permissões:
```bash
chmod 600 certificates/chave.key
chmod 644 certificates/certificado.crt
```

#### 3. Erro de Extensão
```
Erro: Call to undefined function curl_init()
```
**Solução**: Instale a extensão CURL:
```bash
# Ubuntu/Debian
sudo apt-get install php-curl

# CentOS/RHEL
sudo yum install php-curl

# Windows (descomente no php.ini)
extension=curl
```

#### 4. Erro de Autenticação
```
Erro: Token não retornado na resposta
```
**Solução**: Verifique as credenciais no `.env`:
- Client ID correto
- Client Secret correto
- Certificados válidos

#### 5. Erro de Ambiente
```
Erro: HTTP 404
```
**Solução**: Verifique se está usando o ambiente correto:
- `ITAU_SANDBOX=true` para desenvolvimento
- `ITAU_SANDBOX=false` para produção

### Logs de Debug

Para ativar logs detalhados, configure um logger:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('itau-boleto');
$logger->pushHandler(new StreamHandler('logs/itau.log', Logger::DEBUG));

$boletoService = new BoletoGenerationService($gateway, $logger);
```

## 📞 Suporte

Se ainda tiver problemas:

1. **Verifique a documentação**: [README.md](../README.md)
2. **Execute os testes**: Certifique-se de que os testes básicos passam
3. **Verifique os logs**: Analise os logs de erro para mais detalhes
4. **Contate o suporte**: seu@email.com

## ✅ Checklist de Instalação

- [ ] PHP 8.3+ instalado
- [ ] Extensões `curl` e `json` ativas
- [ ] Composer instalado
- [ ] Biblioteca instalada via Composer
- [ ] Arquivo `.env` configurado
- [ ] Certificados no local correto
- [ ] Permissões dos certificados ajustadas
- [ ] Teste de conexão executado com sucesso
- [ ] Teste de geração de boleto executado com sucesso

Parabéns! 🎉 Sua instalação está completa e funcionando.