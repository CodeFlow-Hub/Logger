<?php

/**
 * Exemplo avançado com configuração completa
 * 
 * Execute com: php examples/advanced_usage.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use CodeFlow\Logger\Logger;

echo "=== CodeFlow Logger - Exemplo Avançado ===\n\n";

// Configurar notificações por email (opcional)
// Logger::enableLogByEmail(
//     'noreply@meuapp.com',
//     'admin@meuapp.com', 
//     'Sistema: Erro Crítico Detectado'
// );

// Configurar notificações por Telegram (opcional)  
// Logger::enableLogByTelegram(
//     '123456:ABC-DEF1234567890',  // Token do bot
//     '-1001234567890'             // Chat ID
// );

echo "1. Simulando fluxo de uma aplicação web:\n";

// Início da requisição
Logger::info('HTTP request received', [
   'method' => 'POST',
   'uri' => '/api/users',
   'ip' => '192.168.1.100',
   'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);

// Autenticação
Logger::debug('Validating user credentials', [
   'email' => 'user@example.com'
]);

// Tentativa de autenticação com dados sensíveis (automaticamente sanitizados)
Logger::info('User authentication attempt', [
   'email' => 'user@example.com',
   'password' => 'my_secret_password',  // Será [redacted]
   'remember_token' => 'abc123xyz',     // Será [redacted]
   'session_data' => [
      'ip' => '192.168.1.100',
      'user_secret' => 'topsecret123'  // Será [redacted]
   ]
]);

// Sucesso na autenticação
Logger::info('User authenticated successfully', [
   'user_id' => 1001,
   'email' => 'user@example.com',
   'last_login' => '2024-01-17 10:30:00'
]);

echo "2. Simulando operações de banco de dados:\n";

// Query de leitura
Logger::debug('Database query executed', [
   'query' => 'SELECT u.*, p.name as profile_name FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ?',
   'params' => [1001],
   'execution_time' => '0.045s',
   'rows_affected' => 1
]);

// Operação crítica
Logger::info('User profile updated', [
   'user_id' => 1001,
   'updated_fields' => ['name', 'email', 'profile_picture'],
   'previous_email' => 'old@example.com',
   'new_email' => 'user@example.com'
]);

echo "3. Simulando cenários de erro:\n";

// Warning - problema não crítico
Logger::warning('Upload file size exceeds recommended limit', [
   'file_size' => '15MB',
   'recommended_limit' => '10MB',
   'file_type' => 'image/jpeg',
   'user_id' => 1001
]);

// Erro - problema que precisa atenção
Logger::error('Failed to connect to external API', [
   'api_endpoint' => 'https://api.payment-gateway.com/v1/process',
   'error_message' => 'Connection timeout after 30 seconds',
   'retry_attempt' => 3,
   'user_id' => 1001,
   'order_id' => 'ORD-2024-001'
]);

// Crítico - problema grave no sistema
Logger::critical('Database connection pool exhausted', [
   'active_connections' => 100,
   'max_connections' => 100,
   'queue_size' => 50,
   'affected_operations' => ['user_registration', 'order_processing', 'payment_verification']
]);

echo "4. Simulando logs estruturados para análise:\n";

// Evento de negócio
Logger::info('Order completed successfully', [
   'order_id' => 'ORD-2024-001',
   'user_id' => 1001,
   'total_amount' => 299.99,
   'payment_method' => 'credit_card',
   'shipping_address' => [
      'country' => 'BR',
      'state' => 'SP',
      'city' => 'São Paulo'
   ],
   'items_count' => 3,
   'processing_time' => '2.34s'
]);

// Métricas de performance
Logger::debug('Page performance metrics', [
   'page' => '/dashboard',
   'load_time' => '1.235s',
   'db_queries' => 12,
   'memory_usage' => '45MB',
   'cache_hits' => 8,
   'cache_misses' => 4
]);

echo "5. Testando contexto automático:\n";

// O sistema automaticamente adiciona:
// - request_id (único por requisição)
// - session_id 
// - user_id (se disponível)
// - ip_address
// - user_agent
// - file e line

Logger::info('Testing automatic context', [
   'custom_data' => 'This will be merged with automatic context'
]);

echo "\n=== Exemplo avançado concluído! ===\n";
echo "Verifique o arquivo de log em: logs/app-" . date('Y-m-d') . ".log\n";
echo "\nNota: Para testar notificações por email/Telegram,\n";
echo "descomente as configurações no início do arquivo.\n";
