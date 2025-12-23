# Itaú Boleto PIX - Biblioteca PHP

[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Version](https://img.shields.io/badge/version-1.0.0-orange.svg)](composer.json)

Biblioteca PHP de alto nível para geração de Boletos PIX no Itaú. Oferece uma interface simples e robusta para integração com a API do Itaú, permitindo criar boletos com PIX de forma rápida e segura.

## 🚀 Características

- ✅ **Geração de Boletos PIX** - Crie boletos com PIX integrado
- ✅ **Arquitetura Limpa** - Baseada em contratos e DTOs
- ✅ **Validações Automáticas** - CPF, CNPJ e dados obrigatórios
- ✅ **Tratamento de Erros** - Exceções específicas e contextualizadas
- ✅ **Webhooks** - Sistema completo de notificações
- ✅ **Utilitários** - Helpers para datas, documentos e valores
- ✅ **Sandbox/Produção** - Suporte completo aos dois ambientes
- ✅ **PSR-4** - Autoload compatível com Composer
- ✅ **PHP 8.3+** - Aproveita recursos modernos do PHP

## 📋 Requisitos

- PHP 8.3 ou superior
- Extensões: `curl`, `json`
- Certificado digital do Itaú (.crt e .key)
- Credenciais da API Itaú (Client ID e Secret)

## 📦 Instalação

```bash
composer require celiovmjr/itauboletopix
```

## ⚙️ Configuração

### 1. Variáveis de Ambiente

Copie o arquivo `.env.example` para `.env` e configure:

```bash
cp .env.example .env
```

```env
# Credenciais da API
ITAU_CLIENT_ID=seu-client-id-aqui
ITAU_CLIENT_SECRET=seu-client-secret-aqui

# Certificados
ITAU_CERTIFICATE_PATH=/path/to/certificado.crt
ITAU_CERTIFICATE_KEY_PATH=/path/to/chave.key

# Ambiente
ITAU_SANDBOX=true

# Dados do Beneficiário
ITAU_BENEFICIARY_AGENCY=1111
ITAU_BENEFICIARY_ACCOUNT=0022222
ITAU_BENEFICIARY_ACCOUNT_DIGIT=3
ITAU_PIX_KEY=sua-chave@pix.com.br
```

### 2. Certificados

Coloque os certificados fornecidos pelo Itaú na pasta `certificates/`:
- `certificado.crt` - Certificado público
- `chave.key` - Chave privada

## 🎯 Uso Básico

### Exemplo Simples

```php
<?php

require_once 'vendor/autoload.php';

use ItauBoletoPix\DTOs\BoletoRequestDTO;
use ItauBoletoPix\Enums\{ProcessStep, WalletCode};
use ItauBoletoPix\Gateways\ItauBoletoGateway;
use ItauBoletoPix\Models\{Address, Beneficiary, Payer, PhysicalPerson};
use ItauBoletoPix\Services\BoletoGenerationService;

// 1. Configurar Gateway
$gateway = new ItauBoletoGateway(
    clientId: $_ENV['ITAU_CLIENT_ID'],
    clientSecret: $_ENV['ITAU_CLIENT_SECRET'],
    certificatePath: $_ENV['ITAU_CERTIFICATE_PATH'],
    certificateKeyPath: $_ENV['ITAU_CERTIFICATE_KEY_PATH'],
    sandbox: true
);

$boletoService = new BoletoGenerationService($gateway);

// 2. Configurar Beneficiário
$beneficiary = new Beneficiary(
    agency: $_ENV['ITAU_BENEFICIARY_AGENCY'],
    account: $_ENV['ITAU_BENEFICIARY_ACCOUNT'],
    accountDigit: $_ENV['ITAU_BENEFICIARY_ACCOUNT_DIGIT'],
    pixKey: $_ENV['ITAU_PIX_KEY']
);

// 3. Criar Pagador
$address = new Address(
    street: 'Av Paulista, 1000',
    neighborhood: 'Bela Vista',
    city: 'São Paulo',
    state: 'SP',
    zipCode: '01310-100'
);

$person = new PhysicalPerson(
    name: 'João da Silva',
    document: '123.456.789-00',
    address: $address
);

$payer = new Payer($person);

// 4. Criar Request
$request = new BoletoRequestDTO(
    beneficiary: $beneficiary,
    payer: $payer,
    ourNumber: '00000001',
    yourNumber: '000001',
    amount: 150.00,
    issueDate: new DateTimeImmutable(),
    dueDate: new DateTimeImmutable('+30 days'),
    processStep: ProcessStep::SIMULATION
);

// 5. Gerar Boleto
try {
    $response = $boletoService->createBoleto($request);
    
    echo "✅ Boleto gerado com sucesso!\n";
    echo "ID: {$response->id}\n";
    echo "Nosso Número: {$response->ourNumber}\n";
    echo "PIX Copia e Cola: {$response->pixCopyPaste}\n";
    echo "QR Code: {$response->pixQrCode}\n";
    
} catch (Exception $e) {
    echo "❌ Erro: {$e->getMessage()}\n";
}
```

## 📚 Documentação Detalhada

### Modelos Principais

#### Beneficiary (Quem recebe)
```php
$beneficiary = new Beneficiary(
    agency: '1111',           // 4 dígitos
    account: '0022222',       // 7 dígitos  
    accountDigit: '3',        // 1 dígito
    pixKey: 'empresa@email.com',
    walletCode: WalletCode::REGISTERED_109
);
```

#### Payer (Quem paga)
```php
// Pessoa Física
$person = new PhysicalPerson(
    name: 'João da Silva',
    document: '123.456.789-00',
    address: $address
);

// Pessoa Jurídica
$company = new LegalPerson(
    name: 'Empresa LTDA',
    document: '12.345.678/0001-99',
    address: $address
);

$payer = new Payer($person); // ou $company
```

#### Address
```php
$address = new Address(
    street: 'Rua das Flores, 123',
    neighborhood: 'Centro',
    city: 'São Paulo',
    state: 'SP',
    zipCode: '01234-567'
);
```

### Configurações Avançadas

#### Juros, Multa e Desconto
```php
use ItauBoletoPix\Models\{Charge, Interest, Fine, Discount};

// Juros de R$ 5,00 por dia
$interest = new Interest(
    type: '93',
    amountPerDay: 5.00
);

// Multa de 2%
$fine = new Fine(
    type: '02',
    percentage: 2.0
);

// Desconto de 5% até 10 dias antes
$discount = new Discount(
    type: '02',
    dueDate: new DateTimeImmutable('+20 days'),
    amount: 25.00,
    percentage: 5.0
);

$charge = new Charge(
    interest: $interest,
    fine: $fine,
    discount: $discount,
    messages: [
        'Não receber após vencimento',
        'Juros de R$ 5,00 por dia'
    ]
);

// Usar no request
$request = new BoletoRequestDTO(
    // ... outros parâmetros
    charge: $charge
);
```

### Webhooks

#### Configurar Handler
```php
use ItauBoletoPix\Webhooks\ItauWebhookHandler;

$webhookHandler = new ItauWebhookHandler();

// Listener para pagamentos
$webhookHandler->on('paid', function($payload) {
    echo "Boleto pago: {$payload->getOurNumber()}\n";
    echo "Valor: R$ {$payload->getPaidAmount()}\n";
    
    // Atualizar banco de dados
    // Enviar email de confirmação
    // Liberar acesso
});

// Listener para cancelamentos
$webhookHandler->on('cancelled', function($payload) {
    echo "Boleto cancelado: {$payload->getOurNumber()}\n";
});

// Processar webhook recebido
$webhookData = json_decode(file_get_contents('php://input'), true);
$webhookHandler->handle($webhookData);
```

### Enums Disponíveis

#### ProcessStep
```php
ProcessStep::SIMULATION   // Apenas simula (não registra)
ProcessStep::REGISTRATION // Registra efetivamente
```

#### PersonType
```php
PersonType::INDIVIDUAL // Pessoa Física (CPF)
PersonType::COMPANY    // Pessoa Jurídica (CNPJ)
```

#### WalletCode
```php
WalletCode::REGISTERED_109 // Carteira registrada Itaú
```

### Utilitários

#### Validação de Documentos
```php
use ItauBoletoPix\Utils\DocumentValidator;

// Validar CPF
if (DocumentValidator::isValidCPF('123.456.789-00')) {
    echo "CPF válido\n";
}

// Validar CNPJ
if (DocumentValidator::isValidCNPJ('12.345.678/0001-99')) {
    echo "CNPJ válido\n";
}

// Limpar formatação
$clean = DocumentValidator::clean('123.456.789-00'); // 12345678900
```

#### Formatação de Valores
```php
use ItauBoletoPix\Utils\MoneyFormatter;

// Formatar para API Itaú
$formatted = MoneyFormatter::format(150.75); // 00000000000015075

// Converter de volta
$amount = MoneyFormatter::parse('00000000000015075'); // 150.75
```

#### Helpers de Data
```php
use ItauBoletoPix\Utils\DateHelper;

// Primeiro dia do mês
$firstDay = DateHelper::firstDayOfMonth();

// Adicionar dias úteis
$futureDate = DateHelper::addBusinessDays(new DateTimeImmutable(), 5);

// Verificar se é dia 01
if (DateHelper::isFirstDayOfMonth()) {
    echo "Hoje é dia 01\n";
}
```

## 🔧 Tratamento de Erros

### Exceções Específicas

```php
use ItauBoletoPix\Exceptions\{
    BoletoException,
    GatewayException,
    ValidationException,
    AuthenticationException
};

try {
    $response = $boletoService->createBoleto($request);
} catch (AuthenticationException $e) {
    echo "Erro de autenticação: {$e->getMessage()}\n";
} catch (ValidationException $e) {
    echo "Erro de validação:\n";
    foreach ($e->getErrors() as $error) {
        echo "- {$error}\n";
    }
} catch (GatewayException $e) {
    echo "Erro na API: {$e->getMessage()}\n";
    echo "HTTP Code: {$e->getHttpCode()}\n";
} catch (BoletoException $e) {
    echo "Erro geral: {$e->getDetailedMessage()}\n";
}
```

## 🧪 Testes

### Executar Testes
```bash
# Todos os testes
composer test

# Análise estática
composer stan

# Code style
composer cs
```

### Testar Conexão
```php
if ($gateway->testConnection()) {
    echo "✅ Conexão OK\n";
} else {
    echo "❌ Falha na conexão\n";
}
```

## 📁 Estrutura do Projeto

```
src/
├── Contracts/          # Interfaces
│   ├── BoletoServiceInterface.php
│   ├── PaymentGatewayInterface.php
│   ├── PersonInterface.php
│   └── WebhookHandlerInterface.php
├── DTOs/              # Data Transfer Objects
│   ├── BoletoRequestDTO.php
│   ├── BoletoResponseDTO.php
│   └── WebhookPayload.php
├── Enums/             # Enumerações
│   ├── BoletoType.php
│   ├── PersonType.php
│   ├── ProcessStep.php
│   └── WalletCode.php
├── Exceptions/        # Exceções customizadas
│   ├── BoletoException.php
│   ├── GatewayException.php
│   └── ValidationException.php
├── Gateways/          # Implementações de gateway
│   └── ItauBoletoGateway.php
├── Models/            # Modelos de domínio
│   ├── Address.php
│   ├── Beneficiary.php
│   ├── Boleto.php
│   ├── Charge.php
│   ├── Payer.php
│   └── Person.php
├── Services/          # Serviços de negócio
│   └── BoletoGenerationService.php
├── Utils/             # Utilitários
│   ├── DateHelper.php
│   ├── DocumentValidator.php
│   ├── MoneyFormatter.php
│   └── UuidHelper.php
└── Webhooks/          # Processamento de webhooks
    └── ItauWebhookHandler.php
```

## 🔒 Segurança

- ✅ Validação de certificados SSL
- ✅ Validação de assinatura de webhooks
- ✅ Sanitização de dados de entrada
- ✅ Não exposição de credenciais em logs
- ✅ Timeouts configuráveis para requests

## 🌍 Ambientes

### Sandbox (Desenvolvimento)
```php
$gateway = new ItauBoletoGateway(
    // ... credenciais
    sandbox: true
);
```

### Produção
```php
$gateway = new ItauBoletoGateway(
    // ... credenciais
    sandbox: false
);
```

## 📝 Exemplos Completos

Veja a pasta `examples/` para exemplos detalhados:

- `basic-usage.php` - Uso básico e simples
- `complete-usage.php` - Exemplo completo com todas as funcionalidades
- `boleto.php` - Interface web para visualização de boletos

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🏆 Créditos

Desenvolvido com ❤️ pela equipe [ZukPay](https://zukpay.com.br)

---

**⚠️ Importante**: Esta biblioteca não é oficial do Itaú. Use por sua conta e risco em ambiente de produção.