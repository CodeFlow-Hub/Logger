<?php

namespace CodeFlowHub\Logger;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\TelegramBotHandler;
use Monolog\Logger as LoggerMonolog;

/**
 * Facade estatica do Monolog que centraliza configuracoes e handlers de log.
 *
 * Recursos principais:
 * - Handler de arquivo pronto para uso (`logs/file-YYYY-MM-DD.log`).
 * - Handlers opcionais de email (NativeMailer) e Telegram com niveis configuraveis.
 * - Enriquecimento automatico de contexto com request, sessao, usuario e rede.
 * - Sanitizacao recursiva de dados sensiveis antes de enviar para os handlers.
 * - Cobertura completa dos niveis PSR-3 (debug a emergency).
 *
 * Uso tipico:
 * ```php
 * Logger::settings(['dir_logs' => __DIR__ . '/../logs']);
 * Logger::enableLogByEmail('infra@app.com', 'ops@app.com');
 * Logger::info('User authenticated', ['user_id' => 42]);
 * ```
 *
 * Observacao: chame os metodos de configuracao antes da primeira escrita de log
 * para garantir que os handlers sejam anexados na inicializacao perezosa.
 *
 * @package CodeFlowHub\Logger
 * @since 2.0.0
 * @see https://www.php-fig.org/psr/psr-3/
 */
class Logger
{
   const LEVEL_DEBUG       = LoggerMonolog::DEBUG;
   const LEVEL_INFO        = LoggerMonolog::INFO;
   const LEVEL_NOTICE      = LoggerMonolog::NOTICE;
   const LEVEL_WARNING     = LoggerMonolog::WARNING;
   const LEVEL_ERROR       = LoggerMonolog::ERROR;
   const LEVEL_CRITICAL    = LoggerMonolog::CRITICAL;
   const LEVEL_ALERT       = LoggerMonolog::ALERT;
   const LEVEL_EMERGENCY   = LoggerMonolog::EMERGENCY;

   /** @var array Campos padrão considerados sensíveis para sanitização */
   const SENSITIVE_FIELDS_DEFAULT = ['password', 'token', 'secret', 'senha', 'hash'];

   /** Modo de mascaramento: substitui completamente por [redacted] */
   const MASK_MODE_REDACTED = 'redacted';

   /** Modo de mascaramento: mantém últimos 4 caracteres visíveis, substitui o resto por ******* */
   const MASK_MODE_PARTIAL = 'partial';

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
   private static $dirLogs = null;

   /** @var string Nome do arquivo de log (inclui data) */
   private static $fileLogLabel = null;

   /** @var int Nível mínimo para logs em arquivo */
   private static $levelFileLog = self::LEVEL_DEBUG;

   /** @var int Nível mínimo para logs por email */
   private static $levelEmailLog = self::LEVEL_ERROR;

   /** @var int Nível mínimo para logs via Telegram */
   private static $levelTelegramLog = self::LEVEL_CRITICAL;

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
   private static $telegramEnabled = false;

   /** @var array Campos sensíveis customizados adicionados pelo usuário */
   private static $customSensitiveFields = [];

   /** @var string Modo de mascaramento atual (padrão: REDACTED) */
   private static $maskMode = self::MASK_MODE_REDACTED;

   /** @var Exception|null Última exceção capturada no logger */
   private static $fail = null;

   // =========================================================================================
   // INICIALIZAÇÃO
   // =========================================================================================

   /**
    * Inicializa o Monolog e anexa os handlers habilitados.
    *
    * Esta rotina eh idempotente: chamadas subsequentes retornam imediatamente.
    * Handlers opcionais sao anexados apenas se os metodos de configuracao tiverem
    * sido executados antes da primeira escrita de log.
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

      // Intenção: definir diretório de logs (padrão).
      self::setLogDirectory();
      // Intenção: definir nome do arquivo de log com data atual se não fornecido.
      if (self::$fileLogLabel === null) self::$fileLogLabel = "file-" . date("Y-m-d") . ".log";

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

   /**
    * Define o diretório onde os arquivos de log serão armazenados.
    *
    * @param string|null $dir Diretório customizado (opcional).
    * @return void
    */
   private static function setLogDirectory(?string $dir = null): void
   {
      // Intenção: validar diretório customizado.
      if ($dir && is_dir($dir) && is_writable($dir))
      {
         self::$dirLogs = $dir;
         return;
      }

      // Intenção: usar diretório padrão relativo à raiz do projeto.
      $path = realpath(dirname(__DIR__, 4)) . "/logs";

      if (!is_dir($path) || !is_writable($path))
      {
         self::$fail = new Exception("Log directory is not writable: " . $path);
         return;
      }

      self::$dirLogs = $path;
   }

