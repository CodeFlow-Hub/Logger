<?php

namespace CodeFlowHub\Logger\Tests;

use CodeFlowHub\Logger\Logger;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Suite de testes para a classe Logger.
 *
 * Esta classe contém testes unitários que verificam o funcionamento correto
 * de todos os métodos e funcionalidades da classe Logger, incluindo:
 * - Instanciação e métodos básicos de logging
 * - Sanitização de dados sensíveis
 * - Configuração de email e Telegram
 * - Campos sensíveis customizados
 * - Modos de mascaramento (redacted e partial)
 * - Truncamento de strings longas
 * - Geração de request ID
 *
 * @package CodeFlowHub\Logger\Tests
 * @since 2.0.0
 */
class LoggerTest extends TestCase
{
   /** @var vfsStreamDirectory Sistema de arquivos virtual para testes */
   private vfsStreamDirectory $fileSystem;

   /**
    * Configura o ambiente de teste antes de cada método de teste.
    *
    * Inicializa um sistema de arquivos virtual e reseta o estado estático
    * da classe Logger para garantir isolamento entre os testes.
    *
    * @return void
    */
   protected function setUp(): void
   {
      // Setup virtual file system para testes
      $this->fileSystem = vfsStream::setup('logs');

      // Reset logger state para cada teste
      $reflection = new \ReflectionClass(Logger::class);
      $properties = ['engine', 'initialized', 'requestId', 'configuration', 'fail', 'customSensitiveFields', 'maskMode'];

      foreach ($properties as $property)
      {
         $prop = $reflection->getProperty($property);
         $prop->setAccessible(true);
         if ($property === 'initialized')
         {
            $prop->setValue(null, false);
         }
         elseif ($property === 'customSensitiveFields')
         {
            $prop->setValue(null, []);
         }
         elseif ($property === 'maskMode')
         {
            $prop->setValue(null, Logger::MASK_MODE_REDACTED);
         }
         else
         {
            $prop->setValue(null, null);
         }
      }
   }

   /**
    * Testa se a classe Logger pode ser instanciada corretamente.
    *
    * @return void
    */
   public function testLoggerCanBeInstantiated(): void
   {
      $logger = new Logger();
      $this->assertInstanceOf(Logger::class, $logger);
   }

   /**
    * Testa se todos os métodos básicos de logging podem ser chamados sem erros.
    *
    * Verifica que os métodos debug, info, warning, error, critical, alert e emergency
    * podem ser executados sem lançar exceções.
    *
    * @return void
    */
   public function testBasicLoggingMethods(): void
   {
      // Testa se os métodos de logging não geram erros
      $this->expectNotToPerformAssertions();

      Logger::debug('Test debug message', ['key' => 'value']);
      Logger::info('Test info message', ['key' => 'value']);
      Logger::warning('Test warning message', ['key' => 'value']);
      Logger::error('Test error message', ['key' => 'value']);
      Logger::critical('Test critical message', ['key' => 'value']);
      Logger::alert('Test alert message', ['key' => 'value']);
      Logger::emergency('Test emergency message', ['key' => 'value']);
   }

   /**
    * Testa a sanitização de dados sensíveis.
    *
    * Verifica que campos sensíveis padrão (password, api_token, user_secret)
    * são mascarados como [redacted], enquanto campos normais permanecem intactos.
    *
    * @return void
    */
   public function testDataSanitization(): void
   {
      $reflection = new \ReflectionClass(Logger::class);
      $method = $reflection->getMethod('sanitizeLogParams');
      $method->setAccessible(true);

      $sensitiveData = [
         'email' => 'test@example.com',
         'password' => 'secret123',
         'api_token' => 'abc123xyz',
         'user_secret' => 'topsecret',
         'normal_field' => 'normal_value'
      ];

      $result = $method->invoke(null, $sensitiveData);

      $this->assertEquals('test@example.com', $result['email']);
      $this->assertEquals('[redacted]', $result['password']);
      $this->assertEquals('[redacted]', $result['api_token']);
      $this->assertEquals('[redacted]', $result['user_secret']);
      $this->assertEquals('normal_value', $result['normal_field']);
   }

