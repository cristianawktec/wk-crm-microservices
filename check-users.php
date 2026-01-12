<?php
require_once 'wk-crm-laravel/vendor/autoload.php';
$app = require_once 'wk-crm-laravel/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = \App\Models\User::all(['id', 'name', 'email']);
foreach($users as $user) {
    echo $user->name . " => " . $user->email . "\n";
}
