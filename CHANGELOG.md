# Changelog

Todas as mudanças notáveis deste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

## Tipos de Mudanças

- **Adicionado** - Novas funcionalidades
- **Mudado** - Mudanças em funcionalidades existentes
- **Depreciado** - Funcionalidades que serão removidas em versões futuras
- **Removido** - Funcionalidades removidas
- **Corrigido** - Correções de bugs
- **Segurança** - Vulnerabilidades corrigidas

---

## [Unreleased]

### Planejado
- Suporte a mais handlers do Monolog (Slack, Discord, etc.)
- Formatters customizados
- Integração com sistemas de monitoramento (Sentry, New Relic)
- Suporte a logging assíncrono

---

## [1.0.10] - 2025-11-12

### Adicionado
- Método `Logger::addSensitiveFields()` para adicionar campos sensíveis customizados
- Método `Logger::setMaskMode()` para configurar modo de mascaramento
- Constantes `MASK_MODE_REDACTED` e `MASK_MODE_PARTIAL` para modos de mascaramento
- Modo PARTIAL que mantém últimos 4 caracteres visíveis em dados sensíveis
- Suporte a sanitização recursiva em arrays aninhados
- Método `Logger::fail()` para inspecionar erros de inicialização
- Validação de configuração de email e Telegram
- Tratamento de erros melhorado com captura de exceções

### Mudado
- Sanitização de dados sensíveis agora suporta campos customizados
- Melhorada a detecção de campos sensíveis com verificação case-insensitive
- Documentação expandida com exemplos de campos customizados e modos de mascaramento

### Corrigido
- Correção na sanitização de arrays aninhados profundos
- Correção na validação de emails inválidos
- Correção no tratamento de valores não-escalares em campos sensíveis

---

## [1.0.0] - 2024-01-17

### Adicionado

#### Funcionalidades Principais
- Sistema enterprise de logs **PSR-3 compliant** completo
- Wrapper estático para Monolog com interface simplificada e intuitiva
- Suporte completo aos **8 níveis PSR-3**:
  - `debug()` - Informações detalhadas para desenvolvimento
  - `info()` - Eventos informativos gerais
  - `notice()` - Eventos significativos, porém normais
  - `warning()` - Avisos que não impedem execução
  - `error()` - Erros que exigem atenção
  - `critical()` - Falhas críticas do sistema
  - `alert()` - Ação imediata necessária
  - `emergency()` - Sistema inutilizável

#### Handlers de Log
- **StreamHandler** para logs em arquivo com rotação diária automática
  - Formato: `logs/file-YYYY-MM-DD.log` por padrão
  - Configurável via `Logger::settings()`
- **NativeMailerHandler** para notificações por email
  - Configuração via `Logger::enableLogByEmail()`
  - Nível mínimo configurável (padrão: ERROR)
  - Suporte a assunto customizado
- **TelegramBotHandler** para notificações via Telegram
  - Configuração via `Logger::enableLogByTelegram()`
  - Nível mínimo configurável (padrão: CRITICAL)
  - Suporte a bot tokens e chat IDs

#### Contexto Automático
- **request_id** único gerado por requisição (persistente durante toda a requisição)
- **session_id** automaticamente capturado se sessão PHP estiver ativa
- **ip_address** extraído de `$_SERVER['REMOTE_ADDR']`
- **user_agent** extraído de `$_SERVER['HTTP_USER_AGENT']`
- Contexto do usuário mesclado automaticamente com contexto automático

#### Segurança e Sanitização
- Sanitização automática de dados sensíveis antes de gravar logs
- Campos padrão sanitizados: `password`, `token`, `secret`, `senha`, `hash`
- Detecção case-insensitive de campos sensíveis
- Sanitização recursiva em arrays aninhados
- Truncamento automático de strings longas (máximo 120 caracteres)
- Proteção contra vazamento de dados sensíveis em logs
- Modo de mascaramento padrão: REDACTED (`[redacted]`)

#### Configuração
- Método `Logger::settings()` para configuração fluente:
  - `dir_logs` - Diretório customizado para logs
  - `file_log_label` - Nome customizado do arquivo de log
  - `level_file_log` - Nível mínimo para logs em arquivo
  - `level_email_log` - Nível mínimo para notificações por email
  - `level_telegram_log` - Nível mínimo para notificações por Telegram
- Configuração opcional de email e Telegram
- Validação de configurações antes da inicialização

#### Funções Helper
- Função helper global `logger()` para uso simplificado
- Carregamento automático via Composer autoload
- Retorna instância da classe Logger

#### Qualidade e Testes
- Suite completa de testes unitários com PHPUnit
- Cobertura de testes > 80%
- Análise estática com PHPStan (nível 7)
- Code style com PHP_CodeSniffer (PSR-12)
- Scripts Composer para qualidade:
  - `composer test` - Executa testes
  - `composer test-coverage` - Gera relatório de cobertura
  - `composer phpstan` - Análise estática
  - `composer cs-check` - Verifica code style
  - `composer cs-fix` - Auto-corrige code style
  - `composer quality` - Executa todos os checks

#### Documentação
- README.md completo com exemplos práticos
- Exemplos básicos em `examples/basic_usage.php`
- Exemplos avançados em `examples/advanced_usage.php`
- Documentação PHPDoc completa em todas as classes e métodos
- Guia de contribuição (CONTRIBUTING.md)
- Guia de publicação no Packagist (PUBLISH_GUIDE.md)

#### Infraestrutura
- Suporte a Docker para desenvolvimento
- Dockerfile e docker-compose.yml incluídos
- Ambiente PHP 8.1 com Apache pré-configurado
- Extensões PHP necessárias incluídas

### Técnico

#### Requisitos
- **PHP** >= 7.4
- **Monolog** ^2.0 | ^3.0
- **Composer** para gerenciamento de dependências

#### Arquitetura
- **PSR-3** compliant (Logger Interface)
- **PSR-4** autoloading
- **PSR-12** code style
- **Namespace:** `CodeFlowHub\Logger`
- **Tipo:** Library
- **Licença:** MIT

#### Distribuição
- Publicado no Packagist como `codeflow-hub/logger`
- Disponível via `composer require codeflow-hub/logger`
- Suporte a Semantic Versioning

---

## Links Úteis

- [README.md](README.md) - Documentação principal
- [CONTRIBUTING.md](CONTRIBUTING.md) - Guia de contribuição
- [PUBLISH_GUIDE.md](PUBLISH_GUIDE.md) - Guia de publicação
- [GitHub Issues](https://github.com/codeflow-hub/logger/issues) - Reportar bugs e solicitar features
- [Packagist](https://packagist.org/packages/codeflow-hub/logger) - Página do pacote

---

**Nota:** Este changelog segue o padrão [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/) e [Semantic Versioning](https://semver.org/lang/pt-BR/).