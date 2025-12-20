<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();

if ($user) {
    $token = $user->createToken('sse-test')->plainTextToken;
    echo "\n=== TOKEN GERADO ===\n";
    echo $token;
    echo "\n====================\n\n";
    echo "User: {$user->name} ({$user->email})\n";
} else {
    echo "Nenhum usuário encontrado no banco!\n";
}