   /**
    * Retorna a última exceção capturada no logger, se houver.
    *
    * @return Exception|null Última exceção ou `null` se não houver falhas.
    */
   public static function fail(): ?Exception
   {
      return self::$fail;
   }

   // =========================================================================================
   // MÉTODOS PSR-3 (ORDEM CRESCENTE DE SEVERIDADE)
   // =========================================================================================

   /**
    * Método genérico para registrar mensagens de log em qualquer nível.
    *
    * @param int $level Nível de severidade (use as constantes LEVEL_*).
    * @param string $message Mensagem em inglês no presente simples.
    * @param array $context Metadados adicionais que serão sanitizados.
    * @return void
    */
   private static function toWrite(int $level, string $message, array $context = []): void
   {
      if (!$level || !$message)
      {
         self::$fail = new Exception("Log level or message is missing");
         return;
      }

      self::initialize();
      self::$engine->log($level, $message, self::buildLogContext($context));
   }

   /**
    * Registra informações detalhadas de depuração.
    *
    * @param string $message Mensagem em inglês no presente simples.
    * @param array $context Metadados adicionais que serão sanitizados.
    * @return void
    * @example logger()->debug('Database query executed', ['query' => 'SELECT * FROM users']);
    */
   public static function debug(string $message, array $context = []): void
   {
      self::toWrite(self::LEVEL_DEBUG, $message, $context);
   }

   /**
    * Registra eventos informativos e operações bem-sucedidas.
    *
    * @param string $message Mensagem em inglês no presente simples.
    * @param array $context Metadados adicionais que serão sanitizados.
    * @return void
    * @example logger()->info('User authentication started', ['user_id' => 123]);
    */
   public static function info(string $message, array $context = []): void
   {
      self::toWrite(self::LEVEL_INFO, $message, $context);
   }

   /**
    * Registra eventos normais, mas significativos.
    *
    * @param string $message Mensagem em inglês no presente simples.
    * @param array $context Metadados adicionais que serão sanitizados.
    * @return void
    * @example logger()->notice('Password changed successfully', ['user_id' => 123]);
    */
   public static function notice(string $message, array $context = []): void
   {
      self::toWrite(self::LEVEL_NOTICE, $message, $context);
   }

   /**
    * Registra avisos que podem exigir acompanhamento.
    *
    * @param string $message Mensagem em inglês no presente simples.
    * @param array $context Metadados adicionais que serão sanitizados.
    * @return void
    * @example logger()->warning('Validation failed', ['field' => 'email']);
    */
   public static function warning(string $message, array $context = []): void
   {
      self::toWrite(self::LEVEL_WARNING, $message, $context);
   }

   /**
    * Registra erros que disparam notificações quando configuradas.
    *
    * @param string $message Mensagem em inglês no presente simples.
    * @param array $context Metadados adicionais que serão sanitizados.
    * @return void
    * @example logger()->error('Failed to save user', ['user_id' => 123]);
    */
   public static function error(string $message, array $context = []): void
   {
      self::toWrite(self::LEVEL_ERROR, $message, $context);
   }

   /**
    * Registra falhas críticas que demandam intervenção imediata.
    *
    * @param string $message Mensagem em inglês no presente simples.
    * @param array $context Metadados adicionais que serão sanitizados.
    * @return void
    * @example logger()->critical('Cache system failure', ['cache' => 'redis']);
    */
   public static function critical(string $message, array $context = []): void
   {
      self::toWrite(self::LEVEL_CRITICAL, $message, $context);
   }

   /**
    * Registra alertas que exigem ação imediata.
    *
    * @param string $message Mensagem em inglês no presente simples.
    * @param array $context Metadados adicionais que serão sanitizados.
    * @return void
    * @example logger()->alert('Memory usage above threshold', ['current' => '96%']);
    */
   public static function alert(string $message, array $context = []): void
   {
      self::toWrite(self::LEVEL_ALERT, $message, $context);
   }

   /**
    * Registra emergências que indicam indisponibilidade total.
    *
    * @param string $message Mensagem em inglês no presente simples.
    * @param array $context Metadados adicionais que serão sanitizados.
    * @return void
    * @example logger()->emergency('Database server unreachable', ['host' => 'db']);
    */
   public static function emergency(string $message, array $context = []): void
   {
      self::toWrite(self::LEVEL_EMERGENCY, $message, $context);
   }

   // =========================================================================================
   // MÉTODOS DE CONFIGURAÇÃO
   // =========================================================================================

