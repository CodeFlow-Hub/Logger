<?php

/**
 * Exemplo de configuração fluente do CodeFlow Logger
 * 
 * Execute com: php examples/fluent_configuration.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use CodeFlowHub\Logger\Logger;

echo "=== CodeFlow Logger - Configuração Fluente ===\n\n";

// Exemplo 1: Configuração básica
echo "1. Configuração básica:\n";
Logger::configure()
    ->setLogDirectory(__DIR__ . '/../logs')
    ->setFileLogLabel('app-' . date('Y-m-d') . '.log')
    ->setLevelFileLog(Logger::LEVEL_DEBUG)
    ->apply();

Logger::info('Logger configurado com sucesso!', ['method' => 'fluent']);

echo "✓ Configuração básica aplicada\n\n";

// Exemplo 2: Configuração completa com email
echo "2. Configuração com notificações por email:\n";
Logger::configure()
    ->setLogDirectory(__DIR__ . '/../logs')
    ->setFileLogLabel('app-' . date('Y-m-d') . '.log')
    ->setLevelFileLog(Logger::LEVEL_INFO)
    ->setLevelEmailLog(Logger::LEVEL_ERROR)
    ->enableEmail(
        'noreply@example.com',
        'admin@example.com',
        'Sistema: Erro Detectado'
    )
    ->apply();

Logger::error('Este erro seria enviado por email se configurado corretamente');

echo "✓ Configuração com email aplicada\n\n";

// Exemplo 3: Configuração completa com Telegram
echo "3. Configuração com Telegram:\n";
Logger::configure()
    ->setLogDirectory(__DIR__ . '/../logs')
    ->setLevelFileLog(Logger::LEVEL_DEBUG)
    ->setLevelTelegramLog(Logger::LEVEL_CRITICAL)
    ->disableEmail()  // configure() parte da configuração atual, que tem email do exemplo 2
    ->enableTelegram(
        '123456:ABC-DEF1234567890',  // Substitua pelo seu token
        '-1001234567890'             // Substitua pelo seu chat ID
    )
    ->apply();

Logger::critical('Esta mensagem crítica seria enviada ao Telegram se configurado corretamente');

echo "✓ Configuração com Telegram aplicada\n\n";

// Exemplo 4: Configuração completa com todos os recursos
echo "4. Configuração completa com todos os recursos:\n";
Logger::configure()
    ->setLogDirectory(__DIR__ . '/../logs')
    ->setFileLogLabel('production-' . date('Y-m-d') . '.log')
    ->setLevelFileLog(Logger::LEVEL_INFO)
    ->setLevelEmailLog(Logger::LEVEL_ERROR)
    ->setLevelTelegramLog(Logger::LEVEL_CRITICAL)
    ->enableEmail('noreply@example.com', 'admin@example.com')
    ->enableTelegram('bot_token', 'chat_id')
    ->addSensitiveFields(['credit_card', 'ssn', 'api_key', 'private_key'])
    ->setMaskMode(Logger::MASK_MODE_PARTIAL)
    ->apply();

Logger::info('Configuração completa aplicada', [
    'mask_mode' => 'partial',
    'custom_fields' => ['credit_card', 'ssn', 'api_key']
]);

echo "✓ Configuração completa aplicada\n\n";

// Exemplo 5: Testando campos sensíveis customizados
echo "5. Testando campos sensíveis customizados:\n";
Logger::info('Dados com campos sensíveis', [
    'user' => 'john_doe',
    'credit_card' => '1234567890123456',  // Será mascarado
    'ssn' => '123-45-6789',                // Será mascarado
    'api_key' => 'sk_live_abc123xyz',      // Será mascarado
    'normal_field' => 'este campo não será mascarado'
]);

echo "✓ Campos sensíveis testados\n\n";

// Exemplo 6: Reconfiguração dinâmica
echo "6. Reconfiguração dinâmica:\n";
Logger::configure()
    ->setLevelFileLog(Logger::LEVEL_WARNING)  // Alterando nível mínimo
    ->apply();

Logger::debug('Esta mensagem debug não será gravada');  // Não será gravada
Logger::warning('Esta mensagem warning será gravada');  // Será gravada

echo "✓ Reconfiguração dinâmica testada\n\n";

echo "=== Exemplo de configuração fluente concluído! ===\n";
echo "Verifique o arquivo de log em: logs/\n";



