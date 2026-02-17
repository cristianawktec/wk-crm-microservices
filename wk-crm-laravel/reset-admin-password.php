<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

// Now we can use Laravel
$user = User::where('email', 'admin@consultoriawk.com')->first();

if ($user) {
    $user->password = Hash::make('test123');
    $user->save();
    echo "✅ Senha do admin atualizada para: test123\n";
    echo "Email: " . $user->email . "\n";
} else {
    echo "❌ Usuário não encontrado\n";
}
?>
