# Guia de Migração - Itaú Boleto PIX

Este guia ajuda na migração entre versões da biblioteca, garantindo que suas integrações continuem funcionando corretamente.

## 📋 Índice

- [Política de Versionamento](#política-de-versionamento)
- [Versões Suportadas](#versões-suportadas)
- [Migrações Disponíveis](#migrações-disponíveis)
- [Ferramentas de Migração](#ferramentas-de-migração)
- [Checklist de Migração](#checklist-de-migração)

## 📊 Política de Versionamento

Esta biblioteca segue o [Versionamento Semântico](https://semver.org/lang/pt-BR/):

### Formato: MAJOR.MINOR.PATCH

- **MAJOR** - Mudanças incompatíveis na API
- **MINOR** - Novas funcionalidades compatíveis
- **PATCH** - Correções de bugs compatíveis

### Exemplos

```
1.0.0 → 1.0.1  ✅ Seguro (apenas correções)
1.0.1 → 1.1.0  ✅ Seguro (novas funcionalidades)
1.1.0 → 2.0.0  ⚠️  Cuidado (mudanças incompatíveis)
```

## 🛡️ Versões Suportadas

| Versão | Status | Suporte até | Atualizações |
|--------|--------|-------------|--------------|
| 1.0.x | ✅ Ativa | 2026-01-22 | Bugs + Segurança |
| 1.1.x | 🔄 Planejada | TBD | Bugs + Segurança |
| 2.0.x | 📋 Roadmap | TBD | Bugs + Segurança |

### Política de Suporte

- **Versão Atual** - Suporte completo (bugs, segurança, features)
- **Versão Anterior** - Suporte limitado (bugs críticos, segurança)
- **Versões Antigas** - Apenas segurança crítica

## 🔄 Migrações Disponíveis

### Futuras Migrações (Planejadas)

#### v1.0.x → v1.1.x (Compatível)

**Novas Funcionalidades:**
- Consulta de boletos existentes
- Cancelamento de boletos
- Cache com Redis/Memcached
- Métricas integradas

**Mudanças:**
- Nenhuma breaking change
- Novos métodos opcionais
- Configurações adicionais

**Ação Necessária:**
```bash
# Atualização simples
composer update zukpay/itau-boleto-pix
```

#### v1.1.x → v2.0.x (Breaking Changes)

**Mudanças Incompatíveis:**
- Namespace reorganizado
- Alguns métodos renomeados
- Configuração simplificada
- Suporte a múltiplos bancos

**Ação Necessária:**
- Seguir guia de migração específico
- Atualizar código conforme breaking changes
- Testar extensivamente

## 🛠️ Ferramentas de Migração

### Script de Verificação de Compatibilidade

```php
<?php
// check-compatibility.php

require_once 'vendor/autoload.php';

class CompatibilityChecker
{
    private array $issues = [];
    
    public function checkVersion(string $currentVersion, string $targetVersion): array
    {
        $this->issues = [];
        
        // Verificar breaking changes conhecidos
        $this->checkBreakingChanges($currentVersion, $targetVersion);
        
        // Verificar dependências
        $this->checkDependencies();
        
        // Verificar configuração
        $this->checkConfiguration();
        
        return $this->issues;
    }
    
    private function checkBreakingChanges(string $current, string $target): void
    {
        $currentMajor = (int)explode('.', $current)[0];
        $targetMajor = (int)explode('.', $target)[0];
        
        if ($targetMajor > $currentMajor) {
            $this->issues[] = [
                'type' => 'breaking_change',
                'severity' => 'high',
                'message' => "Migração de v{$current} para v{$target} contém breaking changes",
                'action' => 'Revisar guia de migração específico'
            ];
        }
    }
    
    private function checkDependencies(): void
    {
        // Verificar PHP
        if (version_compare(PHP_VERSION, '8.3.0', '<')) {
            $this->issues[] = [
                'type' => 'dependency',
                'severity' => 'high',
                'message' => 'PHP 8.3+ é obrigatório',
                'action' => 'Atualizar PHP para versão 8.3 ou superior'
            ];
        }
        
        // Verificar extensões
        $requiredExtensions = ['curl', 'json'];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->issues[] = [
                    'type' => 'dependency',
                    'severity' => 'high',
                    'message' => "Extensão {$ext} não encontrada",
                    'action' => "Instalar extensão php-{$ext}"
                ];
            }
        }
    }
    
    private function checkConfiguration(): void
    {
        $requiredEnvVars = [
            'ITAU_CLIENT_ID',
            'ITAU_CLIENT_SECRET',
            'ITAU_CERTIFICATE_PATH',
            'ITAU_CERTIFICATE_KEY_PATH'
        ];
        
        foreach ($requiredEnvVars as $var) {
            if (empty($_ENV[$var])) {
                $this->issues[] = [
                    'type' => 'configuration',
                    'severity' => 'medium',
                    'message' => "Variável {$var} não configurada",
                    'action' => "Configurar {$var} no arquivo .env"
                ];
            }
        }
    }
}

// Uso
$checker = new CompatibilityChecker();
$issues = $checker->checkVersion('1.0.0', '1.1.0');

if (empty($issues)) {
    echo "✅ Nenhum problema de compatibilidade encontrado!\n";
} else {
    echo "⚠️ Problemas encontrados:\n\n";
    foreach ($issues as $issue) {
        $icon = $issue['severity'] === 'high' ? '🔴' : '🟡';
        echo "{$icon} {$issue['type']}: {$issue['message']}\n";
        echo "   Ação: {$issue['action']}\n\n";
    }
}
```

### Script de Backup

```php
<?php
// backup-before-migration.php

class MigrationBackup
{
    private string $backupDir;
    
    public function __construct(string $backupDir = 'backups')
    {
        $this->backupDir = $backupDir;
        $this->ensureBackupDir();
    }
    
    public function createBackup(): string
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupPath = "{$this->backupDir}/backup_{$timestamp}";
        
        mkdir($backupPath, 0755, true);
        
        // Backup do código
        $this->backupCode($backupPath);
        
        // Backup da configuração
        $this->backupConfig($backupPath);
        
        // Backup do banco de dados (se aplicável)
        $this->backupDatabase($backupPath);
        
        echo "✅ Backup criado em: {$backupPath}\n";
        return $backupPath;
    }
    
    private function backupCode(string $backupPath): void
    {
        // Copiar arquivos importantes
        $filesToBackup = [
            'composer.json',
            'composer.lock',
            '.env',
            'src/',
            'config/'
        ];
        
        foreach ($filesToBackup as $file) {
            if (file_exists($file)) {
                $this->copyRecursive($file, "{$backupPath}/{$file}");
            }
        }
    }
    
    private function backupConfig(string $backupPath): void
    {
        $config = [
            'php_version' => PHP_VERSION,
            'extensions' => get_loaded_extensions(),
            'env_vars' => array_keys($_ENV),
            'timestamp' => date('c')
        ];
        
        file_put_contents(
            "{$backupPath}/system_info.json",
            json_encode($config, JSON_PRETTY_PRINT)
        );
    }
    
    private function backupDatabase(string $backupPath): void
    {
        // Implementar backup específico do seu banco
        // Exemplo para MySQL:
        /*
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s/database_backup.sql',
            $_ENV['DB_HOST'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
            $_ENV['DB_NAME'],
            $backupPath
        );
        exec($command);
        */
    }
    
    private function copyRecursive(string $src, string $dst): void
    {
        if (is_dir($src)) {
            mkdir($dst, 0755, true);
            $files = scandir($src);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $this->copyRecursive("{$src}/{$file}", "{$dst}/{$file}");
                }
            }
        } else {
            copy($src, $dst);
        }
    }
    
    private function ensureBackupDir(): void
    {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
}

// Uso
$backup = new MigrationBackup();
$backupPath = $backup->createBackup();
```

## ✅ Checklist de Migração

### Antes da Migração

- [ ] **Backup Completo**
  - [ ] Código fonte
  - [ ] Banco de dados
  - [ ] Configurações
  - [ ] Certificados

- [ ] **Verificação de Compatibilidade**
  - [ ] Executar script de verificação
  - [ ] Revisar changelog da nova versão
  - [ ] Identificar breaking changes
  - [ ] Verificar dependências

- [ ] **Ambiente de Teste**
  - [ ] Configurar ambiente isolado
  - [ ] Replicar configuração de produção
  - [ ] Preparar dados de teste

### Durante a Migração

- [ ] **Atualização Gradual**
  - [ ] Atualizar dependências primeiro
  - [ ] Atualizar biblioteca principal
  - [ ] Executar testes automatizados
  - [ ] Verificar funcionalidades críticas

- [ ] **Testes Específicos**
  - [ ] Geração de boletos
  - [ ] Processamento de webhooks
  - [ ] Validações de dados
  - [ ] Tratamento de erros

### Após a Migração

- [ ] **Validação Completa**
  - [ ] Todos os testes passando
  - [ ] Funcionalidades críticas operacionais
  - [ ] Logs sem erros
  - [ ] Performance mantida

- [ ] **Monitoramento**
  - [ ] Configurar alertas
  - [ ] Monitorar métricas
  - [ ] Acompanhar logs
  - [ ] Validar com usuários

- [ ] **Documentação**
  - [ ] Atualizar documentação interna
  - [ ] Registrar mudanças realizadas
  - [ ] Compartilhar com equipe

## 🚨 Rollback de Emergência

### Quando Fazer Rollback

- Erros críticos em produção
- Performance degradada significativamente
- Funcionalidades essenciais não funcionam
- Problemas de segurança identificados

### Processo de Rollback

```bash
# 1. Parar aplicação
sudo systemctl stop apache2  # ou nginx

# 2. Restaurar código anterior
cp -r backups/backup_YYYY-MM-DD_HH-mm-ss/* ./

# 3. Restaurar dependências
composer install --no-dev --optimize-autoloader

# 4. Restaurar banco de dados (se necessário)
mysql -u user -p database < backups/database_backup.sql

# 5. Reiniciar aplicação
sudo systemctl start apache2

# 6. Verificar funcionamento
curl -f http://localhost/health-check
```

### Script de Rollback Automatizado

```php
<?php
// rollback.php

class EmergencyRollback
{
    private string $backupPath;
    
    public function __construct(string $backupPath)
    {
        $this->backupPath = $backupPath;
    }
    
    public function execute(): bool
    {
        try {
            echo "🚨 Iniciando rollback de emergência...\n";
            
            // Parar serviços
            $this->stopServices();
            
            // Restaurar código
            $this->restoreCode();
            
            // Restaurar dependências
            $this->restoreDependencies();
            
            // Restaurar banco (se necessário)
            $this->restoreDatabase();
            
            // Reiniciar serviços
            $this->startServices();
            
            // Verificar saúde
            $this->healthCheck();
            
            echo "✅ Rollback concluído com sucesso!\n";
            return true;
            
        } catch (Exception $e) {
            echo "❌ Erro no rollback: {$e->getMessage()}\n";
            return false;
        }
    }
    
    private function stopServices(): void
    {
        exec('sudo systemctl stop apache2');
        echo "🛑 Serviços parados\n";
    }
    
    private function restoreCode(): void
    {
        exec("cp -r {$this->backupPath}/* ./");
        echo "📁 Código restaurado\n";
    }
    
    private function restoreDependencies(): void
    {
        exec('composer install --no-dev --optimize-autoloader');
        echo "📦 Dependências restauradas\n";
    }
    
    private function restoreDatabase(): void
    {
        $dbBackup = "{$this->backupPath}/database_backup.sql";
        if (file_exists($dbBackup)) {
            $command = sprintf(
                'mysql -u%s -p%s %s < %s',
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                $_ENV['DB_NAME'],
                $dbBackup
            );
            exec($command);
            echo "🗄️ Banco de dados restaurado\n";
        }
    }
    
    private function startServices(): void
    {
        exec('sudo systemctl start apache2');
        echo "▶️ Serviços reiniciados\n";
    }
    
    private function healthCheck(): void
    {
        sleep(5); // Aguardar inicialização
        
        $response = file_get_contents('http://localhost/health-check');
        if ($response === false) {
            throw new Exception('Health check falhou');
        }
        
        echo "💚 Health check OK\n";
    }
}

// Uso
if ($argc < 2) {
    echo "Uso: php rollback.php <caminho-do-backup>\n";
    exit(1);
}

$rollback = new EmergencyRollback($argv[1]);
$success = $rollback->execute();

exit($success ? 0 : 1);
```

## 📞 Suporte para Migração

### Recursos Disponíveis

- **Documentação** - Guias detalhados para cada versão
- **Scripts** - Ferramentas automatizadas de migração
- **Suporte** - Ajuda da comunidade e mantenedores
- **Testes** - Suítes de teste para validação

### Contato

- **GitHub Issues** - Para problemas específicos de migração
- **Email** - seu@email.com para suporte direto
- **Discussões** - GitHub Discussions para dúvidas gerais

### SLA de Suporte

- **Problemas Críticos** - 4 horas
- **Problemas de Migração** - 24 horas
- **Dúvidas Gerais** - 72 horas

---

**Lembre-se:** Sempre teste migrações em ambiente de desenvolvimento antes de aplicar em produção! 🧪