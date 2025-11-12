<?php

namespace CodeFlow\Logger\Tests;

use CodeFlow\Logger\Logger;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class LoggerTest extends TestCase
{
   private vfsStreamDirectory $fileSystem;

   protected function setUp(): void
   {
      // Setup virtual file system para testes
      $this->fileSystem = vfsStream::setup('logs');

      // Reset logger state para cada teste
      $reflection = new \ReflectionClass(Logger::class);
      $properties = ['engine', 'initialized', 'requestId', 'emailEnabled', 'telegramEnabled'];

      foreach ($properties as $property)
      {
         $prop = $reflection->getProperty($property);
         $prop->setAccessible(true);
         $prop->setValue(null, $property === 'initialized' ? false : null);
      }
   }

   public function testLoggerCanBeInstantiated(): void
   {
      $logger = new Logger();
      $this->assertInstanceOf(Logger::class, $logger);
   }

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

   public function testRequestIdGeneration(): void
   {
      $reflection = new \ReflectionClass(Logger::class);
      $method = $reflection->getMethod('gererateRequestId');
      $method->setAccessible(true);

      $requestId1 = $method->invoke(null);
      $requestId2 = $method->invoke(null);

      // Deve retornar o mesmo ID na mesma requisição
      $this->assertEquals($requestId1, $requestId2);
      $this->assertStringStartsWith('req_', $requestId1);
   }

   public function testEmailConfiguration(): void
   {
      Logger::enableLogByEmail(
         'from@example.com',
         'to@example.com',
         'Test Subject'
      );

      $reflection = new \ReflectionClass(Logger::class);

      $senderProp = $reflection->getProperty('senderEmail');
      $senderProp->setAccessible(true);

      $recipientProp = $reflection->getProperty('recipientEmail');
      $recipientProp->setAccessible(true);

      $subjectProp = $reflection->getProperty('subject');
      $subjectProp->setAccessible(true);

      $enabledProp = $reflection->getProperty('emailEnabled');
      $enabledProp->setAccessible(true);

      $this->assertEquals('from@example.com', $senderProp->getValue());
      $this->assertEquals('to@example.com', $recipientProp->getValue());
      $this->assertEquals('Test Subject', $subjectProp->getValue());
      $this->assertTrue($enabledProp->getValue());
   }

   public function testTelegramConfiguration(): void
   {
      Logger::enableLogByTelegram('bot_token', 'chat_id');

      $reflection = new \ReflectionClass(Logger::class);

      $tokenProp = $reflection->getProperty('telegramBotToken');
      $tokenProp->setAccessible(true);

      $chatProp = $reflection->getProperty('telegramChatId');
      $chatProp->setAccessible(true);

      $enabledProp = $reflection->getProperty('telegramEnabled');
      $enabledProp->setAccessible(true);

      $this->assertEquals('bot_token', $tokenProp->getValue());
      $this->assertEquals('chat_id', $chatProp->getValue());
      $this->assertTrue($enabledProp->getValue());
   }

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
}
