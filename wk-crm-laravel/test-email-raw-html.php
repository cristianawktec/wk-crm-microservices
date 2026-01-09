<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

try {
    $timestamp = date('Y-m-d H:i:s');
    $test_email = 'noreply@consultoriawk.com';
    
    echo "\n========== RAW VIEW TEST ==========\n";
    echo "Time: {$timestamp}\n\n";
    
    // Test rendering view directly
    $html = View::make('emails.notification', [
        'title' => 'Test Notification',
        'message' => 'This is a test',
        'action_url' => 'https://app.consultoriawk.com',
        'created_at' => $timestamp,
    ])->render();
    
    echo "✅ View rendered successfully\n";
    echo "HTML length: " . strlen($html) . " bytes\n\n";
    
    // Now try sending via Mail facade with raw HTML
    Mail::html($html, function ($message) use ($test_email) {
        $message->to($test_email);
        $message->subject('WK CRM Test via Raw HTML');
        $message->from('noreply@consultoriawk.com');
    });
    
    echo "✅ EMAIL SENT SUCCESSFULLY via raw HTML\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
}
