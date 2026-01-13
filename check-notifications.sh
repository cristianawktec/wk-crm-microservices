#!/bin/bash

# Script para verificar action_url das notificações

ssh root@72.60.254.100 << 'REMOTECMD'

# Criar arquivo temporário com script PHP
cat > /tmp/check_notifications.php << 'PHP'
<?php
// Load Laravel app
require '/var/www/wk-crm-api/wk-crm-laravel/vendor/autoload.php';
$app = require '/var/www/wk-crm-api/wk-crm-laravel/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');

// Get DB connection
$db = $app->make('db');
$results = $db->table('notifications')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get(['id', 'action_url']);

echo "Últimas 5 notificações:\n";
echo str_repeat("=", 60) . "\n";
foreach ($results as $row) {
    echo "ID: " . $row->id . "\n";
    echo "URL: " . $row->action_url . "\n";
    echo "-" . str_repeat("-", 58) . "\n";
}
PHP

# Run via Docker
docker exec wk_crm_laravel php /tmp/check_notifications.php

REMOTECMD
