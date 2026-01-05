# Guia de Contribuição - Itaú Boleto PIX

Obrigado por considerar contribuir com o projeto Itaú Boleto PIX! 🎉

Este guia fornece informações sobre como contribuir de forma efetiva para o projeto.

## 📋 Índice

- [Código de Conduta](#código-de-conduta)
- [Como Contribuir](#como-contribuir)
- [Configuração do Ambiente](#configuração-do-ambiente)
- [Padrões de Desenvolvimento](#padrões-de-desenvolvimento)
- [Processo de Pull Request](#processo-de-pull-request)
- [Reportar Bugs](#reportar-bugs)
- [Sugerir Melhorias](#sugerir-melhorias)
- [Documentação](#documentação)

## 🤝 Código de Conduta

Este projeto adere ao [Contributor Covenant](https://www.contributor-covenant.org/). Ao participar, você deve seguir este código de conduta.

### Nossos Compromissos

- **Respeito** - Tratar todos com respeito e dignidade
- **Inclusão** - Criar um ambiente acolhedor para todos
- **Colaboração** - Trabalhar juntos de forma construtiva
- **Profissionalismo** - Manter discussões focadas e produtivas

## 🚀 Como Contribuir

Existem várias maneiras de contribuir:

### 🐛 Reportar Bugs
- Use o template de issue para bugs
- Forneça informações detalhadas
- Inclua passos para reproduzir
- Adicione logs e screenshots quando relevante

### 💡 Sugerir Funcionalidades
- Use o template de issue para features
- Descreva o problema que resolve
- Proponha uma solução
- Considere alternativas

### 🔧 Contribuir com Código
- Implemente correções de bugs
- Adicione novas funcionalidades
- Melhore performance
- Refatore código existente

### 📖 Melhorar Documentação
- Corrija erros de digitação
- Adicione exemplos
- Melhore explicações
- Traduza conteúdo

### 🧪 Adicionar Testes
- Aumente cobertura de testes
- Adicione testes de integração
- Melhore testes existentes
- Adicione testes de performance

## ⚙️ Configuração do Ambiente

### Pré-requisitos

- PHP 8.3+
- Composer
- Git
- Editor com suporte a PHP (VS Code, PhpStorm)

### Setup Inicial

1. **Fork o repositório**
```bash
# No GitHub, clique em "Fork"
```

2. **Clone seu fork**
```bash
git clone https://github.com/SEU_USUARIO/itau-boleto-pix.git
cd itau-boleto-pix
```

3. **Instale dependências**
```bash
composer install
```

4. **Configure ambiente**
```bash
cp .env.example .env
# Edite .env com suas credenciais de teste
```

5. **Configure Git**
```bash
git remote add upstream https://github.com/zukpay/itau-boleto-pix.git
git config user.name "Seu Nome"
git config user.email "seu@email.com"
```

### Ferramentas de Desenvolvimento

#### PHPStan (Análise Estática)
```bash
composer stan
```

#### PHP-CS-Fixer (Code Style)
```bash
composer cs
```

#### PHPUnit (Testes)
```bash
composer test
```

#### Executar Todos
```bash
composer check-all
```

## 📏 Padrões de Desenvolvimento

### Estilo de Código

Seguimos o **PSR-12** com algumas extensões:

```php
<?php

declare(strict_types=1);

namespace ItauBoletoPix\Models;

/**
 * Documentação da classe
 */
class ExampleClass
{
    private string $property;
    
    public function __construct(
        private string $param1,
        private int $param2
    ) {
        $this->validate();
    }
    
    public function exampleMethod(string $param): string
    {
        if (empty($param)) {
            throw new InvalidArgumentException('Parâmetro não pode ser vazio');
        }
        
        return $this->processParam($param);
    }
    
    private function processParam(string $param): string
    {
        return strtoupper($param);
    }
}
```

### Convenções de Nomenclatura

#### Classes
```php
// ✅ Bom
class BoletoGenerationService
class PhysicalPerson
class DocumentValidator

// ❌ Ruim
class boletoService
class physical_person
class docValidator
```

#### Métodos
```php
// ✅ Bom
public function createBoleto(): BoletoResponseDTO
public function validateDocument(): bool
public function getOurNumber(): string

// ❌ Ruim
public function create_boleto()
public function ValidateDocument()
public function get_our_number()
```

#### Variáveis
```php
// ✅ Bom
$boletoRequest = new BoletoRequestDTO();
$ourNumber = '00000001';
$isValid = true;

// ❌ Ruim
$boleto_request = new BoletoRequestDTO();
$OurNumber = '00000001';
$is_valid = true;
```

### Documentação de Código

#### DocBlocks Obrigatórios
```php
/**
 * Cria um novo boleto PIX
 *
 * @param  BoletoRequestDTO                          $request Dados do boleto
 * @return BoletoResponseDTO                         Boleto gerado
 * @throws \ItauBoletoPix\Exceptions\BoletoException
 */
public function createBoleto(BoletoRequestDTO $request): BoletoResponseDTO
{
    // Implementação
}
```

#### Comentários Inline
```php
// ✅ Bom - Explica o "porquê"
// Itaú requer valores com 15 dígitos representando centavos
$formattedAmount = str_pad((string)$cents, 15, '0', STR_PAD_LEFT);

// ❌ Ruim - Explica o "o quê" (óbvio)
// Converte para string e adiciona zeros à esquerda
$formattedAmount = str_pad((string)$cents, 15, '0', STR_PAD_LEFT);
```

### Tratamento de Erros

#### Exceções Específicas
```php
// ✅ Bom
if ($amount <= 0) {
    throw new ValidationException(
        'Valor deve ser maior que zero',
        ['amount' => $amount]
    );
}

// ❌ Ruim
if ($amount <= 0) {
    throw new Exception('Valor inválido');
}
```

#### Contexto em Exceções
```php
try {
    $response = $this->gateway->sendRequest($payload);
} catch (Exception $e) {
    throw new GatewayException(
        "Falha na comunicação: {$e->getMessage()}",
        $e->getCode(),
        null,
        ['payload' => $payload, 'url' => $url],
        $e
    );
}
```

### Testes

#### Estrutura de Testes
```php
<?php

declare(strict_types=1);

namespace ItauBoletoPix\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use ItauBoletoPix\Models\PhysicalPerson;

class PhysicalPersonTest extends TestCase
{
    public function testCanCreatePhysicalPerson(): void
    {
        // Arrange
        $name = 'João da Silva';
        $document = '12345678900';
        $address = $this->createMockAddress();
        
        // Act
        $person = new PhysicalPerson($name, $document, $address);
        
        // Assert
        $this->assertEquals($name, $person->getName());
        $this->assertEquals($document, $person->getDocument());
        $this->assertEquals('F', $person->getDocumentType());
    }
    
    public function testThrowsExceptionForInvalidDocument(): void
    {
        // Arrange
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('CPF inválido');
        
        // Act & Assert
        new PhysicalPerson('João', '00000000000', $this->createMockAddress());
    }
    
    private function createMockAddress(): Address
    {
        return new Address(
            street: 'Rua Teste, 123',
            neighborhood: 'Centro',
            city: 'São Paulo',
            state: 'SP',
            zipCode: '01234-567'
        );
    }
}
```

#### Cobertura de Testes
- **Mínimo**: 80% de cobertura
- **Ideal**: 90%+ de cobertura
- **Obrigatório**: Testes para casos críticos

#### Tipos de Testes
```bash
# Testes unitários
tests/Unit/

# Testes de integração
tests/Integration/

# Testes funcionais
tests/Feature/
```

## 🔄 Processo de Pull Request

### 1. Preparação

```bash
# Sincronizar com upstream
git fetch upstream
git checkout main
git merge upstream/main

# Criar branch para feature
git checkout -b feature/nome-da-feature
```

### 2. Desenvolvimento

```bash
# Fazer commits pequenos e focados
git add .
git commit -m "feat: adicionar validação de CPF"

# Seguir conventional commits
git commit -m "fix: corrigir formatação de valores"
git commit -m "docs: atualizar exemplos de uso"
git commit -m "test: adicionar testes para DocumentValidator"
```

### 3. Testes

```bash
# Executar todos os testes
composer test

# Verificar code style
composer cs

# Análise estática
composer stan

# Verificar tudo
composer check-all
```

### 4. Submissão

```bash
# Push da branch
git push origin feature/nome-da-feature

# Criar PR no GitHub
```

### 5. Template de PR

```markdown
## Descrição
Breve descrição das mudanças realizadas.

## Tipo de Mudança
- [ ] Bug fix (mudança que corrige um problema)
- [ ] Nova funcionalidade (mudança que adiciona funcionalidade)
- [ ] Breaking change (mudança que quebra compatibilidade)
- [ ] Documentação (mudança apenas na documentação)

## Como Testar
1. Passo 1
2. Passo 2
3. Resultado esperado

## Checklist
- [ ] Código segue os padrões do projeto
- [ ] Testes foram adicionados/atualizados
- [ ] Documentação foi atualizada
- [ ] Todas as verificações passaram
```

### Critérios de Aprovação

- ✅ Todos os testes passando
- ✅ Code style correto
- ✅ Análise estática sem erros
- ✅ Documentação atualizada
- ✅ Review aprovado por mantenedor

## 🐛 Reportar Bugs

### Template de Bug Report

```markdown
**Descrição do Bug**
Descrição clara e concisa do problema.

**Passos para Reproduzir**
1. Vá para '...'
2. Clique em '....'
3. Role até '....'
4. Veja o erro

**Comportamento Esperado**
Descrição do que deveria acontecer.

**Comportamento Atual**
Descrição do que está acontecendo.

**Screenshots**
Se aplicável, adicione screenshots.

**Ambiente:**
- OS: [ex: Ubuntu 20.04]
- PHP: [ex: 8.3.1]
- Versão da Lib: [ex: 1.0.0]

**Contexto Adicional**
Qualquer outra informação relevante.

**Logs**
```
Cole aqui os logs relevantes
```
```

### Informações Importantes

- **Seja específico** - Quanto mais detalhes, melhor
- **Inclua código** - Mostre como reproduzir
- **Adicione logs** - Erros e stack traces
- **Teste primeiro** - Verifique se não é problema de configuração

## 💡 Sugerir Melhorias

### Template de Feature Request

```markdown
**Problema Relacionado**
Descrição clara do problema que esta feature resolveria.

**Solução Proposta**
Descrição clara da solução desejada.

**Alternativas Consideradas**
Outras soluções que você considerou.

**Contexto Adicional**
Qualquer outra informação relevante.

**Exemplo de Uso**
```php
// Como você gostaria de usar a feature
$service->newFeature($params);
```
```

### Critérios para Novas Features

- **Utilidade** - Resolve problema real dos usuários
- **Compatibilidade** - Não quebra API existente
- **Manutenibilidade** - Código limpo e testável
- **Documentação** - Bem documentada com exemplos

## 📖 Documentação

### Tipos de Documentação

#### README.md
- Visão geral do projeto
- Instalação e configuração
- Exemplos básicos
- Links para documentação detalhada

#### docs/
- Guias detalhados
- Referência da API
- Tutoriais passo a passo
- Solução de problemas

#### Código
- DocBlocks em classes e métodos
- Comentários inline quando necessário
- Exemplos em comentários

### Padrões de Documentação

#### Linguagem
- **Português** para documentação geral
- **Inglês** para código e comentários técnicos
- **Tom amigável** mas profissional

#### Estrutura
```markdown
# Título Principal

## Seção

### Subseção

Texto explicativo com **negrito** e *itálico*.

```php
// Exemplo de código
$example = new Example();
```

- Lista de itens
- Item 2

1. Lista numerada
2. Item 2
```

#### Exemplos de Código
- **Completos** - Funcionam sem modificação
- **Comentados** - Explicam partes importantes
- **Realistas** - Casos de uso reais

## 🏆 Reconhecimento

### Contribuidores

Todos os contribuidores são reconhecidos:

- **README.md** - Lista de contribuidores
- **CHANGELOG.md** - Créditos por versão
- **GitHub** - Histórico de commits

### Tipos de Contribuição

- 🐛 **Bug Reports** - Encontrar e reportar problemas
- 💡 **Feature Requests** - Sugerir melhorias
- 🔧 **Code** - Implementar funcionalidades
- 📖 **Documentation** - Melhorar documentação
- 🧪 **Testing** - Adicionar testes
- 🎨 **Design** - Melhorar UX/UI
- 🌍 **Translation** - Traduzir conteúdo

## 📞 Suporte

### Canais de Comunicação

- **GitHub Issues** - Bugs e feature requests
- **GitHub Discussions** - Perguntas e discussões
- **Email** - seu@email.com (para questões privadas)

### Tempo de Resposta

- **Issues críticos** - 24 horas
- **Bugs** - 48 horas
- **Feature requests** - 1 semana
- **Pull requests** - 72 horas

## 🎯 Roadmap

### Próximas Versões

Veja [CHANGELOG.md](CHANGELOG.md) para roadmap detalhado.

### Como Influenciar

- **Vote** em issues existentes (👍)
- **Comente** com casos de uso
- **Implemente** features desejadas
- **Patrocine** desenvolvimento

## 📜 Licença

Ao contribuir, você concorda que suas contribuições serão licenciadas sob a [Licença MIT](LICENSE).

---

**Obrigado por contribuir!** 🙏

Sua ajuda torna este projeto melhor para toda a comunidade PHP.