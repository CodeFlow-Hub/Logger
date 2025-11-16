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
- ✅ **Configuração fluente** via `Logger::settings()`, `enableLogByEmail()` e `enableLogByTelegram()`
- ✅ **Scripts de qualidade** prontos (`composer test`, `composer phpstan`, etc.)

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

## 📁 Estrutura dos Logs

Os logs são salvos em `PROJECT_ROOT/logs/file-YYYY-MM-DD.log` por padrão. Use `Logger::settings(['file_log_label' => 'app-' . date('Y-m-d') . '.log'])` para adequar o nome ao seu padrão:

```
logs/
├── file-2024-01-15.log
├── file-2024-01-16.log
└── file-2024-01-17.log
```

### Formato do Log

```json
{
  "message": "User authentication started",
  "context": {
    "user_id": 123,
    "request_id": "req_65a1b2c3d4e5f.12345",
    "session_id": "abc123def456",
    "ip_address": "192.168.1.1", 
    "user_agent": "Mozilla/5.0...",
    "file": "/path/to/file.php",
    "line": 42
  },
  "level": 200,
  "level_name": "INFO",
  "channel": "app",
  "datetime": "2024-01-17T10:30:45.123456+00:00"
}
```

## 📚 Exemplos prontos

```bash
php examples/basic_usage.php
php examples/advanced_usage.php
```

Os exemplos cobrem desde o onboarding rápido até fluxos de produção (contexto HTTP, sanitização, métricas e notificações). Eles são ideais para validar o pacote em ambientes locais ou pipelines.

## 🛠️ Desenvolvimento

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

### Estrutura do projeto

```
src/
├── Logger.php          # Facade principal
└── helpers.php         # Função helper global
tests/
├── LoggerTest.php      # Cobertura de sanitização e configuração
└── bootstrap.php       # Bootstrap dos testes
examples/
├── basic_usage.php     # Tour rápido
└── advanced_usage.php  # Cenários completos
```

## 📋 Requisitos

- **PHP** >= 7.4
- **Monolog** ^2.0 | ^3.0

## 📄 Licença

Este projeto está licenciado sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma feature branch (`git checkout -b feature/amazing-feature`)
3. Commit suas mudanças (`git commit -m 'Add amazing feature'`)
4. Push para a branch (`git push origin feature/amazing-feature`)
5. Abra um Pull Request

## 📞 Suporte

- **Issues:** [GitHub Issues](https://github.com/codeflow-hub/logger/issues)
- **Email:** contato@codeflow.com.br
- **Website:** https://codeflow.com.br

---

Desenvolvido com ❤️ pela [CodeFlow Hub](https://github.com/codeflow-hub)