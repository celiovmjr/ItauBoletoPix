# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Versionamento Semântico](https://semver.org/lang/pt-BR/).

## [Não Lançado]

### Planejado
- [ ] Suporte a múltiplos beneficiários
- [ ] Cache de tokens com Redis
- [ ] Integração com filas (RabbitMQ, SQS)
- [ ] Dashboard de monitoramento
- [ ] Relatórios de cobrança
- [ ] API REST wrapper
- [ ] Suporte a outros bancos

## [1.0.0] - 2025-01-22

### Adicionado
- ✅ **Geração de Boletos PIX** - Criação de boletos com PIX integrado
- ✅ **Gateway Itaú** - Comunicação completa com API do Itaú
- ✅ **Autenticação OAuth2** - Sistema de tokens com cache automático
- ✅ **Validações Robustas** - CPF, CNPJ, datas e valores
- ✅ **Sistema de Webhooks** - Processamento de notificações em tempo real
- ✅ **Tratamento de Erros** - Exceções específicas e contextualizadas
- ✅ **Utilitários** - Helpers para datas, documentos e formatação
- ✅ **Suporte a Sandbox** - Ambiente de desenvolvimento e testes
- ✅ **Documentação Completa** - Guias, exemplos e referência da API

### Recursos Principais

#### Modelos de Domínio
- `Boleto` - Modelo principal com validações
- `Beneficiary` - Dados do recebedor (sua empresa)
- `Payer` - Dados do pagador (cliente)
- `PhysicalPerson` - Pessoa física (CPF)
- `LegalPerson` - Pessoa jurídica (CNPJ)
- `Address` - Endereço completo
- `Charge` - Configurações de cobrança (juros, multa, desconto)

#### DTOs (Data Transfer Objects)
- `BoletoRequestDTO` - Request de criação de boleto
- `BoletoResponseDTO` - Response com dados do boleto gerado
- `WebhookPayload` - Payload estruturado de webhooks

#### Enums
- `ProcessStep` - Simulação vs Efetivação
- `PersonType` - Pessoa física vs jurídica
- `WalletCode` - Códigos de carteira do Itaú
- `BoletoType` - Tipos de boleto disponíveis

#### Serviços
- `BoletoGenerationService` - Serviço principal de geração
- `ItauBoletoGateway` - Gateway de comunicação com API
- `ItauWebhookHandler` - Processador de webhooks

#### Utilitários
- `DocumentValidator` - Validação de CPF/CNPJ
- `MoneyFormatter` - Formatação de valores para API Itaú
- `DateHelper` - Manipulação de datas e dias úteis
- `UuidHelper` - Geração de UUIDs

#### Exceções
- `BoletoException` - Exceção base com contexto
- `GatewayException` - Erros de comunicação com API
- `ValidationException` - Erros de validação de dados
- `AuthenticationException` - Erros de autenticação
- `WebhookException` - Erros de processamento de webhooks

### Funcionalidades Avançadas

#### Configurações de Cobrança
- **Juros** - Valor por dia ou percentual
- **Multa** - Percentual após vencimento
- **Desconto** - Valor ou percentual até data específica
- **Mensagens** - Instruções personalizadas no boleto

#### Sistema de Webhooks
- **Eventos Suportados:**
  - `BAIXA_EFETIVA` - Pagamento confirmado
  - `BAIXA_OPERACIONAL` - Cancelamento
  - `VENCIMENTO` - Boleto vencido
  - `PROTESTO` - Protesto realizado
- **Validação de Assinatura** - Segurança com HMAC SHA256
- **Sistema de Listeners** - Callbacks para eventos específicos
- **Idempotência** - Prevenção de processamento duplicado

#### Validações Automáticas
- **CPF/CNPJ** - Algoritmo completo de validação
- **Datas** - Vencimento posterior à emissão
- **Valores** - Maior que zero e formatação correta
- **Campos Obrigatórios** - Verificação de dados essenciais
- **Limites** - Nosso número (8 dígitos), valores máximos

### Exemplos Incluídos

