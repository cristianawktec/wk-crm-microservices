<?php
/**
 * Script de deploy remoto para VPS via API
 * Executa commands na VPS através de HTTP POST
 */

$deployToken = 'wk-deploy-2024-secure-token-123';
$vpsHost = 'https://api.consultoriawk.com';
$commands = [
    'cd /var/www/consultoriawk-crm && pwd',
    'git pull origin main',
    'cd wk-customer-app && npm install',
    'npm run build',
    'cp -r dist/* /var/www/consultoriawk-crm/app/',
    'ls -la /var/www/consultoriawk-crm/app/ | head -10',
    'echo "Deploy completado em $(date)"'
];

$payload = [
    'token' => $deployToken,
    'commands' => $commands
];

echo "🚀 Iniciando deploy na VPS...\n";
echo "URL: {$vpsHost}/api/deploy\n";
echo "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

// Executar via curl
$curlCmd = sprintf(
    "curl -s -X POST %s/api/deploy \\\n  -H 'Content-Type: application/json' \\\n  -d '%s'",
    $vpsHost,
    json_encode($payload)
);

echo "Comando:\n{$curlCmd}\n\n";
echo "Executando...\n\n";

exec($curlCmd, $output, $returnCode);
echo implode("\n", $output);
echo "\n\nReturn code: {$returnCode}\n";