   /**
    * Habilita envio de notificacoes por email para mensagens `ERROR+`.
    *
    * Chame este metodo antes de gerar o primeiro log do ciclo para que o
    * handler NativeMailer seja anexado em {@see self::initialize()}.
    *
    * @param string $senderEmail Endereco remetente das notificacoes.
    * @param string $recipientEmail Endereco destinatario das notificacoes.
    * @param string|null $subject Assunto customizado (padrao: "Erro detectado no sistema").
    * @return void
    */
   public static function enableLogByEmail(string $senderEmail, string $recipientEmail, ?string $subject = null): void
   {
      if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL))
      {
         self::$fail = new Exception("Invalid email address provided for logging.");
         return;
      }

      // Intenção: configurar parâmetros para envio de notificações por email.
      self::$senderEmail = $senderEmail;
      self::$recipientEmail = $recipientEmail;
      self::$subject = $subject ?? "Erro detectado no sistema";
      self::$emailEnabled = true;
   }

   /**
    * Habilita envio de notificacoes via Telegram para mensagens `ERROR+`.
    *
    * Configure antes da primeira escrita de log para garantir que o handler
    * {@see TelegramBotHandler} seja registrado na inicializacao.
    *
    * @param string $botToken Token do bot do Telegram (BotFather).
    * @param string $chatId Chat ou canal que recebera as mensagens.
    * @return void
    */
   public static function enableLogByTelegram(string $botToken, string $chatId): void
   {
      if (empty($botToken) || empty($chatId))
      {
         self::$fail = new Exception("Invalid Telegram bot token or chat ID provided for logging.");
         return;
      }

      // Intenção: configurar parâmetros para envio de notificações por Telegram.
      self::$telegramBotToken = $botToken;
      self::$telegramChatId = $chatId;
      self::$telegramEnabled = true;
   }

   /**
    * Adiciona campos sensíveis customizados que serão sanitizados automaticamente.
    *
    * Os campos adicionados são combinados com os campos padrão (password, token, secret, senha, hash).
    * Campos duplicados são automaticamente removidos. A verificação é case-insensitive.
    *
    * @param array $fields Array de strings com nomes de campos sensíveis a serem adicionados.
    * @return void
    * @example Logger::addSensitiveFields(['credit_card', 'ssn', 'api_key']);
    */
   public static function addSensitiveFields(array $fields): void
   {
      // Intenção: validar e filtrar apenas strings não vazias.
      $validFields = array_filter($fields, function ($field) {
         return is_string($field) && !empty(trim($field));
      });

      if (empty($validFields))
      {
         return;
      }

      // Intenção: normalizar campos para lowercase para comparação case-insensitive.
      $normalizedFields = array_map('strtolower', $validFields);

      // Intenção: mesclar campos customizados com os já existentes, removendo duplicatas.
      self::$customSensitiveFields = array_values(array_unique(
         array_merge(self::$customSensitiveFields, $normalizedFields)
      ));
   }

   /**
    * Configura o modo de mascaramento para campos sensíveis.
    *
    * Modos disponíveis:
    * - MASK_MODE_REDACTED: Substitui completamente por [redacted] (padrão)
    * - MASK_MODE_PARTIAL: Mantém últimos 4 caracteres visíveis, resto por *******
    *
    * @param string $mode Modo de mascaramento (use as constantes MASK_MODE_*).
    * @return void
    * @example Logger::setMaskMode(Logger::MASK_MODE_PARTIAL);
    */
   public static function setMaskMode(string $mode): void
   {
      // Intenção: validar que o modo informado é válido.
      $validModes = [self::MASK_MODE_REDACTED, self::MASK_MODE_PARTIAL];

      if (!in_array($mode, $validModes, true))
      {
         self::$fail = new Exception("Invalid mask mode. Use MASK_MODE_REDACTED or MASK_MODE_PARTIAL.");
         return;
      }

      // Intenção: definir modo de mascaramento.
      self::$maskMode = $mode;
   }

   // =========================================================================================
   // MÉTODOS PRIVADOS (HELPERS)
   // =========================================================================================

   /**
    * Retorna array com todos os campos sensíveis (padrão + customizados).
    *
    * Combina os campos padrão definidos na constante SENSITIVE_FIELDS_DEFAULT
    * com os campos customizados adicionados via addSensitiveFields().
    *
    * @return array Array de campos sensíveis em lowercase para verificação case-insensitive.
    */
   private static function getSensitiveFields(): array
   {
      // Intenção: mesclar campos padrão com campos customizados, garantindo valores únicos.
      return array_values(array_unique(
         array_merge(
            array_map('strtolower', self::SENSITIVE_FIELDS_DEFAULT),
            self::$customSensitiveFields
         )
      ));
   }

   /**
    * Aplica mascaramento ao valor conforme o modo configurado.
    *
    * @param mixed $value Valor original a ser mascarado.
    * @return string Valor mascarado conforme modo configurado.
    */
   private static function applyMask($value): string
   {
      // Intenção: converter valor para string se necessário.
      $stringValue = is_string($value) ? $value : (string) $value;

      // Intenção: aplicar mascaramento conforme modo configurado.
      if (self::$maskMode === self::MASK_MODE_PARTIAL)
      {
         // Modo parcial: mantém últimos 4 caracteres, resto substitui por *******
         $length = mb_strlen($stringValue);

         if ($length <= 4)
         {
            // Se valor tem 4 ou menos caracteres, mascarar tudo.
            return '*******';
         }

         // Manter últimos 4 caracteres visíveis.
         $lastFour = mb_substr($stringValue, -4);
         return '*******' . $lastFour;
      }

      // Modo padrão: REDACTED - substitui completamente.
      return '[redacted]';
   }

   /**
    * Gera ou reaproveita o identificador único da requisição atual.
    *
    * @return string Request ID com prefixo `req_`.
    */
   private static function generateRequestId(): string
   {
      // Intenção: reutilizar request ID existente para manter rastreabilidade.
      if (self::$requestId)
      {
         return self::$requestId;
      }

      // Intenção: gerar novo ID único para esta requisição.
      self::$requestId = uniqid('req_', true);
      return self::$requestId;
   }

   /**
    * Monta o contexto padrão enriquecido com os metadados informados pelo usuário.
    *
    * @param array $context Dados adicionais fornecidos pelo usuário.
    * @return array Contexto estruturado já sanitizado.
    */
   private static function buildLogContext(array $context = []): array
   {
      // Intenção: criar contexto base automático com dados da requisição HTTP.
      $baseContext = [
         'request_id'    => self::generateRequestId(),
         'session_id'    => (session_status() === PHP_SESSION_ACTIVE ? session_id() : null),
         'ip_address'    => $_SERVER['REMOTE_ADDR']      ?? 'unknown',
         'user_agent'    => $_SERVER['HTTP_USER_AGENT']  ?? 'unknown',
      ];

      // Intenção: mesclar contexto base com dados adicionais sanitizados.
      return array_merge($baseContext, self::sanitizeLogParams($context));
   }

   /**
    * Sanitiza valores sensíveis e normaliza o payload do log.
    *
    * @param array $params Dados originais a serem sanitizados.
    * @return array|null Dados limpos ou `null` quando vazios.
    */
   private static function sanitizeLogParams(array $params = []): array
   {
      // Intenção: early return para arrays vazios.
      if (empty($params))
      {
         return [];
      }

      $sanitized = [];

      // Intenção: obter lista de campos sensíveis uma única vez para otimizar performance.
      $sensitiveFields = self::getSensitiveFields();

      foreach ($params as $key => $value)
      {
         $lowerKey = is_string($key) ? strtolower($key) : '';

         // Intenção: detectar e mascarar campos sensíveis (senhas, tokens, secrets).
         $isSensitive = false;

         foreach ($sensitiveFields as $sensitiveField)
         {
            if (strpos($lowerKey, $sensitiveField) !== false)
            {
               $isSensitive = true;
               break;
            }
         }

         if ($isSensitive)
         {
            // Intenção: aplicar mascaramento conforme modo configurado.
            if (is_scalar($value) || $value === null)
            {
               $sanitized[$key] = self::applyMask($value);
            }
            else
            {
               // Para valores não escalares, usar mascaramento padrão.
               $sanitized[$key] = '[redacted]';
            }
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
    * Ajusta diretorio, rotulos e niveis padrao do logger.
    *
    * As configuracoes devem ser aplicadas antes da primeira escrita de log para
    * que os handlers criados em {@see self::initialize()} reflitam os valores.
    *
    * @param array $settings Chaves suportadas: dir_logs, file_log_label, level_file_log,
    *                        level_email_log e level_telegram_log.
    * @return void
    */
   public static function settings(array $settings = []): void
   {
      // Intenção: garantir que logger está inicializado antes de registrar.
      self::initialize();
      // Intenção: definir diretório de logs customizado se fornecido.
      self::setLogDirectory($settings['dir_logs'] ?? null);

      // Intenção: aplicar configurações fornecidas ou manter valores atuais.
      self::$fileLogLabel     = $settings['file_log_label']       ?? self::$fileLogLabel;
      self::$levelFileLog     = $settings['level_file_log']       ?? self::LEVEL_DEBUG;
      self::$levelEmailLog    = $settings['level_email_log']      ?? self::LEVEL_ERROR;
      self::$levelTelegramLog = $settings['level_telegram_log']   ?? self::LEVEL_CRITICAL;
   }
}