   /**
    * Testa o truncamento de strings longas.
    *
    * Verifica que strings com mais de 120 caracteres são truncadas
    * para o limite máximo de 120 caracteres.
    *
    * @return void
    */
   public function testStringTruncation(): void
   {
      $reflection = new \ReflectionClass(Logger::class);
      $method = $reflection->getMethod('sanitizeLogParams');
      $method->setAccessible(true);

      $longString = str_repeat('a', 150);
      $data = ['long_text' => $longString];

      $result = $method->invoke(null, $data);

      $this->assertEquals(120, mb_strlen($result['long_text']));
   }

   /**
    * Testa a geração e persistência do Request ID.
    *
    * Verifica que o mesmo Request ID é retornado em múltiplas chamadas
    * durante a mesma requisição e que o ID começa com o prefixo 'req_'.
    *
    * @return void
    */
   public function testRequestIdGeneration(): void
   {
      $reflection = new \ReflectionClass(Logger::class);
      $method = $reflection->getMethod('generateRequestId');
      $method->setAccessible(true);

      $requestId1 = $method->invoke(null);
      $requestId2 = $method->invoke(null);

      // Deve retornar o mesmo ID na mesma requisição
      $this->assertEquals($requestId1, $requestId2);
      $this->assertStringStartsWith('req_', $requestId1);
   }

   /**
    * Testa a configuração de notificações por email.
    *
    * Verifica que os parâmetros de email (remetente, destinatário, assunto)
    * são configurados corretamente e que a flag de habilitação é ativada.
    *
    * @return void
    */
   public function testEmailConfiguration(): void
   {
      Logger::enableLogByEmail(
         'from@example.com',
         'to@example.com',
         'Test Subject'
      );

      $config = $this->currentConfiguration();

      $this->assertEquals('from@example.com', $config->getSenderEmail());
      $this->assertEquals('to@example.com', $config->getRecipientEmail());
      $this->assertEquals('Test Subject', $config->getSubject());
      $this->assertTrue($config->isEmailEnabled());
   }

   /**
    * Testa a configuração de notificações via Telegram.
    *
    * Verifica que os parâmetros do Telegram (bot token, chat ID)
    * são configurados corretamente e que a flag de habilitação é ativada.
    *
    * @return void
    */
   public function testTelegramConfiguration(): void
   {
      Logger::enableLogByTelegram('bot_token', 'chat_id');

      $config = $this->currentConfiguration();

      $this->assertEquals('bot_token', $config->getTelegramBotToken());
      $this->assertEquals('chat_id', $config->getTelegramChatId());
      $this->assertTrue($config->isTelegramEnabled());
   }

   /**
    * Testa se o nível configurado via settings() é respeitado pelo handler de arquivo.
    *
    * Regressão: settings() inicializava o handler com DEBUG antes de ler o nível
    * informado, e o nível configurado nunca era aplicado.
    *
    * @return void
    */
   public function testSettingsAppliesFileLogLevel(): void
   {
      Logger::settings([
         'dir_logs'       => $this->fileSystem->url(),
         'file_log_label' => 'app.log',
         'level_file_log' => Logger::LEVEL_INFO,
      ]);

      Logger::debug('Debug message');
      Logger::info('Info message');

      $content = $this->fileSystem->getChild('app.log')->getContent();
      $this->assertStringNotContainsString('Debug message', $content);
      $this->assertStringContainsString('Info message', $content);
   }

   /**
    * Testa se settings() reconfigura o handler quando chamado após a primeira escrita.
    *
    * @return void
    */
   public function testSettingsReconfiguresAfterFirstLog(): void
   {
      Logger::settings(['dir_logs' => $this->fileSystem->url(), 'file_log_label' => 'app.log']);
      Logger::debug('Debug before settings');

      Logger::settings(['level_file_log' => Logger::LEVEL_WARNING]);
      Logger::info('Info after settings');
      Logger::warning('Warning after settings');

      $content = $this->fileSystem->getChild('app.log')->getContent();
      $this->assertStringContainsString('Debug before settings', $content);
      $this->assertStringNotContainsString('Info after settings', $content);
      $this->assertStringContainsString('Warning after settings', $content);
   }

