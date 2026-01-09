<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

try {
    $timestamp = date('Y-m-d H:i:s');
    $test_email = 'noreply@consultoriawk.com';
    
    echo "\n========== EMAIL TEST ==========\n";
    echo "Time: {$timestamp}\n";
    echo "To: {$test_email}\n";
    echo "================================\n\n";
    
    // Simple raw email
    Mail::raw("WK CRM Email Test at {$timestamp}", function ($message) use ($test_email) {
        $message->to($test_email);
        $message->subject('WK CRM - Test Email');
        $message->from('noreply@consultoriawk.com');
    });
    
    Log::info("Email test sent at {$timestamp} to {$test_email}");
    
    echo "✅ EMAIL QUEUED SUCCESSFULLY\n";
    echo "Check inbox at: {$test_email}\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    Log::error('Email test failed: ' . $e->getMessage());
    exit(1);
}
