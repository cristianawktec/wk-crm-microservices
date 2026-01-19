#!/usr/bin/env php
<?php
require '/var/www/html/vendor/autoload.php';
$app = require_once '/var/www/html/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = 'admin@consultoriawk.com';
$new = 'Admin@2025';

$user = User::where('email', $email)->first();
if (!$user) {
    echo "❌ User not found: {$email}\n";
    exit(1);
}

$old = $user->password;
$user->password = Hash::make($new);
$user->save();

echo "✅ Password updated for {$email}\n";
echo "Old: " . substr($old, 0, 30) . "...\n";
echo "New: " . substr($user->password, 0, 30) . "...\n";