   /**
    * Testa se configure() preserva notificações habilitadas antes da configuração fluente.
    *
    * @return void
    */
   public function testConfigurePreservesPreviouslyEnabledTelegram(): void
   {
      Logger::enableLogByTelegram('bot_token', 'chat_id');
      Logger::configure()->setLevelFileLog(Logger::LEVEL_INFO)->apply();

      $config = $this->currentConfiguration();
      $this->assertTrue($config->isTelegramEnabled());
      $this->assertEquals(Logger::LEVEL_INFO, $config->getLevelFileLog());
   }

   /**
    * Testa se settings() registra erro de validação em fail() sem lançar exceção.
    *
    * @return void
    */
   public function testSettingsReportsInvalidDirectoryThroughFail(): void
   {
      Logger::settings(['dir_logs' => $this->fileSystem->url() . '/missing']);

      $this->assertNotNull(Logger::fail());
      $this->assertStringContainsString('does not exist', Logger::fail()->getMessage());
   }

   /**
    * Retorna a configuração atualmente armazenada no Logger.
    *
    * @return \CodeFlowHub\Logger\LoggerConfiguration
    */
   private function currentConfiguration(): \CodeFlowHub\Logger\LoggerConfiguration
   {
      $prop = (new \ReflectionClass(Logger::class))->getProperty('configuration');
      $prop->setAccessible(true);

      return $prop->getValue();
   }

   /**
    * Testa a função helper logger() se estiver disponível.
    *
    * Verifica que a função helper retorna uma instância válida da classe Logger.
    * O teste é pulado se a função helper não estiver carregada.
    *
    * @return void
    */
   public function testHelperFunction(): void
   {
      if (function_exists('logger'))
      {
         $loggerInstance = logger();
         $this->assertInstanceOf(Logger::class, $loggerInstance);
      }
      else
      {
         $this->markTestSkipped('Helper function not loaded');
      }
   }

   /**
    * Testa a adição de campos sensíveis customizados.
    *
    * Verifica que campos sensíveis customizados podem ser adicionados
    * e são armazenados corretamente na lista de campos sensíveis.
    *
    * @return void
    */
   public function testAddSensitiveFields(): void
   {
      // Testa adição de campos sensíveis customizados
      Logger::addSensitiveFields(['credit_card', 'ssn', 'api_key']);

      $reflection = new \ReflectionClass(Logger::class);
      $customFieldsProp = $reflection->getProperty('customSensitiveFields');
      $customFieldsProp->setAccessible(true);

      $customFields = $customFieldsProp->getValue();
      
      $this->assertContains('credit_card', $customFields);
      $this->assertContains('ssn', $customFields);
      $this->assertContains('api_key', $customFields);
   }

   /**
    * Testa que campos sensíveis são normalizados para lowercase.
    *
    * Verifica que campos adicionados com diferentes casos (maiúsculas/minúsculas)
    * são normalizados para lowercase para comparação case-insensitive.
    *
    * @return void
    */
   public function testAddSensitiveFieldsCaseInsensitive(): void
   {
      // Testa que campos são normalizados para lowercase
      Logger::addSensitiveFields(['CreditCard', 'SSN', 'API_KEY']);

      $reflection = new \ReflectionClass(Logger::class);
      $customFieldsProp = $reflection->getProperty('customSensitiveFields');
      $customFieldsProp->setAccessible(true);

      $customFields = $customFieldsProp->getValue();
      
      $this->assertContains('creditcard', $customFields);
      $this->assertContains('ssn', $customFields);
      $this->assertContains('api_key', $customFields);
   }

   /**
    * Testa que campos duplicados são removidos automaticamente.
    *
    * Verifica que ao adicionar os mesmos campos múltiplas vezes,
    * apenas valores únicos são mantidos na lista.
    *
    * @return void
    */
   public function testAddSensitiveFieldsRemovesDuplicates(): void
   {
      // Adiciona campos duplicados
      Logger::addSensitiveFields(['credit_card', 'ssn']);
      Logger::addSensitiveFields(['credit_card', 'ssn']);

      $reflection = new \ReflectionClass(Logger::class);
      $customFieldsProp = $reflection->getProperty('customSensitiveFields');
      $customFieldsProp->setAccessible(true);

      $customFields = $customFieldsProp->getValue();
      
      // Deve ter apenas valores únicos
      $this->assertCount(2, $customFields);
      $this->assertEquals(array_unique($customFields), $customFields);
   }

