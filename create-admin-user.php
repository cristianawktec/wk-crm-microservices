#!/usr/bin/env php
<?php

/**
 * Script para criar/atualizar usuário admin no WK CRM
 * 
 * Uso: php create-admin-user.php
 */

require __DIR__ . '/wk-crm-laravel/vendor/autoload.php';

$app = require_once __DIR__ . '/wk-crm-laravel/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = 'admin@consultoriawk.com';
$password = 'Admin@2025'; // Senha padrão
$name = 'Administrador WK';

echo "🔍 Procurando usuário: {$email}...\n";

$user = User::where('email', $email)->first();

if ($user) {
    echo "✅ Usuário encontrado! ID: {$user->id}\n";
    echo "📝 Atualizando senha...\n";
    
    $user->password = Hash::make($password);
    $user->save();
    
    echo "✅ Senha atualizada com sucesso!\n";
} else {
    echo "❌ Usuário não encontrado. Criando...\n";
    
    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make($password),
    ]);
    
    // Assign admin role if Spatie roles exist
    try {
        $user->assignRole('admin');
        echo "✅ Role 'admin' atribuída!\n";
    } catch (\Exception $e) {
        echo "⚠️  Role 'admin' não existe. Ignorando...\n";
    }
    
    echo "✅ Usuário criado com sucesso! ID: {$user->id}\n";
}

echo "\n📋 Credenciais:\n";
echo "   Email: {$email}\n";
echo "   Senha: {$password}\n";
echo "\n✅ Concluído!\n";
