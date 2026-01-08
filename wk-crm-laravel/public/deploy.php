<?php
// Endpoint para fazer deploy via webhook (sem SSH)
// Chame: curl https://api.consultoriawk.com/deploy.php

header('Content-Type: application/json');

// Validar secret se necessário
$secret = 'deploy_secret_123'; // TODO: Mover para .env
$token = $_GET['token'] ?? '';

if ($token !== $secret) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Mudar para o diretório do projeto
chdir('/root/crm');

// Executar git pull
$output = [];
$returnCode = 0;
exec('git pull 2>&1', $output, $returnCode);

$result = [
    'success' => $returnCode === 0,
    'git_pull' => $output,
    'return_code' => $returnCode
];

// Se sucesso, limpar cache
if ($returnCode === 0) {
    $cacheOutput = [];
    exec('docker exec wk_crm_laravel php artisan optimize:clear 2>&1', $cacheOutput, $cacheCode);
    $result['optimize_clear'] = $cacheOutput;
    $result['cache_code'] = $cacheCode;
}

echo json_encode($result, JSON_PRETTY_PRINT);