   /**
    * Testa que campos sensíveis customizados são sanitizados corretamente.
    *
    * Verifica que campos customizados adicionados via addSensitiveFields()
    * são mascarados da mesma forma que os campos padrão.
    *
    * @return void
    */
   public function testCustomSensitiveFieldsAreSanitized(): void
   {
      // Adiciona campos customizados
      Logger::addSensitiveFields(['credit_card', 'ssn']);

      $reflection = new \ReflectionClass(Logger::class);
      $method = $reflection->getMethod('sanitizeLogParams');
      $method->setAccessible(true);

      $sensitiveData = [
         'email' => 'test@example.com',
         'password' => 'secret123',           // Campo padrão
         'credit_card' => '1234-5678-9012',  // Campo customizado
         'ssn' => '123-45-6789',             // Campo customizado
         'normal_field' => 'normal_value'
      ];

      $result = $method->invoke(null, $sensitiveData);

      $this->assertEquals('test@example.com', $result['email']);
      $this->assertEquals('[redacted]', $result['password']);
      $this->assertEquals('[redacted]', $result['credit_card']);
      $this->assertEquals('[redacted]', $result['ssn']);
      $this->assertEquals('normal_value', $result['normal_field']);
   }

   /**
    * Testa sanitização de campos sensíveis em estruturas aninhadas.
    *
    * Verifica que campos sensíveis customizados são detectados e sanitizados
    * mesmo quando aparecem em chaves aninhadas dentro de arrays.
    *
    * @return void
    */
   public function testCustomSensitiveFieldsWithNestedKeys(): void
   {
      // Adiciona campo customizado que pode aparecer em chaves aninhadas
      Logger::addSensitiveFields(['credit_card']);

      $reflection = new \ReflectionClass(Logger::class);
      $method = $reflection->getMethod('sanitizeLogParams');
      $method->setAccessible(true);

      $sensitiveData = [
         'user_data' => [
            'name' => 'John Doe',
            'credit_card_number' => '1234-5678-9012',  // Deve ser sanitizado
            'email' => 'john@example.com'
         ]
      ];

      $result = $method->invoke(null, $sensitiveData);

      $this->assertEquals('John Doe', $result['user_data']['name']);
      $this->assertEquals('[redacted]', $result['user_data']['credit_card_number']);
      $this->assertEquals('john@example.com', $result['user_data']['email']);
   }

   /**
    * Testa que valores inválidos são filtrados ao adicionar campos sensíveis.
    *
    * Verifica que strings vazias, valores nulos, números e outros tipos inválidos
    * são ignorados ao adicionar campos sensíveis customizados.
    *
    * @return void
    */
   public function testAddSensitiveFieldsFiltersInvalidValues(): void
   {
      // Tenta adicionar valores inválidos
      Logger::addSensitiveFields(['valid_field', '', '   ', 123, null, 'another_valid']);

      $reflection = new \ReflectionClass(Logger::class);
      $customFieldsProp = $reflection->getProperty('customSensitiveFields');
      $customFieldsProp->setAccessible(true);

      $customFields = $customFieldsProp->getValue();
      
      // Deve conter apenas strings válidas
      $this->assertContains('valid_field', $customFields);
      $this->assertContains('another_valid', $customFields);
      $this->assertNotContains('', $customFields);
      $this->assertNotContains(123, $customFields);
   }

   /**
    * Testa que o modo de mascaramento padrão é REDACTED.
    *
    * Verifica que o modo de mascaramento inicial é MASK_MODE_REDACTED,
    * que substitui completamente os valores sensíveis por [redacted].
    *
    * @return void
    */
   public function testDefaultMaskModeIsRedacted(): void
   {
      $reflection = new \ReflectionClass(Logger::class);
      $maskModeProp = $reflection->getProperty('maskMode');
      $maskModeProp->setAccessible(true);

      $maskMode = $maskModeProp->getValue();
      
      $this->assertEquals(Logger::MASK_MODE_REDACTED, $maskMode);
   }

