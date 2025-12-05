# CodeFlow Logger

[![Latest Stable Version](https://img.shields.io/packagist/v/codeflow-hub/logger.svg)](https://packagist.org/packages/codeflow-hub/logger)
[![Total Downloads](https://img.shields.io/packagist/dt/codeflow-hub/logger.svg)](https://packagist.org/packages/codeflow-hub/logger)
[![License](https://img.shields.io/packagist/l/codeflow-hub/logger.svg)](https://packagist.org/packages/codeflow-hub/logger)
[![PHP Version Require](https://img.shields.io/packagist/php-v/codeflow-hub/logger.svg)](https://packagist.org/packages/codeflow-hub/logger)

Sistema enterprise de logs **PSR-3 compliant** com suporte a múltiplos handlers (arquivo, email, Telegram).

Wrapper estático para **Monolog** com contexto estruturado automático, sanitização de dados sensíveis e helper global.

## ✨ Recursos

- ✅ **PSR-3 Compliant** – cobre os 8 níveis oficiais (`debug` → `emergency`)
- ✅ **Logging em arquivo** com rotação diária automática (`PROJECT_ROOT/logs/file-YYYY-MM-DD.log` por padrão)
- ✅ **Notificações por email** com `NativeMailerHandler` para `ERROR+`
- ✅ **Notificações por Telegram** com `TelegramBotHandler` para `CRITICAL+`
- ✅ **Contexto estruturado automático** (request_id, session_id, IP, user-agent)
- ✅ **Sanitização recursiva** de dados sensíveis (password, token, secret, senha, hash)
- ✅ **Campos sensíveis customizados** – adicione seus próprios campos para sanitização
- ✅ **Modos de mascaramento** – REDACTED (completo) ou PARTIAL (últimos 4 caracteres)
- ✅ **Função helper global** `logger()` para uso simplificado
- ✅ **Configuração fluente** via `Logger::settings()`, `enableLogByEmail()` e `enableLogByTelegram()`
- ✅ **Tratamento de erros** – método `Logger::fail()` para inspecionar problemas
- ✅ **Scripts de qualidade** prontos (`composer test`, `composer phpstan`, etc.)
- ✅ **Docker ready** – suporte para desenvolvimento com Docker

## Instalação

```bash
composer require codeflow-hub/logger
```

## 🚀 Uso Básico

### Logging simples

```php
use CodeFlowHub\Logger\Logger;

// Logs informativos
Logger::info("User authentication started", ['user_id' => 123]);
Logger::debug("Database query executed", ['query' => 'SELECT * FROM users']);

// Logs de erro (dispara email/telegram se configurado)
Logger::error("Database connection failed", ['error' => $e->getMessage()]);
Logger::critical("Payment gateway unavailable", ['gateway' => 'stripe']);
```

### Níveis PSR-3 com `notice()`

```php
Logger::notice('Plan provisioning finished', [
  'workspace' => 'acme/app',
  'elapsed' => '850ms'
]);
```

## ⚙️ Configuração (Opcional)

### Diretório, arquivos e níveis

```php
use CodeFlowHub\Logger\Logger;

Logger::settings([
  'dir_logs'            => __DIR__ . '/storage/logs', // pasta alternativa
  'file_log_label'      => 'app-' . date('Y-m-d') . '.log',
  'level_file_log'      => Logger::LEVEL_DEBUG,        // grava apenas DEGUG+
  'level_email_log'     => Logger::LEVEL_ERROR,    // emails apenas para ERROR+
  'level_telegram_log'  => Logger::LEVEL_CRITICAL,       // telegram para CRITICAL+
]);
```

> Se nenhum ajuste for feito, o logger usa `PROJECT_ROOT/logs/file-YYYY-MM-DD.log` e registra a partir de `DEBUG`.

### Notificações por email

```php
use CodeFlowHub\Logger\Logger;

// Configurar ANTES do primeiro uso do logger
Logger::enableLogByEmail(
    'noreply@app.com',           // Email remetente
    'admin@app.com',             // Email destinatário  
    'Sistema: Erro Crítico'      // Assunto (opcional)
);

// Agora erros ERROR+ serão enviados por email automaticamente
Logger::error("Database connection failed");
```

### Notificações por Telegram

```php
use CodeFlowHub\Logger\Logger;

// Configurar ANTES do primeiro uso do logger
Logger::enableLogByTelegram(
    '123456:ABC-DEF...',         // Token do bot (via BotFather)
    '-1001234567890'             // Chat ID do canal/grupo
);

// Agora erros CRITICAL+ serão enviados para o Telegram automaticamente  
Logger::critical("Cache system failure", ['cache_type' => 'redis']);
```

### Tratando falhas de inicialização

```php
if ($fail = Logger::fail()) {
  echo $fail->getMessage();
}
```

Use `Logger::fail()` para inspecionar problemas como diretório de log sem permissão ou parâmetros inválidos de configuração. O método retorna `null` quando não há erros pendentes.

## 📊 Níveis PSR-3 Suportados

| Método | Nível | Descrição | Email/Telegram |
|--------|--------|-----------|----------------|
| `debug()` | DEBUG | Informações detalhadas para desenvolvimento | ❌ |
| `info()` | INFO | Eventos informativos gerais | ❌ |
| `notice()` | NOTICE | Eventos significativos, porém normais | ❌ |
| `warning()` | WARNING | Avisos que não impedem execução | ❌ |
| `error()` | ERROR | Erros que exigem atenção | ✅ |
| `critical()` | CRITICAL | Falhas críticas do sistema | ✅ |
| `alert()` | ALERT | Ação imediata necessária | ✅ |
| `emergency()` | EMERGENCY | Sistema inutilizável | ✅ |

## 🔒 Segurança e Sanitização

O logger **automaticamente sanitiza dados sensíveis** antes de gravar nos logs:

```php
Logger::info("User login attempt", [
    'email' => 'user@example.com',
    'password' => '123456',           // Será exibido como [redacted]
    'api_token' => 'abc123',          // Será exibido como [redacted]  
    'user_secret' => 'secret123'      // Será exibido como [redacted]
]);
```

**Campos automaticamente sanitizados:** password, token, secret, senha, hash (inclusive em arrays aninhados). Strings longas são truncadas para 120 caracteres para facilitar a leitura.

### Campos Sensíveis Customizados

Você pode adicionar seus próprios campos sensíveis que serão automaticamente sanitizados:

```php
use CodeFlowHub\Logger\Logger;

// Adicionar campos customizados
Logger::addSensitiveFields(['credit_card', 'ssn', 'api_key', 'cpf']);

// Agora esses campos também serão sanitizados
Logger::info("Payment processed", [
    'credit_card' => '1234-5678-9012-3456',  // Será [redacted]
    'ssn' => '123-45-6789',                  // Será [redacted]
    'cpf' => '123.456.789-00',               // Será [redacted]
    'amount' => 99.90                        // Permanece visível
]);
```

Os campos customizados são combinados com os campos padrão e a verificação é **case-insensitive**. Campos duplicados são automaticamente removidos.

### Modos de Mascaramento

O logger suporta dois modos de mascaramento para dados sensíveis:

#### Modo REDACTED (Padrão)
Substitui completamente o valor por `[redacted]`:

```php
Logger::setMaskMode(Logger::MASK_MODE_REDACTED);

Logger::info("Login", ['password' => 'secret123']);
// Resultado: password => "[redacted]"
```

#### Modo PARTIAL
Mantém os últimos 4 caracteres visíveis, mascarando o resto:

```php
Logger::setMaskMode(Logger::MASK_MODE_PARTIAL);

Logger::info("Login", [
    'password' => 'secret123',        // Resultado: "*******t123"
    'api_token' => 'abc123xyz',       // Resultado: "*******3xyz"
    'pin' => '1234'                   // Resultado: "*******" (≤4 chars)
]);
```

> **Nota:** Valores com 4 ou menos caracteres são completamente mascarados no modo PARTIAL.

## 📁 Estrutura dos Logs

Os logs são salvos em `PROJECT_ROOT/logs/file-YYYY-MM-DD.log` por padrão. Use `Logger::settings(['file_log_label' => 'app-' . date('Y-m-d') . '.log'])` para adequar o nome ao seu padrão:

```
logs/
├── file-2024-01-15.log
├── file-2024-01-16.log
└── file-2024-01-17.log
```

### Contexto Automático

O logger **automaticamente enriquece** cada log com metadados úteis para rastreabilidade:

- **`request_id`** – ID único gerado por requisição (persistente durante toda a requisição)
- **`session_id`** – ID da sessão PHP (se sessão estiver ativa)
- **`ip_address`** – Endereço IP do cliente (`$_SERVER['REMOTE_ADDR']`)
- **`user_agent`** – User agent do navegador (`$_SERVER['HTTP_USER_AGENT']`)

O `request_id` é especialmente útil para rastrear todas as operações relacionadas a uma única requisição HTTP, facilitando a depuração e análise de logs.

### Formato do Log

Cada entrada de log segue o formato JSON estruturado do Monolog:

```json
{
  "message": "User authentication started",
  "context": {
    "user_id": 123,
    "request_id": "req_65a1b2c3d4e5f.12345",
    "session_id": "abc123def456",
    "ip_address": "192.168.1.1", 
    "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
    "file": "/path/to/file.php",
    "line": 42
  },
  "level": 200,
  "level_name": "INFO",
  "channel": "app",
  "datetime": "2024-01-17T10:30:45.123456+00:00"
}
```

O contexto fornecido pelo usuário é **mesclado** com o contexto automático, permitindo adicionar informações específicas do seu domínio.

## 📚 Exemplos Prontos

O projeto inclui exemplos práticos que demonstram diferentes cenários de uso:

```bash
# Exemplo básico - uso simples e direto
php examples/basic_usage.php

# Exemplo avançado - cenários de produção
php examples/advanced_usage.php
```

Os exemplos cobrem:
- ✅ Uso básico sem configuração
- ✅ Função helper global
- ✅ Sanitização automática de dados sensíveis
- ✅ Todos os níveis PSR-3
- ✅ Configuração de notificações (email/Telegram)
- ✅ Contexto HTTP automático
- ✅ Logs estruturados para análise
- ✅ Métricas de performance

### Casos de Uso Comuns

#### 1. Logging de Autenticação

```php
Logger::info("User login attempt", [
    'email' => $email,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'success' => true
]);

Logger::warning("Failed login attempt", [
    'email' => $email,
    'attempts' => $attempts,
    'ip' => $_SERVER['REMOTE_ADDR']
]);
```

#### 2. Logging de Operações de Banco de Dados

```php
Logger::debug("Database query executed", [
    'query' => $sql,
    'params' => $params,
    'execution_time' => $time . 'ms',
    'rows_affected' => $count
]);

Logger::error("Database connection failed", [
    'host' => $host,
    'database' => $database,
    'error' => $e->getMessage()
]);
```

#### 3. Logging de APIs Externas

```php
Logger::info("External API call", [
    'endpoint' => $url,
    'method' => 'POST',
    'status_code' => 200,
    'response_time' => $time . 'ms'
]);

Logger::error("External API failed", [
    'endpoint' => $url,
    'status_code' => $statusCode,
    'error' => $errorMessage,
    'retry_attempt' => $attempt
]);
```

#### 4. Logging de Transações Financeiras

```php
Logger::info("Payment processed", [
    'order_id' => $orderId,
    'amount' => $amount,
    'payment_method' => $method,
    'transaction_id' => $transactionId
]);

Logger::critical("Payment gateway unavailable", [
    'gateway' => 'stripe',
    'error' => $e->getMessage(),
    'affected_orders' => $count
]);
```

#### 5. Logging de Performance

```php
Logger::debug("Page load metrics", [
    'page' => $_SERVER['REQUEST_URI'],
    'load_time' => $loadTime . 'ms',
    'memory_usage' => memory_get_peak_usage(true) / 1024 / 1024 . 'MB',
    'db_queries' => $queryCount
]);
```

## 🛠️ Desenvolvimento

### Requisitos para Desenvolvimento

- **PHP** >= 7.4
- **Composer** >= 2.0
- **Docker** (opcional, para ambiente isolado)

### Instalação para Desenvolvimento

```bash
# Clonar o repositório
git clone https://github.com/codeflow-hub/logger.git
cd logger

# Instalar dependências
composer install
```

### Scripts Composer úteis

```bash
composer install            # Instala dependências (prod + dev)
composer test               # PHPUnit
composer test-coverage      # PHPUnit com cobertura em build/coverage
composer phpstan            # Análise estática (level 7)
composer cs-check           # CodeSniffer (PSR-12)
composer cs-fix             # Auto-fix de estilo
composer quality            # Executa cs-check + phpstan + test
```

### Desenvolvimento com Docker

O projeto inclui configuração Docker para desenvolvimento isolado:

```bash
# Iniciar ambiente Docker
docker-compose up -d

# Executar testes dentro do container
docker-compose exec web composer test

# Executar análise estática
docker-compose exec web composer phpstan

# Executar todos os checks de qualidade
docker-compose exec web composer quality
```

O ambiente Docker inclui:
- PHP 8.1 com Apache
- Extensões necessárias (mysqli, pdo, gd, zip, intl, bcmath)
- Composer pré-instalado
- Módulos Apache habilitados (rewrite, expires)

### Estrutura do projeto

```
Logger/
├── src/
│   ├── Logger.php          # Facade principal com métodos estáticos
│   └── helpers.php         # Função helper global logger()
├── tests/
│   ├── LoggerTest.php      # Suite completa de testes unitários
│   └── bootstrap.php       # Bootstrap dos testes
├── examples/
│   ├── basic_usage.php     # Exemplos básicos de uso
│   └── advanced_usage.php  # Cenários avançados e produção
├── docker/
│   └── Dockerfile          # Imagem Docker para desenvolvimento
├── build/
│   └── logs/               # Relatórios de testes e cobertura
├── logs/                    # Logs gerados durante desenvolvimento
├── composer.json            # Configuração do pacote
├── phpunit.xml             # Configuração PHPUnit
├── phpstan.neon            # Configuração PHPStan
├── phpcs.xml               # Configuração CodeSniffer
├── docker-compose.yml       # Orquestração Docker
├── README.md               # Documentação principal
├── CHANGELOG.md            # Histórico de mudanças
├── CONTRIBUTING.md         # Guia de contribuição
├── PUBLISH_GUIDE.md        # Guia de publicação no Packagist
└── LICENSE                 # Licença MIT
```

### Testes

O projeto inclui uma suite completa de testes unitários cobrindo:

- ✅ Todos os métodos de logging PSR-3
- ✅ Sanitização de dados sensíveis (padrão e customizados)
- ✅ Modos de mascaramento (REDACTED e PARTIAL)
- ✅ Configuração de email e Telegram
- ✅ Geração de Request ID
- ✅ Truncamento de strings longas
- ✅ Função helper global
- ✅ Tratamento de erros

Execute os testes com:

```bash
composer test
```

Para ver a cobertura de código:

```bash
composer test-coverage
# Relatório HTML disponível em: build/coverage/index.html
```

## 📋 Requisitos

### Requisitos Mínimos

- **PHP** >= 7.4
- **Monolog** ^2.0 | ^3.0
- **Extensões PHP:** Nenhuma adicional necessária (usa apenas extensões padrão do PHP)

### Compatibilidade

- ✅ **PHP 7.4+** (testado até PHP 8.3)
- ✅ **Monolog 2.x** e **3.x**
- ✅ **PSR-3** compliant
- ✅ **PSR-4** autoloading
- ✅ **PSR-12** code style

### Versão Atual

- **Versão:** 1.0.9
- **Status:** Estável
- **Licença:** MIT

## 📄 Licença

Este projeto está licenciado sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor, leia o [CONTRIBUTING.md](CONTRIBUTING.md) para detalhes sobre nosso código de conduta e processo de contribuição.

### Processo Rápido

1. **Fork** o projeto
2. **Clone** seu fork: `git clone https://github.com/seu-usuario/logger.git`
3. **Crie** uma branch: `git checkout -b feature/amazing-feature`
4. **Desenvolva** seguindo os padrões do projeto
5. **Teste** suas mudanças: `composer quality`
6. **Commit** suas mudanças: `git commit -m 'feat: add amazing feature'`
7. **Push** para a branch: `git push origin feature/amazing-feature`
8. **Abra** um Pull Request

### Diretrizes

- Siga os padrões **PSR-12** para código
- Adicione **testes** para novas funcionalidades
- Mantenha a **documentação** atualizada
- Use **Conventional Commits** para mensagens de commit
- Mantenha a **cobertura de testes** acima de 80%

Veja [CONTRIBUTING.md](CONTRIBUTING.md) para mais detalhes.

## 🔧 Troubleshooting

### Problemas Comuns

#### 1. Diretório de logs não tem permissão de escrita

**Erro:** `Log directory is not writable`

**Solução:**
```bash
# Criar diretório de logs
mkdir -p logs

# Dar permissão de escrita
chmod 755 logs
```

Ou configure um diretório customizado:
```php
Logger::settings([
    'dir_logs' => __DIR__ . '/storage/logs'
]);
```

#### 2. Email não está sendo enviado

**Verifique:**
- Se `enableLogByEmail()` foi chamado **antes** do primeiro log
- Se os emails são válidos
- Se o servidor tem suporte a `mail()` do PHP
- Se o nível do log é `ERROR` ou superior

**Teste:**
```php
Logger::enableLogByEmail('from@example.com', 'to@example.com');
Logger::error("Test email"); // Deve enviar email
```

#### 3. Telegram não está funcionando

**Verifique:**
- Se `enableLogByTelegram()` foi chamado **antes** do primeiro log
- Se o token do bot é válido
- Se o chat ID está correto
- Se o nível do log é `CRITICAL` ou superior

**Teste:**
```php
Logger::enableLogByTelegram('bot_token', 'chat_id');
Logger::critical("Test Telegram"); // Deve enviar para Telegram
```

#### 4. Dados sensíveis não estão sendo sanitizados

**Verifique:**
- Se o nome do campo contém palavras-chave (password, token, secret, etc.)
- Se campos customizados foram adicionados corretamente
- Se o modo de mascaramento está configurado corretamente

**Teste:**
```php
Logger::addSensitiveFields(['credit_card']);
Logger::info("Test", ['credit_card' => '1234-5678']); // Deve ser [redacted]
```

#### 5. Verificar erros de inicialização

Use o método `fail()` para inspecionar problemas:

```php
Logger::settings(['dir_logs' => '/invalid/path']);

if ($fail = Logger::fail()) {
    echo "Erro: " . $fail->getMessage();
}
```

### Logs de Debug

Para debug mais detalhado, você pode verificar os logs diretamente:

```bash
# Ver últimos logs
tail -f logs/file-$(date +%Y-%m-%d).log

# Buscar por request_id específico
grep "req_65a1b2c3d4e5f" logs/file-*.log

# Contar erros do dia
grep '"level_name":"ERROR"' logs/file-$(date +%Y-%m-%d).log | wc -l
```

## 📞 Suporte

- **Issues:** [GitHub Issues](https://github.com/codeflow-hub/logger/issues)
- **Email:** contato@codeflow.com.br
- **Website:** https://codeflow.com.br
- **Documentação:** Veja os arquivos em `examples/` para mais exemplos

## 📝 Changelog

Todas as mudanças notáveis deste projeto estão documentadas no [CHANGELOG.md](CHANGELOG.md).

O projeto segue [Semantic Versioning](https://semver.org/):
- **MAJOR** (1.0.0 → 2.0.0): Mudanças incompatíveis
- **MINOR** (1.0.0 → 1.1.0): Novas funcionalidades compatíveis
- **PATCH** (1.0.0 → 1.0.1): Correções de bugs compatíveis

## 📦 Publicação

Este pacote está disponível no [Packagist](https://packagist.org/packages/codeflow-hub/logger).

Para informações sobre como publicar novas versões, consulte o [PUBLISH_GUIDE.md](PUBLISH_GUIDE.md).

---

**Desenvolvido com ❤️ pela [CodeFlow Hub](https://github.com/codeflow-hub)**

⭐ Se este projeto foi útil para você, considere dar uma estrela no GitHub!