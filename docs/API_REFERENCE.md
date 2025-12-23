# Referência da API - Itaú Boleto PIX

## 📋 Índice

- [Contratos (Interfaces)](#contratos-interfaces)
- [DTOs (Data Transfer Objects)](#dtos-data-transfer-objects)
- [Modelos](#modelos)
- [Serviços](#serviços)
- [Gateways](#gateways)
- [Enums](#enums)
- [Utilitários](#utilitários)
- [Exceções](#exceções)

## 🔌 Contratos (Interfaces)

### BoletoServiceInterface

Interface principal para serviços de geração de boletos.

```php
interface BoletoServiceInterface
{
    public function createBoleto(BoletoRequestDTO $request): BoletoResponseDTO;
    public function getBoleto(string $ourNumber): Boleto;
    public function listBoletos(array $filters = []): array;
    public function cancelBoleto(string $ourNumber): bool;
    public function getPaymentStatus(string $ourNumber): string;
}
```

#### Métodos

##### `createBoleto(BoletoRequestDTO $request): BoletoResponseDTO`
Cria um novo boleto PIX.

**Parâmetros:**
- `$request` - Dados do boleto a ser criado

**Retorna:** `BoletoResponseDTO` com dados do boleto gerado

**Exceções:** `BoletoException`

##### `getBoleto(string $ourNumber): Boleto`
Consulta um boleto existente.

**Parâmetros:**
- `$ourNumber` - Nosso número do boleto

**Retorna:** `Boleto` com dados completos

##### `listBoletos(array $filters = []): array`
Lista boletos com filtros opcionais.

**Parâmetros:**
- `$filters` - Filtros de busca (período, status, etc)

**Retorna:** Array de objetos `Boleto`

##### `cancelBoleto(string $ourNumber): bool`
Cancela um boleto.

**Parâmetros:**
- `$ourNumber` - Nosso número do boleto

**Retorna:** `true` se cancelado com sucesso

##### `getPaymentStatus(string $ourNumber): string`
Verifica status de pagamento.

**Parâmetros:**
- `$ourNumber` - Nosso número do boleto

**Retorna:** Status (`pending`, `paid`, `cancelled`, `expired`)

### PaymentGatewayInterface

Interface para gateways de pagamento.

```php
interface PaymentGatewayInterface
{
    public function authenticate(): string;
    public function sendBoletoRequest(array $payload): array;
    public function fetchBoleto(string $beneficiaryId, string $ourNumber): array;
    public function registerWebhook(array $webhookConfig): array;
    public function testConnection(): bool;
    public function getLastResponse(): ?array;
}
```

### PersonInterface

Interface para representar pessoas (física ou jurídica).

```php
interface PersonInterface
{
    public function getName(): string;
    public function getDocument(bool $unmasked = true): ?string;
    public function getDocumentType(): string;
    public function getAddress(): Address;
}
```

### WebhookHandlerInterface

Interface para processamento de webhooks.

```php
interface WebhookHandlerInterface
{
    public function handle(array $rawPayload): bool;
    public function validateSignature(array $payload, string $signature): bool;
    public function parsePayload(array $rawPayload): WebhookPayload;
    public function on(string $event, callable $callback): void;
}
```

## 📦 DTOs (Data Transfer Objects)

### BoletoRequestDTO

DTO para requisição de criação de boleto.

```php
readonly class BoletoRequestDTO
{
    public function __construct(
        public Beneficiary $beneficiary,
        public Payer $payer,
        public string $ourNumber,
        public string $yourNumber,
        public float $amount,
        public \DateTimeImmutable $issueDate,
        public \DateTimeImmutable $dueDate,
        public ?Charge $charge = null,
        public ProcessStep $processStep = ProcessStep::REGISTRATION
    ) {}
    
    public function toArray(): array;
}
```

#### Propriedades

- `$beneficiary` - Dados do beneficiário (quem recebe)
- `$payer` - Dados do pagador (quem paga)
- `$ourNumber` - Nosso número (máximo 8 dígitos)
- `$yourNumber` - Seu número (identificação interna)
- `$amount` - Valor do boleto em reais
- `$issueDate` - Data de emissão
- `$dueDate` - Data de vencimento
- `$charge` - Configurações de cobrança (opcional)
- `$processStep` - Etapa do processo (simulação ou efetivação)

### BoletoResponseDTO

DTO para resposta de criação de boleto.

```php
readonly class BoletoResponseDTO
{
    public function __construct(
        public string $id,
        public string $ourNumber,
        public string $barcode,
        public string $digitableLine,
        public string $pixCopyPaste,
        public string $pixQrCode,
        public string $pixTxid,
        public string $amount,
        public string $dueDate,
        public array $rawResponse = []
    ) {}
    
    public function toArray(): array;
}
```

#### Propriedades

- `$id` - ID interno do boleto
- `$ourNumber` - Nosso número retornado
- `$barcode` - Código de barras
- `$digitableLine` - Linha digitável
- `$pixCopyPaste` - PIX copia e cola
- `$pixQrCode` - QR Code PIX (base64)
- `$pixTxid` - Transaction ID do PIX
- `$amount` - Valor formatado
- `$dueDate` - Data de vencimento formatada
- `$rawResponse` - Resposta bruta da API

### WebhookPayload

DTO para payload de webhooks.

```php
class WebhookPayload
{
    public function __construct(
        private string $eventType,
        private string $ourNumber,
        private ?string $paymentDate = null,
        private ?float $paidAmount = null,
        private array $rawData = []
    ) {}
    
    public function getEventType(): string;
    public function getOurNumber(): string;
    public function getPaymentDate(): ?string;
    public function getPaidAmount(): ?float;
    public function getRawData(): array;
    public function isPaid(): bool;
    public function isCancelled(): bool;
    public function toArray(): array;
}
```

## 🏗️ Modelos

### Boleto

Modelo principal do Boleto PIX.

```php
class Boleto
{
    public function __construct(
        private Beneficiary $beneficiary,
        private Payer $payer,
        private string $ourNumber,
        private string $yourNumber,
        private float $amount,
        private \DateTimeImmutable $issueDate,
        private \DateTimeImmutable $dueDate,
        private ?Charge $charge = null,
        private string $processStep = 'Efetivacao',
        private string $boletoType = 'a vista',
        private string $walletCode = '109',
        private string $speciesCode = '01'
    ) {}
}
```

#### Métodos Principais

```php
// Getters
public function getId(): ?string;
public function getBeneficiary(): Beneficiary;
public function getPayer(): Payer;
public function getOurNumber(): string;
public function getAmount(): float;
public function getDueDate(): \DateTimeImmutable;

// Setters (para dados da API)
public function setId(string $id): void;
public function setBarcode(string $barcode): void;
public function setDigitableLine(string $digitableLine): void;
public function setStatus(string $status): void;

// Métodos auxiliares
public function isPaid(): bool;
public function isCancelled(): bool;
public function isExpired(): bool;
public function toArray(): array;
```

### Beneficiary

Modelo do beneficiário (quem recebe o pagamento).

```php
class Beneficiary
{
    public function __construct(
        private string $agency,           // 4 dígitos
        private string $account,          // 7 dígitos
        private string $accountDigit,     // 1 dígito
        private string $pixKey,
        private WalletCode $walletCode = WalletCode::REGISTERED_109
    ) {}
    
    public function getId(): string;                    // AgenciaContaDigito
    public function getAgency(): string;
    public function getAccount(): string;
    public function getAccountDigit(): string;
    public function getWalletCode(): string;
    public function getPixKey(): string;
    public function toArray(): array;
}
```

### Payer

Modelo do pagador (quem paga o boleto).

```php
class Payer
{
    public function __construct(
        private PersonInterface $person
    ) {}
    
    public function getPerson(): PersonInterface;
    public function getName(): string;
    public function getDocument(): string;
    public function getDocumentType(): string;
    public function getAddress(): Address;
    public function toArray(): array;
}
```

### PhysicalPerson

Pessoa física (CPF).

```php
class PhysicalPerson implements PersonInterface
{
    public function __construct(
        private string $name,
        private string $document,        // CPF
        private Address $address
    ) {}
    
    public function getName(): string;
    public function getDocument(bool $unmasked = true): string;
    public function getDocumentType(): string;          // Retorna 'F'
    public function getAddress(): Address;
}
```

### LegalPerson

Pessoa jurídica (CNPJ).

```php
class LegalPerson implements PersonInterface
{
    public function __construct(
        private string $name,
        private string $document,        // CNPJ
        private Address $address
    ) {}
    
    public function getName(): string;
    public function getDocument(bool $unmasked = true): string;
    public function getDocumentType(): string;          // Retorna 'J'
    public function getAddress(): Address;
}
```

### Address

Endereço.

```php
class Address
{
    public function __construct(
        private string $street,
        private string $neighborhood,
        private string $city,
        private string $state,           // 2 caracteres (SP, RJ, etc)
        private string $zipCode
    ) {}
    
    public function getStreet(): string;
    public function getNeighborhood(): string;
    public function getCity(): string;
    public function getState(): string;
    public function getZipCode(): string;
    public function toArray(): array;
}
```

### Charge

Configurações de cobrança (juros, multa, desconto).

```php
class Charge
{
    public function __construct(
        private ?Interest $interest = null,
        private ?Fine $fine = null,
        private ?Discount $discount = null,
        private array $messages = []
    ) {}
    
    public function getInterest(): ?Interest;
    public function getFine(): ?Fine;
    public function getDiscount(): ?Discount;
    public function getMessages(): array;
    public function hasInterest(): bool;
    public function hasFine(): bool;
    public function hasDiscount(): bool;
    public function toArray(): array;
}
```

### Interest

Juros do boleto.

```php
class Interest
{
    public function __construct(
        private string $type,            // '93' = valor por dia
        private float $amountPerDay
    ) {}
    
    public function getType(): string;
    public function getAmountPerDay(): float;
    public function toArray(): array;
}
```

### Fine

Multa do boleto.

```php
class Fine
{
    public function __construct(
        private string $type,            // '02' = percentual
        private float $percentage
    ) {}
    
    public function getType(): string;
    public function getPercentage(): float;
    public function toArray(): array;
}
```

### Discount

Desconto do boleto.

```php
class Discount
{
    public function __construct(
        private string $type,            // '02' = percentual até data
        private \DateTimeImmutable $dueDate,
        private float $amount,
        private float $percentage
    ) {}
    
    public function getType(): string;
    public function getDueDate(): \DateTimeImmutable;
    public function getAmount(): float;
    public function getPercentage(): float;
    public function toArray(): array;
}
```

## 🔧 Serviços

### BoletoGenerationService

Serviço principal para geração de boletos.

```php
class BoletoGenerationService implements BoletoServiceInterface
{
    public function __construct(
        private PaymentGatewayInterface $gateway,
        private ?LoggerInterface $logger = null
    ) {}
    
    public function createBoleto(BoletoRequestDTO $request): BoletoResponseDTO;
    public function getBoleto(string $ourNumber): Boleto;
    public function listBoletos(array $filters = []): array;
    public function cancelBoleto(string $ourNumber): bool;
    public function getPaymentStatus(string $ourNumber): string;
}
```

## 🌐 Gateways

### ItauBoletoGateway

Gateway para comunicação com a API do Itaú.

```php
class ItauBoletoGateway implements PaymentGatewayInterface
{
    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $certificatePath,
        private string $certificateKeyPath,
        private bool $sandbox = true,
        private ?LoggerInterface $logger = null
    ) {}
    
    public function authenticate(): string;
    public function sendBoletoRequest(array $payload): array;
    public function fetchBoleto(string $beneficiaryId, string $ourNumber): array;
    public function registerWebhook(array $webhookConfig): array;
    public function testConnection(): bool;
    public function getLastResponse(): ?array;
}
```

## 📊 Enums

### ProcessStep

Etapa do processo de emissão.

```php
enum ProcessStep: string
{
    case SIMULATION = 'Simulacao';      // Apenas simula
    case REGISTRATION = 'Efetivacao';   // Registra efetivamente
}
```

### PersonType

Tipo de pessoa.

```php
enum PersonType: string
{
    case INDIVIDUAL = 'F';              // Pessoa física (CPF)
    case COMPANY = 'J';                 // Pessoa jurídica (CNPJ)
}
```

### WalletCode

Código da carteira de cobrança.

```php
enum WalletCode: string
{
    case REGISTERED_109 = '109';        // Carteira registrada Itaú
}
```

### BoletoType

Tipo de boleto.

```php
enum BoletoType: string
{
    case AT_SIGHT = 'a vista';          // Pagamento à vista
}
```

## 🛠️ Utilitários

### DocumentValidator

Validador de documentos (CPF/CNPJ).

```php
class DocumentValidator
{
    public static function isValidCPF(string $cpf): bool;
    public static function isValidCNPJ(string $cnpj): bool;
    public static function clean(string $document): string;
}
```

#### Exemplos

```php
// Validar CPF
DocumentValidator::isValidCPF('123.456.789-00');    // true/false

// Validar CNPJ
DocumentValidator::isValidCNPJ('12.345.678/0001-99'); // true/false

// Limpar formatação
DocumentValidator::clean('123.456.789-00');          // '12345678900'
```

### MoneyFormatter

Formatador de valores monetários para o padrão Itaú.

```php
class MoneyFormatter
{
    public static function format(float $amount): string;
    public static function parse(string $formattedValue): float;
}
```

#### Exemplos

```php
// Formatar para API Itaú (15 dígitos representando centavos)
MoneyFormatter::format(100.50);     // '00000000000010050'
MoneyFormatter::format(1500.75);    // '00000000000150075'

// Converter de volta
MoneyFormatter::parse('00000000000010050');  // 100.50
```

### DateHelper

Helper para manipulação de datas.

```php
class DateHelper
{
    public static function firstDayOfMonth(?\DateTimeImmutable $date = null): \DateTimeImmutable;
    public static function lastDayOfMonth(?\DateTimeImmutable $date = null): \DateTimeImmutable;
    public static function isFirstDayOfMonth(?\DateTimeImmutable $date = null): bool;
    public static function addBusinessDays(\DateTimeImmutable $date, int $days): \DateTimeImmutable;
}
```

#### Exemplos

```php
// Primeiro dia do mês atual
$firstDay = DateHelper::firstDayOfMonth();

// Último dia do mês
$lastDay = DateHelper::lastDayOfMonth();

// Verificar se é dia 01
if (DateHelper::isFirstDayOfMonth()) {
    // Executar rotina mensal
}

// Adicionar 5 dias úteis
$futureDate = DateHelper::addBusinessDays(new DateTimeImmutable(), 5);
```

### UuidHelper

Helper para geração de UUIDs.

```php
class UuidHelper
{
    public static function generate(): string;
}
```

#### Exemplo

```php
$uuid = UuidHelper::generate(); // 'f47ac10b-58cc-4372-a567-0e02b2c3d479'
```

## ⚠️ Exceções

### BoletoException

Exceção base para erros de boleto.

```php
class BoletoException extends \Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        array $context = [],
        ?\Throwable $previous = null
    ) {}
    
    public function getContext(): array;
    public function getDetailedMessage(): string;
}
```

### GatewayException

Exceção para erros de gateway/API.

```php
class GatewayException extends BoletoException
{
    public function __construct(
        string $message,
        int $code = 0,
        ?int $httpCode = null,
        array $context = [],
        ?\Throwable $previous = null
    ) {}
    
    public function getHttpCode(): ?int;
}
```

### ValidationException

Exceção para erros de validação.

```php
class ValidationException extends BoletoException
{
    public function __construct(
        string $message,
        array $errors = [],
        array $context = []
    ) {}
    
    public function getErrors(): array;
}
```

### AuthenticationException

Exceção para erros de autenticação.

```php
class AuthenticationException extends GatewayException
{
    // Herda todos os métodos de GatewayException
}
```

### WebhookException

Exceção para erros de webhook.

```php
class WebhookException extends BoletoException
{
    // Herda todos os métodos de BoletoException
}
```

## 📝 Exemplos de Uso

### Exemplo Completo

```php
use ItauBoletoPix\DTOs\BoletoRequestDTO;
use ItauBoletoPix\Enums\ProcessStep;
use ItauBoletoPix\Gateways\ItauBoletoGateway;
use ItauBoletoPix\Models\{Address, Beneficiary, Payer, PhysicalPerson, Charge, Interest, Fine};
use ItauBoletoPix\Services\BoletoGenerationService;

// Gateway
$gateway = new ItauBoletoGateway(
    clientId: 'seu-client-id',
    clientSecret: 'seu-client-secret',
    certificatePath: '/path/to/cert.crt',
    certificateKeyPath: '/path/to/key.key',
    sandbox: true
);

// Serviço
$boletoService = new BoletoGenerationService($gateway);

// Beneficiário
$beneficiary = new Beneficiary(
    agency: '1111',
    account: '0022222',
    accountDigit: '3',
    pixKey: 'empresa@email.com'
);

// Pagador
$address = new Address(
    street: 'Rua das Flores, 123',
    neighborhood: 'Centro',
    city: 'São Paulo',
    state: 'SP',
    zipCode: '01234-567'
);

$person = new PhysicalPerson(
    name: 'João da Silva',
    document: '123.456.789-00',
    address: $address
);

$payer = new Payer($person);

// Cobrança com juros e multa
$interest = new Interest('93', 5.00);
$fine = new Fine('02', 2.0);
$charge = new Charge(interest: $interest, fine: $fine);

// Request
$request = new BoletoRequestDTO(
    beneficiary: $beneficiary,
    payer: $payer,
    ourNumber: '00000001',
    yourNumber: 'DOC001',
    amount: 150.00,
    issueDate: new DateTimeImmutable(),
    dueDate: new DateTimeImmutable('+30 days'),
    charge: $charge,
    processStep: ProcessStep::REGISTRATION
);

// Gerar boleto
try {
    $response = $boletoService->createBoleto($request);
    echo "Boleto gerado: {$response->id}\n";
} catch (BoletoException $e) {
    echo "Erro: {$e->getDetailedMessage()}\n";
}
```

Esta referência cobre todos os componentes principais da biblioteca. Para exemplos mais específicos, consulte a pasta `examples/` do projeto.