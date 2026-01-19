#!/usr/bin/env php
<?php
// Test authentication directly

require '/var/www/html/vendor/autoload.php';

$app = require_once '/var/www/html/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Testing Authentication ===\n\n";

$user = User::where('email', 'admin@consultoriawk.com')->first();

if (!$user) {
    echo "❌ User NOT FOUND\n";
    exit(1);
}

echo "✅ User found:\n";
echo "   Email: {$user->email}\n";
echo "   Name: {$user->name}\n";
echo "   Hash: " . substr($user->password, 0, 30) . "...\n\n";

$passwords = ['Admin@2025', 'Admin@123', 'password123'];

foreach ($passwords as $pwd) {
    $result = Hash::check($pwd, $user->password);
    $status = $result ? "✅ MATCH" : "❌ NO MATCH";
    echo "Testing '{$pwd}': {$status}\n";
}

echo "\n=== End Test ===\n";
