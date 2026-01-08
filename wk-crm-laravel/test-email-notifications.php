<?php
/**
 * Script de teste de envio de email via NotificationService
 * Usa uma oportunidade existente - NÃO CRIA DADOS NOVOS
 * 
 * Uso: php test-email-notifications.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Opportunity;
use App\Models\User;
use App\Services\NotificationService;

echo "=== Teste de Envio de Email - Sistema de Notificações ===\n\n";

// Buscar primeira oportunidade existente
$opp = Opportunity::first();

if (!$opp) {
    echo "❌ Nenhuma oportunidade encontrada no banco!\n";
    exit(1);
}

echo "✅ Oportunidade encontrada:\n";
echo "   ID: {$opp->id}\n";
echo "   Título: {$opp->title}\n";
echo "   Valor: R$ " . number_format($opp->value, 2, ',', '.') . "\n";
echo "   Status: {$opp->status}\n";
echo "   Customer ID: {$opp->customer_id}\n\n";

// Buscar um usuário para ser o "criador" (pode ser qualquer um)
$user = User::first();

if (!$user) {
    echo "⚠️  Nenhum usuário encontrado - criando notificação sem remetente\n";
    $user = null;
} else {
    echo "✅ Usuário remetente: {$user->name} ({$user->email})\n\n";
}

// Testar envio de email
echo "📧 Testando envio de email...\n";

try {
    $startTime = microtime(true);
    
    // Chamar o método que envia email + notificação
    NotificationService::opportunityCreated($opp, $user);
    
    $duration = round((microtime(true) - $startTime) * 1000);
    
    echo "✅ Email enviado com sucesso! (tempo: {$duration}ms)\n\n";
    
    // Verificar última notificação criada
    $notification = \App\Models\Notification::orderByDesc('created_at')->first();
    if ($notification) {
        echo "✅ Notificação criada no banco:\n";
        echo "   ID: {$notification->id}\n";
        echo "   Type: {$notification->type}\n";
        echo "   User ID: {$notification->user_id}\n";
        echo "   Created: {$notification->created_at}\n";
        echo "   Read: " . ($notification->read_at ? 'Sim' : 'Não') . "\n";
        $data = is_array($notification->data) ? $notification->data : (json_decode($notification->data, true) ?? []);
        if (!empty($data)) {
            echo "   Data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
        } else {
            echo "\n";
        }
    }
    
    echo "📝 Verifique:\n";
    echo "   1. Email recebido em: noreply@consultoriawk.com (ou inbox configurado)\n";
    echo "   2. Log do Laravel: storage/logs/laravel.log\n";
    echo "   3. SSE stream se houver conexões ativas\n\n";
    
    echo "✅ TESTE CONCLUÍDO COM SUCESSO!\n";
    
} catch (\Throwable $e) {
    echo "❌ ERRO ao enviar email:\n";
    echo "   Mensagem: {$e->getMessage()}\n";
    echo "   Arquivo: {$e->getFile()}:{$e->getLine()}\n\n";
    
    echo "💡 Verifique:\n";
    echo "   1. Configurações MAIL_* no .env\n";
    echo "   2. Credenciais SMTP válidas\n";
    echo "   3. Conexão com smtp0101.titan.email:465 (SSL)\n";
    echo "   4. Logs detalhados em storage/logs/laravel.log\n\n";
    
    exit(1);
}
