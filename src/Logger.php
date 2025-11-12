<?php

namespace CodeFlowHub\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Logger as LoggerMonolog;

/**
 * =========================================================================================
 * LOGGER - SISTEMA ENTERPRISE DE LOGS (PSR-3 COMPLIANT)
 * =========================================================================================
 * Wrapper estático para Monolog com suporte a múltiplos handlers (arquivo, email, Telegram).
 * Implementa padrões enterprise de logging com contexto estruturado e sanitização automática.
 * 
 * Recursos:
 * - ✅ Logging em arquivo com rotação diária automática
 * - ✅ Notificações por email para erros críticos (ERROR+)
 * - ✅ Notificações por Telegram para erros críticos (ERROR+)
 * - ✅ Contexto estruturado automático (request_id, session_id, user_id, IP, user-agent)
 * - ✅ Sanitização automática de dados sensíveis (passwords, tokens, secrets)
 * - ✅ Suporte completo aos 8 níveis PSR-3 (debug → emergency)
 * 
 * Uso Básico:
 *   logger()->info("User authentication started", ['user_id' => 123]);
 *   logger()->error("Database connection failed", ['error' => $e->getMessage()]);
 * 
 * Configuração (opcional - antes do primeiro uso):
 *   Logger::enableLogByEmail('from@email.com', 'to@email.com', 'Subject');
 *   Logger::enableLogByTelegram('bot_token', 'chat_id');
 * 
 * Níveis PSR-3 (ordem crescente de severidade):
 *   debug()     → Informações detalhadas para debug (desenvolvimento)
 *   info()      → Eventos informativos gerais (operações bem-sucedidas)
 *   notice()    → Não implementado (use info ou warning)
 *   warning()   → Avisos que não impedem execução
 *   error()     → Erros que exigem atenção (dispara email/telegram)
 *   critical()  → Falhas críticas do sistema (dispara email/telegram)
 *   alert()     → Ação imediata necessária (dispara email/telegram)
 *   emergency() → Sistema inutilizável (dispara email/telegram)
 * 
 * @package CodeFlowHub\Logger
 * @version 2.0
 * @see https://www.php-fig.org/psr/psr-3/
 * =========================================================================================
 */
class Logger
{
   const DEFAULT_DIR_LOGS =            __DIR__ . "/../logs";
   const DEFAULT_FILE_LOG_LABEL =      "file-" . date("Y-m-d") . ".log";
   const DEFAULT_LEVEL_FILE_LOG =      LoggerMonolog::DEBUG;
   const DEFAULT_LEVEL_EMAIL_LOG =     LoggerMonolog::ERROR;
   const DEFAULT_LEVEL_TELEGRAM_LOG =  LoggerMonolog::ERROR;

   // =========================================================================================
   // CONSTANTES E PROPRIEDADES
   // =========================================================================================

   /** @var LoggerMonolog|null Engine Monolog subjacente */
   private static $engine;

   /** @var bool Flag de inicialização para evitar múltiplas configurações */
   private static $initialized = false;

   /** @var string|null ID único da requisição atual (persistente durante toda a request) */
   private static $requestId = null;

   /** @var string Diretório onde os arquivos de log serão armazenados */
   private static $dirLogs = self::DEFAULT_DIR_LOGS;

   /** @var string Nome do arquivo de log (inclui data) */
   private static $fileLogLabel = self::DEFAULT_FILE_LOG_LABEL;

   private static $levelFileLog = self::DEFAULT_LEVEL_FILE_LOG;
   private static $levelEmailLog = self::DEFAULT_LEVEL_EMAIL_LOG;
   private static $levelTelegramLog = self::DEFAULT_LEVEL_TELEGRAM_LOG;

   // -----------------------------------------------------------------------------------------
   // Configurações de Email
   // -----------------------------------------------------------------------------------------

   /** @var string|null Email remetente para notificações */
   private static $senderEmail = null;

   /** @var string|null Email destinatário para notificações */
   private static $recipientEmail = null;

   /** @var string|null Assunto dos emails de notificação */
   private static $subject = null;

   /** @var bool Flag de habilitação de notificações por email */
   private static $emailEnabled = false;

   // -----------------------------------------------------------------------------------------
   // Configurações de Telegram
   // -----------------------------------------------------------------------------------------

   /** @var string|null Token do bot do Telegram */
   private static $telegramBotToken = null;