   /**
    * Testa a configuração do modo de mascaramento para REDACTED.
    *
    * Verifica que o modo MASK_MODE_REDACTED pode ser configurado corretamente.
    *
    * @return void
    */
   public function testSetMaskModeToRedacted(): void
   {
      Logger::setMaskMode(Logger::MASK_MODE_REDACTED);

      $reflection = new \ReflectionClass(Logger::class);
      $maskModeProp = $reflection->getProperty('maskMode');
      $maskModeProp->setAccessible(true);

      $maskMode = $maskModeProp->getValue();
      
      $this->assertEquals(Logger::MASK_MODE_REDACTED, $maskMode);
   }

   /**
    * Testa a configuração do modo de mascaramento para PARTIAL.
    *
    * Verifica que o modo MASK_MODE_PARTIAL pode ser configurado corretamente.
    *
    * @return void
    */
   public function testSetMaskModeToPartial(): void
   {
      Logger::setMaskMode(Logger::MASK_MODE_PARTIAL);

      $reflection = new \ReflectionClass(Logger::class);
      $maskModeProp = $reflection->getProperty('maskMode');
      $maskModeProp->setAccessible(true);

      $maskMode = $maskModeProp->getValue();
      
      $this->assertEquals(Logger::MASK_MODE_PARTIAL, $maskMode);
   }

   /**
    * Testa validação de modo de mascaramento inválido.
    *
    * Verifica que ao tentar configurar um modo inválido, uma exceção
    * é capturada e pode ser recuperada via Logger::fail().
    *
    * @return void
    */
   public function testSetMaskModeInvalidMode(): void
   {
      Logger::setMaskMode('invalid_mode');

      $fail = Logger::fail();
      
      $this->assertNotNull($fail);
      $this->assertStringContainsString('Invalid mask mode', $fail->getMessage());
   }

   /**
    * Testa sanitização no modo REDACTED.
    *
    * Verifica que no modo REDACTED, todos os valores sensíveis são
    * completamente substituídos por [redacted], independente do tamanho.
    *
    * @return void
    */
   public function testRedactedModeSanitization(): void
   {
      Logger::setMaskMode(Logger::MASK_MODE_REDACTED);

      $reflection = new \ReflectionClass(Logger::class);
      $method = $reflection->getMethod('sanitizeLogParams');
      $method->setAccessible(true);

      $sensitiveData = [
         'password' => 'secret123',
         'token' => 'abc123xyz',
         'normal_field' => 'normal_value'
      ];

      $result = $method->invoke(null, $sensitiveData);

      $this->assertEquals('[redacted]', $result['password']);
      $this->assertEquals('[redacted]', $result['token']);
      $this->assertEquals('normal_value', $result['normal_field']);
   }

   /**
    * Testa sanitização no modo PARTIAL com valores longos.
    *
    * Verifica que no modo PARTIAL, valores com mais de 4 caracteres mantêm
    * os últimos 4 caracteres visíveis e o resto é substituído por *******.
    *
    * @return void
    */
   public function testPartialModeSanitizationLongValue(): void
   {
      Logger::setMaskMode(Logger::MASK_MODE_PARTIAL);
      Logger::addSensitiveFields(['credit_card']);

      $reflection = new \ReflectionClass(Logger::class);
      $method = $reflection->getMethod('sanitizeLogParams');
      $method->setAccessible(true);

      $sensitiveData = [
         'credit_card' => '1234567890',
         'api_token' => 'abcdefghij1234',
         'normal_field' => 'normal_value'
      ];

      $result = $method->invoke(null, $sensitiveData);

      $this->assertEquals('*******7890', $result['credit_card']);
      $this->assertEquals('*******1234', $result['api_token']);
      $this->assertEquals('normal_value', $result['normal_field']);
   }

