<?php

namespace CodeFlowHub\Logger;

use Exception;

/**
 * Builder de configuração do Logger com interface fluente.
 *
 * Permite configuração expressiva e encadeada do Logger antes da aplicação.
 * Todas as configurações são validadas antes de serem aplicadas.
 *
 * @package CodeFlowHub\Logger
 * @since 1.0.10
 */
class LoggerConfiguration
{
   /** @var string|null Diretório onde os arquivos de log serão armazenados */
   private $logDirectory = null;

   /** @var string|null Nome do arquivo de log (inclui data) */
   private $fileLogLabel = null;

   /** @var int Nível mínimo para logs em arquivo */
   private $levelFileLog = Logger::LEVEL_DEBUG;

   /** @var int Nível mínimo para logs por email */
   private $levelEmailLog = Logger::LEVEL_ERROR;

   /** @var int Nível mínimo para logs via Telegram */
   private $levelTelegramLog = Logger::LEVEL_CRITICAL;

   /** @var string|null Email remetente para notificações */
   private $senderEmail = null;

   /** @var string|null Email destinatário para notificações */
   private $recipientEmail = null;

   /** @var string|null Assunto dos emails de notificação */
   private $subject = null;

   /** @var bool Flag de habilitação de notificações por email */
   private $emailEnabled = false;

   /** @var string|null Token do bot do Telegram */
   private $telegramBotToken = null;

   /** @var string|null ID do chat/canal do Telegram */
   private $telegramChatId = null;

   /** @var bool Flag de habilitação de notificações por Telegram */
   private $telegramEnabled = false;

   /** @var array Campos sensíveis customizados adicionados pelo usuário */
   private $customSensitiveFields = [];

   /** @var string Modo de mascaramento atual (padrão: REDACTED) */
   private $maskMode = Logger::MASK_MODE_REDACTED;

   /** @var Exception|null Última exceção capturada durante configuração */
   private $lastError = null;

   /**
    * Descarta erros de validação herdados ao copiar uma configuração.
    *
    * Usado por {@see Logger::configure()}, que parte de uma cópia da configuração atual.
    *
    * @return void
    */
   public function __clone()
   {
      $this->lastError = null;
   }

   /**
    * Define o diretório onde os arquivos de log serão armazenados.
    *
    * @param string $directory Diretório para logs.
    * @return self
    */
   public function setLogDirectory(string $directory): self
   {
      // Intenção: validar que o diretório existe e é gravável.
      if (!is_dir($directory))
      {
         $this->lastError = new Exception("Log directory does not exist: {$directory}");
         return $this;
      }

      if (!is_writable($directory))
      {
         $this->lastError = new Exception("Log directory is not writable: {$directory}");
         return $this;
      }

      $this->logDirectory = $directory;
      return $this;
   }

   /**
    * Define o nome do arquivo de log.
    *
    * @param string $label Nome do arquivo de log (ex: 'app-2024-01-15.log').
    * @return self
    */
   public function setFileLogLabel(string $label): self
   {
      if (empty(trim($label)))
      {
         $this->lastError = new Exception("File log label cannot be empty");
         return $this;
      }

      $this->fileLogLabel = $label;
      return $this;
   }

   /**
    * Define o nível mínimo para logs em arquivo.
    *
    * @param int $level Nível mínimo (use as constantes Logger::LEVEL_*).
    * @return self
    */
   public function setLevelFileLog(int $level): self
   {
      // Intenção: validar que o nível é válido.
      $validLevels = [
         Logger::LEVEL_DEBUG,
         Logger::LEVEL_INFO,
         Logger::LEVEL_NOTICE,
         Logger::LEVEL_WARNING,
         Logger::LEVEL_ERROR,
         Logger::LEVEL_CRITICAL,
         Logger::LEVEL_ALERT,
         Logger::LEVEL_EMERGENCY
      ];

      if (!in_array($level, $validLevels, true))
      {
         $this->lastError = new Exception("Invalid log level for file log: {$level}");
         return $this;
      }

      $this->levelFileLog = $level;
      return $this;
   }