   /** @var string|null ID do chat/canal do Telegram */
   private static $telegramChatId = null;

   /** @var bool Flag de habilitação de notificações por Telegram */
   /** @var bool Flag de habilitação de notificações por Telegram */
   private static $telegramEnabled = false;

   // =========================================================================================
   // INICIALIZAÇÃO
   // =========================================================================================

   /**
    * -------------------------------------------------------------------------------------
    * MÉTODO: inicializa o sistema de logs
    * -------------------------------------------------------------------------------------
    * Intenção: configurar o Monolog com handlers para arquivo, email e Telegram.
    * 
    * Pré-condições: nenhuma.
    * 
    * Passos / Fluxo:
    * 1. Early return se já inicializado (previne duplicação).
    * 2. Instanciar LoggerMonolog com nome "app".
    * 3. Adicionar StreamHandler para arquivo (logs/app-YYYY-MM-DD.log, nível DEBUG).
    * 4. Se email habilitado, adicionar NativeMailerHandler (nível ERROR).
    * 5. Se Telegram habilitado, adicionar TelegramBotHandler (nível ERROR).
    * 6. Marcar flag de inicialização.
    * 
    * Efeitos colaterais:
    * - Define self::$engine como instância do Monolog.
    * - Marca self::$initialized como true.
    * - Cria arquivo de log no diretório logs/ se não existir.
    * 
    * Retornos: void.
    * 
    * Tratamento de erros: nenhum (delega ao Monolog).
    * 
    * @return void
    */
   private static function initialize(): void
   {
      // Intenção: evitar múltiplas inicializações do sistema de logs.
      if (self::$initialized)
      {
         return;
      }

      // Intenção: criar engine Monolog com nome "app".
      self::$engine = new LoggerMonolog("app");

      // Intenção: adicionar handler de arquivo para todos os níveis (DEBUG+).
      self::$engine->pushHandler(
         new StreamHandler(
            self::$dirLogs . "/" . self::$fileLogLabel,
            self::$levelFileLog
         )
      );

      // Intenção: adicionar handler de email para erros críticos (ERROR+).
      if (self::$emailEnabled)
      {
         self::$engine->pushHandler(
            new NativeMailerHandler(
               self::$recipientEmail,
               self::$subject,
               self::$senderEmail,
               self::$levelEmailLog
            )
         );
      }

      // Intenção: adicionar handler de Telegram para erros críticos (ERROR+).
      if (self::$telegramEnabled)
      {
         self::$engine->pushHandler(
            new TelegramBotHandler(
               self::$telegramBotToken,
               self::$telegramChatId,
               self::$levelTelegramLog
            )
         );
      }

      // Intenção: marcar logger como inicializado.
      self::$initialized = true;
   }

   // =========================================================================================
   // MÉTODOS PSR-3 (ORDEM CRESCENTE DE SEVERIDADE)
   // =========================================================================================

   /**
    * -------------------------------------------------------------------------------------
    * MÉTODO: registra log de debug
    * -------------------------------------------------------------------------------------
    * Intenção: registrar informações detalhadas para desenvolvimento e troubleshooting.
    * 
    * Pré-condições: nenhuma.
    * 
    * Passos / Fluxo:
    * 1. Inicializar logger se necessário.
    * 2. Construir contexto estruturado com dados da requisição.
    * 3. Enviar para Monolog com nível DEBUG.
    * 
    * Efeitos colaterais: grava em arquivo de log (logs/app-YYYY-MM-DD.log).
    * 
    * Retornos: void.
    * 
    * Tratamento de erros: delega ao Monolog.
    * 
    * @param string $message Mensagem em inglês, presente simples (ex: "User data fetched").
    * @param array $context Contexto estruturado (IDs, dados relevantes sanitizados).
    * @return void
    * @example logger()->debug("Database query executed", ['query' => 'SELECT * FROM users', 'rows' => 10]);
    */
   public static function debug(string $message, array $context = []): void
   {
      // Intenção: garantir que logger está inicializado antes de registrar.
      self::initialize();
      self::$engine->debug($message, self::buildLogContext($context));
   }

