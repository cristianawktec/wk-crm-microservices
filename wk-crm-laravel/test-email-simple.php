<?php
require_once "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationMail;

try {
    $timestamp = date("Y-m-d H:i:s");
    $email = "noreply@consultoriawk.com";
    
    echo "Test: Sending to $email at $timestamp\n";
    
    $mailable = new NotificationMail(
        "Test Notification",
        "Sistema de notificacoes funcionando",
        "https://app.consultoriawk.com",
        $timestamp
    );
    
    Mail::to($email)->send($mailable);
    echo "SUCCESS\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