   /**
    * Define o nível mínimo para logs por email.
    *
    * @param int $level Nível mínimo (use as constantes Logger::LEVEL_*).
    * @return self
    */
   public function setLevelEmailLog(int $level): self
   {
      $validLevels = [
         Logger::LEVEL_DEBUG,
         Logger::LEVEL_INFO,
         Logger::LEVEL_NOTICE,
         Logger::LEVEL_WARNING,
         Logger::LEVEL_ERROR,
         Logger::LEVEL_CRITICAL,
         Logger::LEVEL_ALERT,
         Logger::LEVEL_EMERGENCY
      ];

      if (!in_array($level, $validLevels, true))
      {
         $this->lastError = new Exception("Invalid log level for email log: {$level}");
         return $this;
      }

      $this->levelEmailLog = $level;
      return $this;
   }

   /**
    * Define o nível mínimo para logs via Telegram.
    *
    * @param int $level Nível mínimo (use as constantes Logger::LEVEL_*).
    * @return self
    */
   public function setLevelTelegramLog(int $level): self
   {
      $validLevels = [
         Logger::LEVEL_DEBUG,
         Logger::LEVEL_INFO,
         Logger::LEVEL_NOTICE,
         Logger::LEVEL_WARNING,
         Logger::LEVEL_ERROR,
         Logger::LEVEL_CRITICAL,
         Logger::LEVEL_ALERT,
         Logger::LEVEL_EMERGENCY
      ];

      if (!in_array($level, $validLevels, true))
      {
         $this->lastError = new Exception("Invalid log level for telegram log: {$level}");
         return $this;
      }

      $this->levelTelegramLog = $level;
      return $this;
   }