   /**
    * -------------------------------------------------------------------------------------
    * MÉTODO: registra log informativo
    * -------------------------------------------------------------------------------------
    * Intenção: registrar eventos informativos gerais e operações bem-sucedidas.
    * 
    * Pré-condições: nenhuma.
    * 
    * Passos / Fluxo:
    * 1. Inicializar logger se necessário.
    * 2. Construir contexto estruturado com dados da requisição.
    * 3. Enviar para Monolog com nível INFO.
    * 
    * Efeitos colaterais: grava em arquivo de log (logs/app-YYYY-MM-DD.log).
    * 
    * Retornos: void.
    * 
    * Tratamento de erros: delega ao Monolog.
    * 
    * @param string $message Mensagem em inglês, presente simples (ex: "User created successfully").
    * @param array $context Contexto estruturado (IDs, dados relevantes sanitizados).
    * @return void
    * @example logger()->info("User authentication started", ['user_id' => 123, 'ip_address' => '192.168.1.1']);
    */
   public static function info(string $message, array $context = []): void
   {
      // Intenção: garantir que logger está inicializado antes de registrar.
      self::initialize();
      self::$engine->info($message, self::buildLogContext($context));
   }

   /**
    * -------------------------------------------------------------------------------------
    * MÉTODO: registra log de aviso
    * -------------------------------------------------------------------------------------
    * Intenção: registrar avisos que não impedem execução mas podem indicar problemas.
    * 
    * Pré-condições: nenhuma.
    * 
    * Passos / Fluxo:
    * 1. Inicializar logger se necessário.
    * 2. Construir contexto estruturado com dados da requisição.
    * 3. Enviar para Monolog com nível WARNING.
    * 
    * Efeitos colaterais: grava em arquivo de log (logs/app-YYYY-MM-DD.log).
    * 
    * Retornos: void.
    * 
    * Tratamento de erros: delega ao Monolog.
    * 
    * @param string $message Mensagem em inglês, presente simples (ex: "Invalid input received").
    * @param array $context Contexto estruturado (IDs, dados relevantes sanitizados).
    * @return void
    * @example logger()->warning("Validation failed", ['field' => 'email', 'value' => 'invalid@']);
    */
   public static function warning(string $message, array $context = []): void
   {
      // Intenção: garantir que logger está inicializado antes de registrar.
      self::initialize();
      self::$engine->warning($message, self::buildLogContext($context));
   }

   /**
    * -------------------------------------------------------------------------------------
    * MÉTODO: registra log de erro
    * -------------------------------------------------------------------------------------
    * Intenção: registrar erros que exigem atenção mas não impedem sistema.
    * 
    * Pré-condições: nenhuma.
    * 
    * Passos / Fluxo:
    * 1. Inicializar logger se necessário.
    * 2. Construir contexto estruturado com dados da requisição.
    * 3. Enviar para Monolog com nível ERROR.
    * 4. Se configurado, dispara notificações por email e/ou Telegram.
    * 
    * Efeitos colaterais:
    * - Grava em arquivo de log (logs/app-YYYY-MM-DD.log).
    * - Envia email se configurado.
    * - Envia mensagem Telegram se configurado.
    * 
    * Retornos: void.
    * 
    * Tratamento de erros: delega ao Monolog.
    * 
    * @param string $message Mensagem em inglês, presente simples (ex: "Database connection failed").
    * @param array $context Contexto estruturado (IDs, erro, stack trace quando aplicável).
    * @return void
    * @example logger()->error("Failed to save user", ['user_id' => 123, 'error' => $e->getMessage()]);
    */
   public static function error(string $message, array $context = []): void
   {
      // Intenção: garantir que logger está inicializado antes de registrar.
      self::initialize();
      self::$engine->error($message, self::buildLogContext($context));
   }

   /**
    * -------------------------------------------------------------------------------------
    * MÉTODO: registra log crítico
    * -------------------------------------------------------------------------------------
    * Intenção: registrar condições críticas que requerem atenção imediata.
    * 
    * Pré-condições: nenhuma.
    * 
    * Passos / Fluxo:
    * 1. Inicializar logger se necessário.
    * 2. Construir contexto estruturado com dados da requisição.
    * 3. Enviar para Monolog com nível CRITICAL.
    * 4. Se configurado, dispara notificações por email e/ou Telegram.
    * 
    * Efeitos colaterais:
    * - Grava em arquivo de log (logs/app-YYYY-MM-DD.log).
    * - Envia email se configurado.
    * - Envia mensagem Telegram se configurado.
    * 
    * Retornos: void.
    * 
    * Tratamento de erros: delega ao Monolog.
    * 
    * @param string $message Mensagem em inglês, presente simples (ex: "Payment gateway unavailable").
    * @param array $context Contexto estruturado (IDs, erro, impacto no sistema).
    * @return void
    * @example logger()->critical("Cache system failure", ['cache_type' => 'redis', 'error' => $e->getMessage()]);
    */
   public static function critical(string $message, array $context = []): void
   {
      // Intenção: garantir que logger está inicializado antes de registrar.
      self::initialize();
      self::$engine->critical($message, self::buildLogContext($context));
   }