#### Básicos
- `basic-usage.php` - Uso mais simples possível
- `complete-usage.php` - Exemplo com todas as funcionalidades
- `boleto.php` - Interface web para visualização

#### Casos de Uso
- **E-commerce** - Integração com lojas virtuais
- **SaaS/Assinatura** - Cobrança recorrente mensal
- **Educacional** - Mensalidades escolares
- **Geração em Lote** - Múltiplos boletos automatizados

### Documentação

#### Guias Completos
- `README.md` - Visão geral e início rápido
- `docs/INSTALLATION.md` - Guia detalhado de instalação
- `docs/API_REFERENCE.md` - Referência completa da API
- `docs/WEBHOOKS.md` - Guia completo de webhooks
- `docs/EXAMPLES.md` - Exemplos práticos detalhados
- `docs/TROUBLESHOOTING.md` - Solução de problemas

#### Recursos de Desenvolvimento
- **Testes Automatizados** - PHPUnit configurado
- **Análise Estática** - PHPStan nível máximo
- **Code Style** - PHP-CS-Fixer com PSR-12
- **Autoload PSR-4** - Compatível com Composer

### Requisitos Técnicos
- **PHP** 8.3+ (aproveita recursos modernos)
- **Extensões** curl, json
- **Certificados** Itaú (.crt e .key)
- **Credenciais** Client ID e Secret da API

### Ambientes Suportados
- **Sandbox** - Desenvolvimento e testes
- **Produção** - Ambiente real do Itaú
- **Configuração Flexível** - Via variáveis de ambiente

### Segurança
- ✅ Validação de certificados SSL
- ✅ Verificação de assinatura de webhooks
- ✅ Sanitização de dados de entrada
- ✅ Não exposição de credenciais em logs
- ✅ Timeouts configuráveis para requests

### Performance
- ✅ Cache automático de tokens OAuth2
- ✅ Reutilização de conexões HTTP
- ✅ Validações otimizadas
- ✅ Lazy loading de recursos
- ✅ Logs estruturados para monitoramento

### Compatibilidade
- ✅ **Frameworks** - Laravel, Symfony, CodeIgniter
- ✅ **Sistemas** - Linux, Windows, macOS
- ✅ **Servidores** - Apache, Nginx, IIS
- ✅ **Containers** - Docker, Kubernetes

## Roadmap Futuro

### v1.1.0 (Planejado para Q2 2025)
- [ ] Consulta de boletos existentes
- [ ] Cancelamento de boletos
- [ ] Listagem com filtros
- [ ] Cache com Redis/Memcached
- [ ] Métricas e monitoramento

### v1.2.0 (Planejado para Q3 2025)
- [ ] Suporte a múltiplos beneficiários
- [ ] Integração com filas (RabbitMQ, SQS)
- [ ] Dashboard web de administração
- [ ] Relatórios de cobrança
- [ ] Exportação de dados

### v2.0.0 (Planejado para Q4 2025)
- [ ] Suporte a outros bancos (Bradesco, Santander)
- [ ] API REST wrapper
- [ ] Interface gráfica completa
- [ ] Integração com ERPs
- [ ] Módulos para CMS (WordPress, Drupal)

## Contribuições

Este projeto foi desenvolvido com foco na qualidade e facilidade de uso. Contribuições são bem-vindas através de:

- 🐛 **Issues** - Reportar bugs ou sugerir melhorias
- 🔧 **Pull Requests** - Implementar novas funcionalidades
- 📖 **Documentação** - Melhorar guias e exemplos
- 🧪 **Testes** - Adicionar cobertura de testes
- 💡 **Ideias** - Sugerir novos recursos

## Licença

Este projeto está licenciado sob a [Licença MIT](LICENSE) - veja o arquivo LICENSE para detalhes.

## Agradecimentos

- **Itaú** - Pela API robusta e documentação
- **Comunidade PHP** - Pelas bibliotecas e ferramentas
- **Contribuidores** - Por feedback e melhorias
- **Usuários** - Por confiarem na biblioteca

---

**Nota:** Esta é a versão inicial (1.0.0) da biblioteca. Futuras versões manterão compatibilidade com a API atual, seguindo o versionamento semântico.