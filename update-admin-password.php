<?php
// Script temporário para atualizar senha do admin

require __DIR__ . '/wk-crm-laravel/vendor/autoload.php';

$app = require_once __DIR__ . '/wk-crm-laravel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = 'admin@consultoriawk.com';
$newPassword = 'Admin@2026';

$user = User::where('email', $email)->first();

if ($user) {
    $user->password = Hash::make($newPassword);
    $user->save();
    
    echo "✅ Senha atualizada com sucesso!\n";
    echo "Email: {$user->email}\n";
    echo "Nome: {$user->name}\n";
    echo "Nova senha: {$newPassword}\n";
} else {
    echo "❌ Usuário não encontrado: {$email}\n";
}