   /**
    * -------------------------------------------------------------------------------------
    * MÉTODO: registra log de alerta
    * -------------------------------------------------------------------------------------
    * Intenção: registrar situações que exigem ação imediata.
    * 
    * Pré-condições: nenhuma.
    * 
    * Passos / Fluxo:
    * 1. Inicializar logger se necessário.
    * 2. Construir contexto estruturado com dados da requisição.
    * 3. Enviar para Monolog com nível ALERT.
    * 4. Se configurado, dispara notificações por email e/ou Telegram.
    * 
    * Efeitos colaterais:
    * - Grava em arquivo de log (logs/app-YYYY-MM-DD.log).
    * - Envia email se configurado.
    * - Envia mensagem Telegram se configurado.
    * 
    * Retornos: void.
    * 
    * Tratamento de erros: delega ao Monolog.
    * 
    * @param string $message Mensagem em inglês, presente simples (ex: "Disk space critical").
    * @param array $context Contexto estruturado (IDs, métrica, limite).
    * @return void
    * @example logger()->alert("Memory usage above 95%", ['current' => '96%', 'limit' => '95%']);
    */
   public static function alert(string $message, array $context = []): void
   {
      // Intenção: garantir que logger está inicializado antes de registrar.
      self::initialize();
      self::$engine->alert($message, self::buildLogContext($context));
   }

   /**
    * -------------------------------------------------------------------------------------
    * MÉTODO: registra log de emergência
    * -------------------------------------------------------------------------------------
    * Intenção: registrar falha total do sistema (nível mais alto de severidade).
    * 
    * Pré-condições: nenhuma.
    * 
    * Passos / Fluxo:
    * 1. Inicializar logger se necessário.
    * 2. Construir contexto estruturado com dados da requisição.
    * 3. Enviar para Monolog com nível EMERGENCY.
    * 4. Se configurado, dispara notificações por email e/ou Telegram.
    * 
    * Efeitos colaterais:
    * - Grava em arquivo de log (logs/app-YYYY-MM-DD.log).
    * - Envia email se configurado.
    * - Envia mensagem Telegram se configurado.
    * 
    * Retornos: void.
    * 
    * Tratamento de erros: delega ao Monolog.
    * 
    * @param string $message Mensagem em inglês, presente simples (ex: "System is down").
    * @param array $context Contexto estruturado (IDs, causa, impacto total).
    * @return void
    * @example logger()->emergency("Database server unreachable", ['host' => 'db.example.com', 'error' => 'Connection timeout']);
    */
   public static function emergency(string $message, array $context = []): void
   {
      // Intenção: garantir que logger está inicializado antes de registrar.
      self::initialize();
      self::$engine->emergency($message, self::buildLogContext($context));
   }

   // =========================================================================================
   // MÉTODOS DE CONFIGURAÇÃO
   // =========================================================================================

   /**
    * -------------------------------------------------------------------------------------
    * MÉTODO: habilita notificações por email
    * -------------------------------------------------------------------------------------
    * Intenção: configurar envio de logs por email para erros críticos (ERROR+).
    * 
    * Pré-condições: deve ser chamado ANTES do primeiro uso do Logger.
    * 
    * Passos / Fluxo:
    * 1. Armazenar email remetente.
    * 2. Armazenar email destinatário.
    * 3. Definir assunto (padrão: "Erro detectado no sistema").
    * 4. Habilitar flag de email.
    * 
    * Efeitos colaterais:
    * - Define self::$senderEmail, self::$recipientEmail, self::$subject.
    * - Marca self::$emailEnabled como true.
    * - Próxima inicialização incluirá NativeMailerHandler.
    * 
    * Retornos: void.
    * 
    * Tratamento de erros: nenhum (validação delegada ao Monolog).
    * 
    * @param string $senderEmail Email remetente das notificações.
    * @param string $recipientEmail Email destinatário das notificações.
    * @param string|null $subject Assunto do email (padrão: "Erro detectado no sistema").
    * @return void
    * @example Logger::enableLogByEmail('noreply@app.com', 'admin@app.com', 'Sistema: Erro Crítico');
    */
   public static function enableLogByEmail(string $senderEmail, string $recipientEmail, ?string $subject = null): void
   {
      // Intenção: configurar parâmetros para envio de notificações por email.
      self::$senderEmail = $senderEmail;
      self::$recipientEmail = $recipientEmail;
      self::$subject = $subject ?? "Erro detectado no sistema";
      self::$emailEnabled = true;
   }

