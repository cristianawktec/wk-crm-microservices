<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Opportunity;
use App\Services\NotificationService;

echo "=== TEST: Oportunidade + Email ===\n\n";

$user = User::where('email', 'admin@consultoriawk.com')->first();
if (!$user) {
    echo "❌ User not found\n";
    exit(1);
}

echo "✅ User: {$user->name} ({$user->email})\n\n";

echo "📝 Creating opportunity...\n";
$opp = Opportunity::create([
    'title' => 'TEST OPP ' . now()->timestamp,
    'value' => 50000,
    'customer_id' => $user->id,
    'status' => 'open'
]);

echo "✅ Opportunity created: {$opp->id}\n\n";

echo "📧 Sending notification + email...\n";
try {
    NotificationService::opportunityCreated($opp, $user);
    echo "✅ Notification service called successfully!\n\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}

echo "✅ TEST COMPLETED!\n";
