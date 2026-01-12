<?php
require '/var/www/html/bootstrap/app.php';
$app = app();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::where('email', 'admin@consultoriawk.com')->first();
if ($admin) {
    $admin->password = Hash::make('Admin@123456');
    $admin->save();
    echo "✅ Senha atualizada para: Admin@123456\n";
    
    if (Hash::check('Admin@123456', $admin->password)) {
        echo "✅ Senha verificada com sucesso!\n";
    }
} else {
    echo "❌ Usuário não encontrado\n";
}
