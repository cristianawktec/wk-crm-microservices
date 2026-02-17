<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'admin@consultoriawk.com')->first();

if ($user) {
    $user->password = Hash::make('Admin@2025');
    $user->save();
    echo "✅ Senha atualizada para Admin@2025\n";
    echo "Email: {$user->email}\n";
    echo "Nome: {$user->name}\n";
} else {
    echo "❌ Usuário não encontrado\n";
}
