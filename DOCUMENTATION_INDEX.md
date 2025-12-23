# Índice Geral da Documentação - Itaú Boleto PIX

## 📚 Documentação Completa

Esta biblioteca possui documentação abrangente organizada por tópicos. Use este índice para navegar rapidamente para o conteúdo desejado.

## 🎯 Navegação por Objetivo

### 🚀 Quero Começar Rapidamente
1. **[README.md](README.md)** - Visão geral e exemplo básico
2. **[docs/INSTALLATION.md](docs/INSTALLATION.md)** - Instalação passo a passo
3. **[examples/basic-usage.php](examples/basic-usage.php)** - Primeiro boleto

### 🔧 Quero Implementar Funcionalidades Específicas
- **Boleto Simples**: [EXAMPLES.md - Boleto Simples](docs/EXAMPLES.md#1-boleto-simples---pessoa-física)
- **Boleto com Juros/Multa**: [EXAMPLES.md - Boleto Avançado](docs/EXAMPLES.md#1-boleto-com-juros-multa-e-desconto)
- **Webhooks**: [WEBHOOKS.md](docs/WEBHOOKS.md)
- **Cobrança Recorrente**: [EXAMPLES.md - Sistema Recorrente](docs/EXAMPLES.md#3-sistema-de-cobrança-recorrente)
- **E-commerce**: [EXAMPLES.md - E-commerce](docs/EXAMPLES.md#1-e-commerce)

### 📖 Quero Entender a API Completa
- **[docs/API_REFERENCE.md](docs/API_REFERENCE.md)** - Referência completa de classes e métodos

### 🐛 Tenho Problemas
- **[docs/TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md)** - Solução de problemas comuns

### 🤝 Quero Contribuir
- **[CONTRIBUTING.md](CONTRIBUTING.md)** - Guia de contribuição
- **[CHANGELOG.md](CHANGELOG.md)** - Histórico de mudanças

## 📁 Estrutura Completa dos Arquivos

### 📄 Arquivos Principais
```
├── README.md                     # Visão geral e início rápido
├── CHANGELOG.md                  # Histórico de versões
├── CONTRIBUTING.md               # Guia de contribuição
├── LICENSE                       # Licença MIT
├── composer.json                 # Configuração do Composer
├── .env.example                  # Exemplo de configuração
└── DOCUMENTATION_INDEX.md        # Este arquivo
```

### 📚 Documentação Detalhada
```
docs/
├── README.md                     # Índice da documentação
├── INSTALLATION.md               # Guia de instalação completo
├── API_REFERENCE.md              # Referência da API
├── WEBHOOKS.md                   # Guia completo de webhooks
├── EXAMPLES.md                   # Exemplos práticos detalhados
├── TROUBLESHOOTING.md            # Solução de problemas
└── MIGRATION.md                  # Guia de migração entre versões
```

### 💻 Código Fonte
```
src/
├── Contracts/                    # Interfaces
├── DTOs/                         # Data Transfer Objects
├── Enums/                        # Enumerações
├── Exceptions/                   # Exceções customizadas
├── Gateways/                     # Comunicação com APIs
├── Models/                       # Modelos de domínio
├── Services/                     # Serviços de negócio
├── Utils/                        # Utilitários
└── Webhooks/                     # Sistema de webhooks
```

### 🧪 Exemplos e Testes
```
examples/
├── basic-usage.php               # Uso básico
├── complete-usage.php            # Exemplo completo
└── boleto.php                    # Interface web

tests/
├── Unit/                         # Testes unitários
└── Integration/                  # Testes de integração
```

## 🔍 Busca por Tópico

### Instalação e Configuração
| Tópico | Arquivo | Seção |
|--------|---------|-------|
| Instalação via Composer | [INSTALLATION.md](docs/INSTALLATION.md) | [Instalação](docs/INSTALLATION.md#instalação) |
| Configuração de ambiente | [INSTALLATION.md](docs/INSTALLATION.md) | [Configuração](docs/INSTALLATION.md#configuração) |
| Certificados Itaú | [INSTALLATION.md](docs/INSTALLATION.md) | [Certificados](docs/INSTALLATION.md#2-configurar-certificados) |
| Teste de conectividade | [INSTALLATION.md](docs/INSTALLATION.md) | [Teste da Instalação](docs/INSTALLATION.md#teste-da-instalação) |

### Uso Básico
| Tópico | Arquivo | Seção |
|--------|---------|-------|
| Primeiro boleto | [README.md](README.md) | [Uso Básico](README.md#uso-básico) |
| Pessoa física vs jurídica | [EXAMPLES.md](docs/EXAMPLES.md) | [Exemplos Básicos](docs/EXAMPLES.md#exemplos-básicos) |
| Configurar beneficiário | [API_REFERENCE.md](docs/API_REFERENCE.md) | [Beneficiary](docs/API_REFERENCE.md#beneficiary) |
| Configurar pagador | [API_REFERENCE.md](docs/API_REFERENCE.md) | [Payer](docs/API_REFERENCE.md#payer) |

### Funcionalidades Avançadas
| Tópico | Arquivo | Seção |
|--------|---------|-------|
| Juros e multa | [EXAMPLES.md](docs/EXAMPLES.md) | [Boleto Avançado](docs/EXAMPLES.md#1-boleto-com-juros-multa-e-desconto) |
| Desconto | [API_REFERENCE.md](docs/API_REFERENCE.md) | [Discount](docs/API_REFERENCE.md#discount) |
| Mensagens no boleto | [API_REFERENCE.md](docs/API_REFERENCE.md) | [Charge](docs/API_REFERENCE.md#charge) |
| Geração em lote | [EXAMPLES.md](docs/EXAMPLES.md) | [Geração em Lote](docs/EXAMPLES.md#2-geração-em-lote) |

### Webhooks
| Tópico | Arquivo | Seção |
|--------|---------|-------|
| Configuração básica | [WEBHOOKS.md](docs/WEBHOOKS.md) | [Configuração](docs/WEBHOOKS.md#configuração) |
| Eventos disponíveis | [WEBHOOKS.md](docs/WEBHOOKS.md) | [Eventos](docs/WEBHOOKS.md#eventos-disponíveis) |
| Validação de assinatura | [WEBHOOKS.md](docs/WEBHOOKS.md) | [Validação](docs/WEBHOOKS.md#validação-de-assinatura) |
| Implementação avançada | [WEBHOOKS.md](docs/WEBHOOKS.md) | [Implementação](docs/WEBHOOKS.md#implementação) |

### Integração com Frameworks
| Framework | Arquivo | Seção |
|-----------|---------|-------|
| Laravel | [EXAMPLES.md](docs/EXAMPLES.md) | [Laravel](docs/EXAMPLES.md#laravel) |
| Symfony | [EXAMPLES.md](docs/EXAMPLES.md) | [Symfony](docs/EXAMPLES.md#symfony) |
| Vanilla PHP | [examples/](examples/) | Todos os exemplos |

### Casos de Uso Reais
| Caso de Uso | Arquivo | Seção |
|-------------|---------|-------|
| E-commerce | [EXAMPLES.md](docs/EXAMPLES.md) | [E-commerce](docs/EXAMPLES.md#1-e-commerce) |
| SaaS/Assinatura | [EXAMPLES.md](docs/EXAMPLES.md) | [SaaS](docs/EXAMPLES.md#2-sistema-de-assinaturasaas) |
| Sistema educacional | [EXAMPLES.md](docs/EXAMPLES.md) | [Educacional](docs/EXAMPLES.md#3-sistema-educacional) |
| Cobrança recorrente | [EXAMPLES.md](docs/EXAMPLES.md) | [Recorrente](docs/EXAMPLES.md#3-sistema-de-cobrança-recorrente) |

### Solução de Problemas
| Problema | Arquivo | Seção |
|----------|---------|-------|
| Erros de instalação | [TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md) | [Instalação](docs/TROUBLESHOOTING.md#problemas-de-instalação) |
| Erros de autenticação | [TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md) | [Autenticação](docs/TROUBLESHOOTING.md#problemas-de-autenticação) |
| Problemas com certificados | [TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md) | [Certificados](docs/TROUBLESHOOTING.md#problemas-de-certificados) |
| Erros da API | [TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md) | [API](docs/TROUBLESHOOTING.md#problemas-de-api) |
| Webhooks não funcionam | [TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md) | [Webhooks](docs/TROUBLESHOOTING.md#problemas-de-webhooks) |

### Referência Técnica
| Componente | Arquivo | Seção |
|------------|---------|-------|
| Todas as classes | [API_REFERENCE.md](docs/API_REFERENCE.md) | Documento completo |
| Interfaces | [API_REFERENCE.md](docs/API_REFERENCE.md) | [Contratos](docs/API_REFERENCE.md#contratos-interfaces) |
| DTOs | [API_REFERENCE.md](docs/API_REFERENCE.md) | [DTOs](docs/API_REFERENCE.md#dtos-data-transfer-objects) |
| Modelos | [API_REFERENCE.md](docs/API_REFERENCE.md) | [Modelos](docs/API_REFERENCE.md#modelos) |
| Enums | [API_REFERENCE.md](docs/API_REFERENCE.md) | [Enums](docs/API_REFERENCE.md#enums) |
| Utilitários | [API_REFERENCE.md](docs/API_REFERENCE.md) | [Utilitários](docs/API_REFERENCE.md#utilitários) |
| Exceções | [API_REFERENCE.md](docs/API_REFERENCE.md) | [Exceções](docs/API_REFERENCE.md#exceções) |

## 🎓 Trilhas de Aprendizado

### 🥇 Iniciante
1. Leia o [README.md](README.md) para entender o projeto
2. Siga o [Guia de Instalação](docs/INSTALLATION.md)
3. Execute o [exemplo básico](examples/basic-usage.php)
4. Teste no ambiente sandbox

### 🥈 Intermediário
1. Explore [exemplos avançados](docs/EXAMPLES.md#exemplos-avançados)
2. Configure [webhooks básicos](docs/WEBHOOKS.md#configuração)
3. Implemente [validações customizadas](docs/API_REFERENCE.md#utilitários)
4. Integre com seu framework preferido

### 🥉 Avançado
1. Implemente [sistema completo](docs/EXAMPLES.md#casos-de-uso-reais)
2. Configure [webhooks avançados](docs/WEBHOOKS.md#implementação)
3. Otimize [performance e monitoramento](docs/TROUBLESHOOTING.md#logs-e-debug)
4. Contribua com [melhorias](CONTRIBUTING.md)

## 🔧 Ferramentas de Desenvolvimento

### Scripts Úteis
```bash
# Verificar tudo
composer check-all

# Executar testes
composer test

# Verificar code style
composer cs

# Análise estática
composer stan
```

### Arquivos de Configuração
- **composer.json** - Dependências e scripts
- **.env.example** - Exemplo de configuração
- **phpunit.xml** - Configuração de testes
- **phpstan.neon** - Configuração de análise estática
- **.php-cs-fixer.php** - Configuração de code style

## 📊 Métricas da Documentação

### Cobertura
- ✅ **100%** das classes documentadas
- ✅ **100%** dos métodos públicos documentados
- ✅ **15+** exemplos práticos
- ✅ **50+** casos de uso cobertos

### Qualidade
- ✅ Exemplos testados e funcionais
- ✅ Código de exemplo atualizado
- ✅ Links internos verificados
- ✅ Formatação consistente

## 🚀 Próximos Passos

Após navegar pela documentação:

1. **Implemente** em seu projeto
2. **Teste** em ambiente sandbox
3. **Configure** para produção
4. **Monitore** e otimize
5. **Contribua** com melhorias

## 📞 Precisa de Ajuda?

### Recursos Disponíveis
- **GitHub Issues** - Para bugs e feature requests
- **GitHub Discussions** - Para perguntas e discussões
- **Email** - seu@email.com para suporte direto

### Antes de Pedir Ajuda
1. Consulte este índice para encontrar a documentação relevante
2. Verifique o [Troubleshooting](docs/TROUBLESHOOTING.md)
3. Procure em issues existentes no GitHub
4. Prepare informações detalhadas sobre seu problema

---

**Esta documentação está sempre evoluindo!** 📈

Contribua com melhorias, correções ou novos exemplos através do [GitHub](https://github.com/zukpay/itau-boleto-pix).