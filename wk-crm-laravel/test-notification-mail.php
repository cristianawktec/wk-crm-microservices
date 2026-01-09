<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\NotificationMail;

try {
    $timestamp = date('Y-m-d H:i:s');
    $test_email = 'noreply@consultoriawk.com';
    
    echo "\n========== NOTIFICATION MAIL TEST ==========\n";
    echo "Time: {$timestamp}\n";
    echo "To: {$test_email}\n";
    echo "==========================================\n\n";
    
    $mailable = new NotificationMail(
        "WK CRM Test Notification",
        "This is a test of the notification system with properly cast variables",
        "https://app.consultoriawk.com/dashboard",
        $timestamp
    );
    
    Mail::to($test_email)->send($mailable);
    
    Log::info("NotificationMail test sent successfully at {$timestamp}");
    
    echo "✅ NOTIFICATION MAIL SENT SUCCESSFULLY\n";
    echo "Expected to arrive at: {$test_email}\n";
    echo "Check email spam folder if not in inbox\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
    Log::error('NotificationMail test failed: ' . $e->getMessage(), ['exception' => $e]);
    exit(1);
}
