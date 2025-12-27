<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$customer = App\Models\Customer::first();
if ($customer) {
    echo $customer->id . "\n";
} else {
    echo "NO_CUSTOMER\n";
}