   /**
    * -------------------------------------------------------------------------------------
    * MÉTODO: habilita notificações por Telegram
    * -------------------------------------------------------------------------------------
    * Intenção: configurar envio de logs por Telegram para erros críticos (ERROR+).
    * 
    * Pré-condições: deve ser chamado ANTES do primeiro uso do Logger.
    * 
    * Passos / Fluxo:
    * 1. Armazenar token do bot do Telegram.
    * 2. Armazenar ID do chat/canal.
    * 3. Habilitar flag de Telegram.
    * 
    * Efeitos colaterais:
    * - Define self::$telegramBotToken e self::$telegramChatId.
    * - Marca self::$telegramEnabled como true.
    * - Próxima inicialização incluirá TelegramBotHandler.
    * 
    * Retornos: void.
    * 
    * Tratamento de erros: nenhum (validação delegada ao Monolog).
    * 
    * @param string $botToken Token do bot do Telegram (obtido via BotFather).
    * @param string $chatId ID do chat ou canal que receberá as mensagens.
    * @return void
    * @example Logger::enableLogByTelegram('123456:ABC-DEF...', '-1001234567890');
    */
   public static function enableLogByTelegram(string $botToken, string $chatId): void
   {
      // Intenção: configurar parâmetros para envio de notificações por Telegram.
      self::$telegramBotToken = $botToken;
      self::$telegramChatId = $chatId;
      self::$telegramEnabled = true;
   }

   // =========================================================================================
   // MÉTODOS PRIVADOS (HELPERS)
   // =========================================================================================

   /**
    * -------------------------------------------------------------------------------------
    * MÉTODO: gera ID único da requisição
    * -------------------------------------------------------------------------------------
    * Intenção: criar ou retornar ID único para rastreamento de toda a requisição HTTP.
    * 
    * Pré-condições: nenhuma.
    * 
    * Passos / Fluxo:
    * 1. Early return se request ID já foi gerado.
    * 2. Gerar novo ID único com prefixo "req_" usando uniqid().
    * 3. Armazenar em self::$requestId para reutilização.
    * 4. Retornar request ID.
    * 
    * Efeitos colaterais: define self::$requestId na primeira chamada.
    * 
    * Retornos: string com formato "req_XXXXXXXXXXXXX.XXXXX".
    * 
    * Tratamento de erros: nenhum.
    * 
    * @return string Request ID único e persistente durante toda a requisição.
    */
   private static function generateRequestId(): string
   {
      // Intenção: reutilizar request ID existente para manter rastreabilidade.
      if (!empty(self::$requestId))
      {
         return self::$requestId;
      }

      // Intenção: gerar novo ID único para esta requisição.
      self::$requestId = uniqid('req_', true);
      return self::$requestId;
   }

   /**
    * -------------------------------------------------------------------------------------
    * MÉTODO: constrói contexto estruturado do log
    * -------------------------------------------------------------------------------------
    * Intenção: adicionar contexto base automático e sanitizar dados do usuário.
    * 
    * Pré-condições: nenhuma.
    * 
    * Passos / Fluxo:
    * 1. Criar array de contexto base (request_id, session_id, user_id, IP, user-agent).
    * 2. Sanitizar dados adicionais fornecidos pelo usuário.
    * 3. Mesclar contexto base com dados sanitizados.
    * 4. Retornar contexto completo.
    * 
    * Efeitos colaterais: nenhum.
    * 
    * Retornos: array com contexto estruturado mesclado.
    * 
    * Tratamento de erros: nenhum (sanitização trata valores inválidos).
    * 
    * @param array $context Dados adicionais fornecidos pelo usuário.
    * @return array Contexto estruturado completo (base + dados sanitizados).
    */
   private static function buildLogContext(array $context = []): array
   {
      // Intenção: criar contexto base automático com dados da requisição HTTP.
      $baseContext = [
         'request_id'    => self::generateRequestId(),
         'session_id'    => (session_status() === PHP_SESSION_ACTIVE ? session_id() : null),
         'user_id'       => $_SESSION['user_id']         ?? null,
         'ip_address'    => $_SERVER['REMOTE_ADDR']      ?? 'unknown',
         'user_agent'    => $_SERVER['HTTP_USER_AGENT']  ?? 'unknown',
      ];

      // Intenção: mesclar contexto base com dados adicionais sanitizados.
      return array_merge($baseContext, self::sanitizeLogParams($context));
   }

