<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationMail;
use App\Models\User;

try {
    // Find or create test user
    $user = User::firstOrCreate(
        ['email' => 'cristian@consultoriawk.com'],
        ['name' => 'Cristian Test', 'password' => bcrypt('password')]
    );

    echo "User: {$user->email}\n";

    // Create Mailable directly with strings
    $mailable = new NotificationMail(
        titleText: '🎯 Nova Oportunidade',
        bodyText: 'Uma nova oportunidade foi criada.',
        actionUrl: 'https://app.consultoriawk.com/opportunities/123',
        createdAtText: now()->toDateTimeString()
    );

    echo "Mailable created\n";

    // Send directly
    Mail::to($user->email)->send($mailable);

    echo "✅ Email enviado com sucesso para {$user->email}\n";
} catch (\Throwable $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
