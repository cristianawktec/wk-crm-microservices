#!/bin/bash
# Script para resetar senha do admin

cd /var/www/html

php artisan tinker << 'EOF'
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::where('email', 'admin@consultoriawk.com')->first();

if ($admin) {
    echo "✅ Usuário encontrado: " . $admin->name . "\n";
    $admin->password = Hash::make('Admin@2025');
    $admin->save();
    echo "✅ Senha atualizada para: Admin@2025\n";
} else {
    echo "❌ Usuário não encontrado!\n";
    echo "Criando novo usuário...\n";
    
    $admin = User::create([
        'name' => 'Administrador WK',
        'email' => 'admin@consultoriawk.com',
        'password' => Hash::make('Admin@2025'),
    ]);
    
    echo "✅ Usuário criado com sucesso!\n";
    echo "Email: admin@consultoriawk.com\n";
    echo "Senha: Admin@2025\n";
}
EOF
