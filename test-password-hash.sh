#!/bin/bash
cd /var/www/html

php artisan tinker << 'EOF'
$user = App\Models\User::where('email', 'admin@consultoriawk.com')->first();

if ($user) {
    echo "✅ Usuário encontrado: " . $user->email . "\n";
    echo "Hash atual: " . substr($user->password, 0, 40) . "...\n";
    echo "\nTestando senhas:\n";
    echo "- Admin@123: " . (Hash::check('Admin@123', $user->password) ? '✅ VÁLIDA' : '❌ INVÁLIDA') . "\n";
    echo "- Admin@2025: " . (Hash::check('Admin@2025', $user->password) ? '✅ VÁLIDA' : '❌ INVÁLIDA') . "\n";
    echo "- password123: " . (Hash::check('password123', $user->password) ? '✅ VÁLIDA' : '❌ INVÁLIDA') . "\n";
} else {
    echo "❌ Usuário não encontrado\n";
}
exit
EOF
