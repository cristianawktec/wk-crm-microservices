<?php
/**
 * Script de teste de envio de email - VERSÃO CORRIGIDA
 * Usa uma oportunidade existente sem buscar por opportunity_id
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Opportunity;
use App\Models\User;
use App\Services\NotificationService;

echo "=== Teste de Email - Sistema de Notificações (v2) ===\n\n";

$opp = Opportunity::first();

if (!$opp) {
    echo "❌ Nenhuma oportunidade no banco!\n";
    exit(1);
}

echo "✅ Oportunidade: {$opp->title} (R$ " . number_format($opp->value, 2, ',', '.') . ")\n";
echo "   ID: {$opp->id}\n";
echo "   Status: {$opp->status}\n\n";

$user = User::first();

if (!$user) {
    echo "⚠️  Nenhum usuário encontrado\n";
    exit(1);
}

echo "✅ Destinatário: {$user->name} ({$user->email})\n\n";

echo "📧 Enviando email via NotificationService...\n";

try {
    $start = microtime(true);
    
    // Chamar o serviço que envia email + cria notificação
    NotificationService::opportunityCreated($opp, $user);
    
    $ms = round((microtime(true) - $start) * 1000);
    
    echo "✅ Email enviado com sucesso! (tempo: {$ms}ms)\n\n";
    
    // Buscar última notificação SEM filtrar por opportunity_id (coluna não existe)
    $notification = \App\Models\Notification::orderByDesc('created_at')->first();
    
    if ($notification) {
        echo "✅ Última notificação no banco:\n";
        echo "   ID: {$notification->id}\n";
        echo "   Type: {$notification->type}\n";
        echo "   User: {$notification->user_id}\n";
        echo "   Title: {$notification->title}\n";
        echo "   Created: {$notification->created_at}\n";
        echo "   Read: " . ($notification->read_at ? 'Sim' : 'Não') . "\n";
        
        $data = is_array($notification->data) ? $notification->data : (json_decode($notification->data, true) ?? []);
        if (!empty($data)) {
            echo "   Data: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
    }
    
    echo "📝 Verifique:\n";
    echo "   • Email em: noreply@consultoriawk.com\n";
    echo "   • Logs: storage/logs/laravel.log\n";
    echo "   • SSE: conexões ativas receberão evento\n\n";
    
    echo "✅ TESTE CONCLUÍDO!\n";
    
} catch (\Throwable $e) {
    echo "❌ ERRO: {$e->getMessage()}\n";
    echo "   Arquivo: {$e->getFile()}:{$e->getLine()}\n\n";
    
    echo "💡 Verifique:\n";
    echo "   1. MAIL_* no .env\n";
    echo "   2. SMTP Titan (smtp0101.titan.email:465 SSL)\n";
    echo "   3. Logs: storage/logs/laravel.log\n\n";
    
    exit(1);
}