   /**
    * -------------------------------------------------------------------------------------
    * MÉTODO: sanitiza dados sensíveis do log
    * -------------------------------------------------------------------------------------
    * Intenção: remover/mascarar dados sensíveis e normalizar valores para logging seguro.
    * 
    * Pré-condições: nenhuma.
    * 
    * Passos / Fluxo:
    * 1. Early return null se array vazio.
    * 2. Iterar sobre cada par chave-valor.
    * 3. Detectar campos sensíveis (password, token, secret, senha, hash) e substituir por "[redacted]".
    * 4. Para valores escalares: truncar strings em 120 caracteres.
    * 5. Para arrays: aplicar sanitização recursivamente.
    * 6. Para objetos: registrar apenas o nome da classe.
    * 7. Retornar array sanitizado.
    * 
    * Efeitos colaterais: nenhum.
    * 
    * Retornos: array sanitizado ou null se vazio.
    * 
    * Tratamento de erros: nenhum (trata tipos inesperados graciosamente).
    * 
    * @param array $params Dados originais a serem sanitizados.
    * @return array|null Array sanitizado ou null se entrada vazia.
    */
   private static function sanitizeLogParams(array $params): ?array
   {
      // Intenção: early return para arrays vazios.
      if (empty($params))
      {
         return null;
      }

      $sanitized = [];

      foreach ($params as $key => $value)
      {
         $lowerKey = is_string($key) ? strtolower($key) : '';

         // Intenção: detectar e mascarar campos sensíveis (senhas, tokens, secrets).
         if (preg_match('/password|token|secret|senha|hash/', $lowerKey))
         {
            $sanitized[$key] = '[redacted]';
            continue;
         }

         // Intenção: processar valores escalares (truncar strings longas).
         if (is_scalar($value) || $value === null)
         {
            $sanitized[$key] = is_string($value) ? mb_substr(trim($value), 0, 120) : $value;
            continue;
         }

         // Intenção: processar arrays recursivamente, objetos como nome da classe.
         $sanitized[$key] = is_array($value)
            ? self::sanitizeLogParams($value)
            : (is_object($value) ? get_class($value) : (string) gettype($value));
      }

      return $sanitized;
   }

   /**
    * -------------------------------------------------------------------------------------
    * MÉTODO: inicializa configurações do Logger
    * -------------------------------------------------------------------------------------
    * Intenção: configurar diretório e nome do arquivo de log.
    * 
    * Pré-condições: nenhuma.
    * 
    * Passos / Fluxo:
    * 1. Definir diretório de logs a partir das configurações fornecidas.
    * 2. Definir nome do arquivo de log com base no rótulo fornecido ou padrão.
    * 
    * Efeitos colaterais:
    * - Define self::$directory e self::$fileLogLabel.
    * 
    * Retornos: void.
    * 
    * Tratamento de erros: nenhum.
    * 
    * @param array $settings Configurações com chaves 'dir_logs' e 'file_log_label'.
    * @return void
    * @example Logger::settings(['dir_logs' => '/var/logs/myapp', 'file_log_label' => 'custom-log-2024-06-01.log']);
    */
   public static function settings(array $settings = []): void
   {
      self::$dirLogs          = $settings['dir_logs']             ?? self::DEFAULT_DIR_LOGS;
      self::$fileLogLabel     = $settings['file_log_label']       ?? self::DEFAULT_FILE_LOG_LABEL;
      self::$levelFileLog     = $settings['level_file_log']       ?? self::DEFAULT_LEVEL_FILE_LOG;
      self::$levelEmailLog    = $settings['level_email_log']      ?? self::DEFAULT_LEVEL_EMAIL_LOG;
      self::$levelTelegramLog = $settings['level_telegram_log']   ?? self::DEFAULT_LEVEL_TELEGRAM_LOG;
   }
}
