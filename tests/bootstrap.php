<?php

// Bootstrap para testes PHPUnit

require_once __DIR__ . '/../vendor/autoload.php';

// Configurar timezone para testes
date_default_timezone_set('UTC');

// Limpar variáveis globais para testes isolados
$_SERVER = [
   'REMOTE_ADDR' => '127.0.0.1',
   'HTTP_USER_AGENT' => 'PHPUnit Test Suite'
];

$_SESSION = [];

// Criar diretório de logs se não existir
$logsDir = __DIR__ . '/../logs';
if (!is_dir($logsDir))
{
   mkdir($logsDir, 0755, true);
}
