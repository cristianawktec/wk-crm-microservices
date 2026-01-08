$PHPCODE = @'
<?php
require '/var/www/html/vendor/autoload.php';
$app = require_once '/var/www/html/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Opportunity;
use App\Models\User;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Str;

echo "=== Teste Email v3 (destinatário fixo) ===\n\n";

$targetEmail = 'cristian@consultoriawk.com';

$opp = Opportunity::first();
if (!$opp) { echo "Erro: nenhuma oportunidade encontrada\n"; exit(1); }

$user = User::where('email', $targetEmail)->first();
if (!$user) {
	$user = new User();
	$user->name = 'Cristian WK';
	$user->email = $targetEmail;
	$user->password = bcrypt(Str::random(16));
	$user->save();
	echo "Usuário criado para teste: {$targetEmail}\n";
} else {
	echo "Usuário encontrado: {$user->email}\n";
}

echo "Oportunidade: {$opp->title}\n";
echo "Destinatário: {$targetEmail}\n\n";

echo "Enviando email...\n";

$data = [
	'user_id' => $user->id,
	'type' => 'opportunity_created',
	'title' => '🎯 Nova Oportunidade',
	'message' => "Oportunidade \"{$opp->title}\" foi criada. Valor: R$ " . number_format($opp->value ?? 0, 2, ',', '.'),
	'action_url' => "/opportunities/{$opp->id}",
	'related_data' => [
		'opportunity_id' => $opp->id,
		'opportunity_title' => $opp->title,
		'opportunity_value' => $opp->value ?? 0,
	],
];

NotificationService::notify($data);

echo "Email enviado!\n\n";

$n = Notification::orderByDesc('created_at')->first();
echo "Notificação criada: {$n->id}\n";
echo "Type: {$n->type}\n";
echo "Title: {$n->title}\n\n";
echo "SUCESSO!\n";
'@

$PHPCODE | ssh root@72.60.254.100 "cat > /tmp/test-email.php && docker cp /tmp/test-email.php wk_crm_laravel:/tmp/ && docker exec wk_crm_laravel php /tmp/test-email.php"
