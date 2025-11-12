# Changelog

Todas as mudanças notáveis deste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

## [Unreleased]

## [1.0.0] - 2024-01-17

### Adicionado
- Sistema enterprise de logs PSR-3 compliant
- Wrapper estático para Monolog com interface simplificada
- Suporte a múltiplos handlers:
  - StreamHandler para logs em arquivo com rotação diária
  - NativeMailerHandler para notificações por email (ERROR+)  
  - TelegramBotHandler para notificações por Telegram (ERROR+)
- Contexto estruturado automático:
  - request_id único por requisição
  - session_id, user_id, ip_address, user_agent
  - file e line do código que gerou o log
- Sanitização automática de dados sensíveis:
  - password, token, secret, senha, hash → [redacted]
  - Truncamento de strings longas (120 caracteres)
  - Proteção contra vazamento de dados em logs
- Função helper global `logger()` para uso simplificado
- Suporte completo aos 8 níveis PSR-3:
  - debug(), info(), warning()
  - error(), critical(), alert(), emergency()
- Configuração opcional para email e Telegram
- Documentação completa com exemplos
- Testes unitários com PHPUnit
- Análise estática com PHPStan
- Code style com PHP_CodeSniffer
- Licença MIT

### Técnico
- PHP >= 7.4
- Monolog ^2.0 | ^3.0
- PSR-4 autoloading
- Namespace: CodeFlow\Logger
- Packagist ready