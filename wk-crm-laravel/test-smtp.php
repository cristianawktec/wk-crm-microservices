<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "🔧 TESTE DE CONEXÃO SMTP TITAN\n";
echo "================================\n\n";

echo "📋 Configurações:\n";
echo "MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "MAIL_PORT: " . config('mail.mailers.smtp.port') . "\n";
echo "MAIL_USERNAME: " . config('mail.mailers.smtp.username') . "\n";
echo "MAIL_ENCRYPTION: " . config('mail.mailers.smtp.encryption') . "\n";
echo "MAIL_FROM: " . config('mail.from.address') . "\n";
echo "MAIL_AUDIT_RECIPIENT: " . config('mail.audit_recipient') . "\n\n";

echo "📤 Enviando email de teste...\n";

try {
    Mail::raw('Este é um teste de conexão SMTP do WK CRM.', function ($message) {
        $message->to(config('mail.audit_recipient'))
                ->subject('[TESTE] Conexão SMTP WK CRM');
    });
    
    echo "✅ Email enviado com sucesso!\n";
    echo "📧 Verifique a caixa de entrada de: " . config('mail.audit_recipient') . "\n";
} catch (\Exception $e) {
    echo "❌ ERRO ao enviar email:\n";
    echo $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
