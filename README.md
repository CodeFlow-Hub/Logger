# CodeFlow Logger

[![Latest Stable Version](https://img.shields.io/packagist/v/codeflow/logger.svg)](https://packagist.org/packages/codeflow/logger)
[![Total Downloads](https://img.shields.io/packagist/dt/codeflow/logger.svg)](https://packagist.org/packages/codeflow/logger)
[![License](https://img.shields.io/packagist/l/codeflow/logger.svg)](https://packagist.org/packages/codeflow/logger)
[![PHP Version Require](https://img.shields.io/packagist/php-v/codeflow/logger.svg)](https://packagist.org/packages/codeflow/logger)

Sistema enterprise de logs **PSR-3 compliant** com suporte a múltiplos handlers (arquivo, email, Telegram). 

Wrapper estático para **Monolog** com contexto estruturado automático e sanitização de dados sensíveis.

## ✨ Recursos

- ✅ **PSR-3 Compliant** - Implementa todos os 8 níveis padrão de logging
- ✅ **Logging em arquivo** com rotação diária automática  
- ✅ **Notificações por email** para erros críticos (ERROR+)
- ✅ **Notificações por Telegram** para erros críticos (ERROR+)
- ✅ **Contexto estruturado automático** (request_id, session_id, user_id, IP, user-agent)
- ✅ **Sanitização automática** de dados sensíveis (passwords, tokens, secrets)
- ✅ **Interface estática simples** - sem necessidade de DI ou configuração complexa
- ✅ **Zero configuração** - funciona out-of-the-box

## 📦 Instalação

```bash
composer require codeflowhub/logger
```

## 🚀 Uso Básico

### Logging Simples

```php
use function CodeFlow\Logger\logger;

// Logs informativos
logger()->info("User authentication started", ['user_id' => 123]);
logger()->debug("Database query executed", ['query' => 'SELECT * FROM users']);

// Logs de erro (dispara email/telegram se configurado)
logger()->error("Database connection failed", ['error' => $e->getMessage()]);
logger()->critical("Payment gateway unavailable", ['gateway' => 'stripe']);
```

### Com Classe Estática

```php
use CodeFlow\Logger\Logger;

Logger::info("User created successfully", ['user_id' => 456]);
Logger::warning("Validation failed", ['field' => 'email', 'value' => 'invalid@']);
Logger::error("Failed to save user", ['user_id' => 123, 'error' => $e->getMessage()]);
```

## ⚙️ Configuração (Opcional)

### Notificações por Email

```php
use CodeFlow\Logger\Logger;

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
use CodeFlow\Logger\Logger;

// Configurar ANTES do primeiro uso do logger
Logger::enableLogByTelegram(
    '123456:ABC-DEF...',         // Token do bot (via BotFather)
    '-1001234567890'             // Chat ID do canal/grupo
);

// Agora erros ERROR+ serão enviados para o Telegram automaticamente  
Logger::critical("Cache system failure", ['cache_type' => 'redis']);
```

## 📊 Níveis PSR-3 Suportados

| Método | Nível | Descrição | Email/Telegram |
|--------|--------|-----------|----------------|
| `debug()` | DEBUG | Informações detalhadas para desenvolvimento | ❌ |
| `info()` | INFO | Eventos informativos gerais | ❌ |  
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

**Campos automaticamente sanitizados:** password, token, secret, senha, hash

## 📁 Estrutura dos Logs

Os logs são salvos em `logs/app-YYYY-MM-DD.log` com rotação diária automática:

```
logs/
├── app-2024-01-15.log
├── app-2024-01-16.log
└── app-2024-01-17.log
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

## 🛠️ Desenvolvimento

### Testes

```bash
composer test                    # Executar testes
composer test-coverage          # Testes com cobertura  
composer phpstan                # Análise estática
composer cs-check               # Code style check
composer cs-fix                 # Code style fix
composer quality                # Executar todas as verificações
```

### Estrutura do Projeto

```
src/
├── Logger.php          # Classe principal
└── helpers.php         # Função helper global
tests/
├── LoggerTest.php      # Testes unitários
└── bootstrap.php       # Bootstrap dos testes
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