   /**
    * Habilita envio de notificações por email.
    *
    * @param string $senderEmail Email remetente.
    * @param string $recipientEmail Email destinatário.
    * @param string|null $subject Assunto customizado (padrão: "Erro detectado no sistema").
    * @return self
    */
   public function enableEmail(string $senderEmail, string $recipientEmail, ?string $subject = null): self
   {
      // Intenção: validar emails antes de habilitar.
      if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL))
      {
         $this->lastError = new Exception("Invalid sender email address: {$senderEmail}");
         return $this;
      }

      if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL))
      {
         $this->lastError = new Exception("Invalid recipient email address: {$recipientEmail}");
         return $this;
      }

      $this->senderEmail = $senderEmail;
      $this->recipientEmail = $recipientEmail;
      $this->subject = $subject ?? "Erro detectado no sistema";
      $this->emailEnabled = true;
      return $this;
   }

   /**
    * Desabilita envio de notificações por email.
    *
    * @return self
    */
   public function disableEmail(): self
   {
      $this->emailEnabled = false;
      $this->senderEmail = null;
      $this->recipientEmail = null;
      $this->subject = null;
      return $this;
   }

   /**
    * Habilita envio de notificações via Telegram.
    *
    * @param string $botToken Token do bot do Telegram (BotFather).
    * @param string $chatId Chat ou canal que receberá as mensagens.
    * @return self
    */
   public function enableTelegram(string $botToken, string $chatId): self
   {
      // Intenção: validar token e chat ID antes de habilitar.
      if (empty(trim($botToken)))
      {
         $this->lastError = new Exception("Telegram bot token cannot be empty");
         return $this;
      }

      if (empty(trim($chatId)))
      {
         $this->lastError = new Exception("Telegram chat ID cannot be empty");
         return $this;
      }

      $this->telegramBotToken = $botToken;
      $this->telegramChatId = $chatId;
      $this->telegramEnabled = true;
      return $this;
   }

   /**
    * Desabilita envio de notificações via Telegram.
    *
    * @return self
    */
   public function disableTelegram(): self
   {
      $this->telegramEnabled = false;
      $this->telegramBotToken = null;
      $this->telegramChatId = null;
      return $this;
   }

   /**
    * Adiciona campos sensíveis customizados que serão sanitizados automaticamente.
    *
    * @param array $fields Array de strings com nomes de campos sensíveis.
    * @return self
    */
   public function addSensitiveFields(array $fields): self
   {
      // Intenção: validar e filtrar apenas strings não vazias.
      $validFields = array_filter($fields, function ($field) {
         return is_string($field) && !empty(trim($field));
      });

      if (empty($validFields))
      {
         return $this;
      }

      // Intenção: normalizar campos para lowercase para comparação case-insensitive.
      $normalizedFields = array_map('strtolower', $validFields);

      // Intenção: mesclar campos customizados com os já existentes, removendo duplicatas.
      $this->customSensitiveFields = array_values(array_unique(
         array_merge($this->customSensitiveFields, $normalizedFields)
      ));

      return $this;
   }

   /**
    * Configura o modo de mascaramento para campos sensíveis.
    *
    * @param string $mode Modo de mascaramento (use Logger::MASK_MODE_*).
    * @return self
    */
   public function setMaskMode(string $mode): self
   {
      // Intenção: validar que o modo informado é válido.
      $validModes = [Logger::MASK_MODE_REDACTED, Logger::MASK_MODE_PARTIAL];

      if (!in_array($mode, $validModes, true))
      {
         $this->lastError = new Exception("Invalid mask mode. Use MASK_MODE_REDACTED or MASK_MODE_PARTIAL.");
         return $this;
      }

      $this->maskMode = $mode;
      return $this;
   }

   /**
    * Aplica a configuração ao Logger.
    *
    * Todas as configurações definidas são aplicadas e o logger é reinicializado
    * se já estiver inicializado, permitindo reconfiguração dinâmica.
    *
    * @return void
    * @throws Exception Se houver erros de validação durante a configuração.
    */
   public function apply(): void
   {
      // Intenção: verificar se há erros pendentes antes de aplicar.
      if ($this->lastError !== null)
      {
         throw $this->lastError;
      }

      // Intenção: aplicar todas as configurações ao Logger.
      Logger::applyConfiguration($this);
   }

   /**
    * Retorna a última exceção capturada durante a configuração, se houver.
    *
    * @return Exception|null Última exceção ou `null` se não houver falhas.
    */
   public function getLastError(): ?Exception
   {
      return $this->lastError;
   }

   // =========================================================================================
   // GETTERS (usados internamente pelo Logger)
   // =========================================================================================

   /** @return string|null */
   public function getLogDirectory(): ?string
   {
      return $this->logDirectory;
   }

   /** @return string|null */
   public function getFileLogLabel(): ?string
   {
      return $this->fileLogLabel;
   }

   /** @return int */
   public function getLevelFileLog(): int
   {
      return $this->levelFileLog;
   }

   /** @return int */
   public function getLevelEmailLog(): int
   {
      return $this->levelEmailLog;
   }

   /** @return int */
   public function getLevelTelegramLog(): int
   {
      return $this->levelTelegramLog;
   }

   /** @return string|null */
   public function getSenderEmail(): ?string
   {
      return $this->senderEmail;
   }

   /** @return string|null */
   public function getRecipientEmail(): ?string
   {
      return $this->recipientEmail;
   }

   /** @return string|null */
   public function getSubject(): ?string
   {
      return $this->subject;
   }

   /** @return bool */
   public function isEmailEnabled(): bool
   {
      return $this->emailEnabled;
   }

   /** @return string|null */
   public function getTelegramBotToken(): ?string
   {
      return $this->telegramBotToken;
   }

   /** @return string|null */
   public function getTelegramChatId(): ?string
   {
      return $this->telegramChatId;
   }

   /** @return bool */
   public function isTelegramEnabled(): bool
   {
      return $this->telegramEnabled;
   }

   /** @return array */
   public function getCustomSensitiveFields(): array
   {
      return $this->customSensitiveFields;
   }

   /** @return string */
   public function getMaskMode(): string
   {
      return $this->maskMode;
   }
}