   /**
    * Testa sanitização no modo PARTIAL com valores curtos.
    *
    * Verifica que no modo PARTIAL, valores com 4 ou menos caracteres
    * são completamente mascarados como *******.
    *
    * @return void
    */
   public function testPartialModeSanitizationShortValue(): void
   {
      Logger::setMaskMode(Logger::MASK_MODE_PARTIAL);
      Logger::addSensitiveFields(['pin']);

      $reflection = new \ReflectionClass(Logger::class);
      $method = $reflection->getMethod('sanitizeLogParams');
      $method->setAccessible(true);

      $sensitiveData = [
         'token' => 'abc',
         'pin' => '1234',
         'password' => 'xy',
         'normal_field' => 'normal_value'
      ];

      $result = $method->invoke(null, $sensitiveData);

      // Valores com 4 ou menos caracteres devem retornar apenas *******
      $this->assertEquals('*******', $result['token']);
      $this->assertEquals('*******', $result['pin']);
      $this->assertEquals('*******', $result['password']);
      $this->assertEquals('normal_value', $result['normal_field']);
   }

   /**
    * Testa sanitização no modo PARTIAL com exatamente 4 caracteres.
    *
    * Verifica que valores com exatamente 4 caracteres são completamente
    * mascarados como ******* no modo PARTIAL.
    *
    * @return void
    */
   public function testPartialModeSanitizationExactlyFourCharacters(): void
   {
      Logger::setMaskMode(Logger::MASK_MODE_PARTIAL);
      Logger::addSensitiveFields(['code']);

      $reflection = new \ReflectionClass(Logger::class);
      $method = $reflection->getMethod('sanitizeLogParams');
      $method->setAccessible(true);

      $sensitiveData = [
         'code' => '1234'
      ];

      $result = $method->invoke(null, $sensitiveData);

      // Valor com exatamente 4 caracteres deve retornar apenas *******
      $this->assertEquals('*******', $result['code']);
   }

   /**
    * Testa sanitização no modo PARTIAL com arrays aninhados.
    *
    * Verifica que o modo PARTIAL funciona corretamente com estruturas
    * de dados aninhadas, mantendo os últimos 4 caracteres visíveis.
    *
    * @return void
    */
   public function testPartialModeSanitizationNestedArrays(): void
   {
      Logger::setMaskMode(Logger::MASK_MODE_PARTIAL);
      Logger::addSensitiveFields(['credit_card']);

      $reflection = new \ReflectionClass(Logger::class);
      $method = $reflection->getMethod('sanitizeLogParams');
      $method->setAccessible(true);

      $sensitiveData = [
         'user_data' => [
            'name' => 'John Doe',
            'credit_card' => '1234567890',
            'password' => 'secret123'
         ]
      ];

      $result = $method->invoke(null, $sensitiveData);

      $this->assertEquals('John Doe', $result['user_data']['name']);
      $this->assertEquals('*******7890', $result['user_data']['credit_card']);
      $this->assertEquals('*******t123', $result['user_data']['password']);
   }

   /**
    * Testa o método applyMask() no modo REDACTED.
    *
    * Verifica que o método applyMask() retorna [redacted] para qualquer valor
    * quando o modo está configurado como MASK_MODE_REDACTED.
    *
    * @return void
    */
   public function testApplyMaskMethodRedacted(): void
   {
      Logger::setMaskMode(Logger::MASK_MODE_REDACTED);

      $reflection = new \ReflectionClass(Logger::class);
      $method = $reflection->getMethod('applyMask');
      $method->setAccessible(true);

      $this->assertEquals('[redacted]', $method->invoke(null, 'secret123'));
      $this->assertEquals('[redacted]', $method->invoke(null, 'abc'));
      $this->assertEquals('[redacted]', $method->invoke(null, 'very_long_password_here'));
   }

   /**
    * Testa o método applyMask() no modo PARTIAL.
    *
    * Verifica que o método applyMask() mantém os últimos 4 caracteres visíveis
    * para valores longos e mascara completamente valores com 4 ou menos caracteres.
    *
    * @return void
    */
   public function testApplyMaskMethodPartial(): void
   {
      Logger::setMaskMode(Logger::MASK_MODE_PARTIAL);

      $reflection = new \ReflectionClass(Logger::class);
      $method = $reflection->getMethod('applyMask');
      $method->setAccessible(true);

      $this->assertEquals('*******7890', $method->invoke(null, '1234567890'));
      $this->assertEquals('*******', $method->invoke(null, 'abc'));
      $this->assertEquals('*******', $method->invoke(null, '1234'));
      $this->assertEquals('*******here', $method->invoke(null, 'very_long_password_here'));
   }
}
