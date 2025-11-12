<?php

/**
 * Exemplo básico de uso do CodeFlow Logger
 * 
 * Execute com: php examples/basic_usage.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use CodeFlow\Logger\Logger;
use function CodeFlow\Logger\logger;

echo "=== CodeFlow Logger - Exemplo Básico ===\n\n";

// 1. Uso básico sem configuração
echo "1. Logs básicos:\n";
Logger::debug('Sistema inicializado', ['version' => '1.0.0']);
Logger::info('Usuário autenticado', ['user_id' => 123, 'ip' => '192.168.1.1']);
Logger::warning('Tentativa de login inválida', ['email' => 'test@example.com']);

echo "✓ Logs gravados em logs/app-" . date('Y-m-d') . ".log\n\n";

// 2. Usando função helper
echo "2. Usando função helper logger():\n";
logger()->info('Pedido criado', ['order_id' => 456, 'total' => 99.90]);
logger()->debug('Query executada', ['query' => 'SELECT * FROM orders', 'time' => '0.05s']);

echo "✓ Logs gravados via helper function\n\n";

// 3. Testando sanitização automática
echo "3. Testando sanitização de dados sensíveis:\n";
Logger::info('Login attempt', [
   'email' => 'user@example.com',
   'password' => 'secret123',      // Será exibido como [redacted]
   'api_token' => 'abc123xyz',     // Será exibido como [redacted]
   'user_data' => [
      'name' => 'João Silva',
      'senha' => 'password123'    // Será exibido como [redacted]
   ]
]);

echo "✓ Dados sensíveis automaticamente sanitizados\n\n";

// 4. Testando diferentes níveis
echo "4. Testando todos os níveis PSR-3:\n";
Logger::debug('Debug message');
Logger::info('Info message');
Logger::warning('Warning message');
Logger::error('Error message');
Logger::critical('Critical message');
Logger::alert('Alert message');
Logger::emergency('Emergency message');

echo "✓ Todos os níveis testados\n\n";

// 5. Configuração opcional (descomente para testar)
echo "5. Configuração de notificações (comentado):\n";
echo "// Logger::enableLogByEmail('from@example.com', 'to@example.com', 'Erro no Sistema');\n";
echo "// Logger::enableLogByTelegram('bot_token', 'chat_id');\n\n";

echo "=== Exemplo concluído! ===\n";
echo "Verifique o arquivo de log em: logs/app-" . date('Y-m-d') . ".log\n";
