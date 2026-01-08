#!/usr/bin/env pwsh
# Script para testar envio de email no servidor VPS

$SERVER = "root@72.60.254.100"

Write-Host "=== Criando script de teste no servidor ===" -ForegroundColor Cyan

# Criar arquivo PHP corrigido no servidor
$phpScript = @'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Opportunity;
use App\Models\User;
use App\Services\NotificationService;

echo "=== Teste Email - Notificações (v2 corrigido) ===\n\n";

$opp = Opportunity::first();
if (!$opp) {
    echo "❌ Nenhuma oportunidade!\n";
    exit(1);
}

echo "✅ Oportunidade: {$opp->title}\n";
echo "   Valor: R$ " . number_format($opp->value, 2, ',', '.') . "\n";
echo "   Status: {$opp->status}\n\n";

$user = User::first();
if (!$user) {
    echo "❌ Nenhum usuário!\n";
    exit(1);
}

echo "✅ Destinatário: {$user->name} ({$user->email})\n\n";
echo "📧 Enviando email...\n";

try {
    $start = microtime(true);
    NotificationService::opportunityCreated($opp, $user);
    $ms = round((microtime(true) - $start) * 1000);
    
    echo "✅ Email enviado! (tempo: {$ms}ms)\n\n";
    
    // Buscar última notificação (SEM filtrar por opportunity_id)
    $notif = \App\Models\Notification::orderByDesc('created_at')->first();
    
    if ($notif) {
        echo "✅ Notificação registrada:\n";
        echo "   ID: {$notif->id}\n";
        echo "   Type: {$notif->type}\n";
        echo "   Title: {$notif->title}\n";
        echo "   User: {$notif->user_id}\n";
        echo "   Created: {$notif->created_at}\n";
        echo "   Read: " . ($notif->read_at ? 'Sim' : 'Não') . "\n";
        
        $data = is_array($notif->data) ? $notif->data : (json_decode($notif->data, true) ?? []);
        if (!empty($data)) {
            echo "   Data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
        }
        echo "\n";
    }
    
    echo "📝 Verifique email em: noreply@consultoriawk.com\n";
    echo "✅ TESTE CONCLUÍDO!\n";
    
} catch (\Throwable $e) {
    echo "❌ ERRO: {$e->getMessage()}\n";
    echo "   Local: {$e->getFile()}:{$e->getLine()}\n\n";
    exit(1);
}
'@

# Enviar script para servidor
Write-Host "Enviando script para servidor..." -ForegroundColor Yellow
$phpScript | ssh $SERVER "cat > /var/www/html/test-email-v2.php"

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Falha ao enviar script" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Script enviado com sucesso`n" -ForegroundColor Green

# Executar teste
Write-Host "=== Executando teste de email ===" -ForegroundColor Cyan
ssh $SERVER "docker exec wk_crm_laravel php /var/www/html/test-email-v2.php"

Write-Host "`n=== Teste finalizado ===" -ForegroundColor Cyan
