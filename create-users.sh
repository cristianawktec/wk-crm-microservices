#!/bin/bash

# Create roles and test users via Laravel Artisan

echo "Criando roles e usuários de teste..."

docker exec wk_crm_laravel php /var/www/html/artisan tinker --execute="
// Create roles
\$adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
\$customerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
\$sellerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'seller', 'guard_name' => 'web']);
echo '✅ Roles criadas: admin, customer, seller' . PHP_EOL;

// Admin Test
\$adminTest = \App\Models\User::where('email', 'admin-test@wkcrm.local')->first();
if (!\$adminTest) {
    \$adminTest = \App\Models\User::create(['email' => 'admin-test@wkcrm.local', 'name' => 'Admin WK', 'password' => \Hash::make('password123')]);
    \$adminTest->syncRoles(['admin']);
    echo '✅ Admin Test criado: admin-test@wkcrm.local / password123' . PHP_EOL;
} else {
    \$adminTest->syncRoles(['admin']);
    echo 'ℹ️  Admin Test já existe: ' . \$adminTest->email . PHP_EOL;
}

// Customer Test
\$customerTest = \App\Models\User::where('email', 'customer-test@wkcrm.local')->first();
if (!\$customerTest) {
    \$customerTest = \App\Models\User::create(['email' => 'customer-test@wkcrm.local', 'name' => 'Customer Test', 'password' => \Hash::make('password123')]);
    \$customerTest->syncRoles(['customer']);
    echo '✅ Customer Test criado: customer-test@wkcrm.local / password123' . PHP_EOL;
    \$customer = \App\Models\Customer::firstOrCreate(['email' => 'customer-test@wkcrm.local'], ['id' => \$customerTest->id, 'name' => 'Customer Test', 'phone' => '000000000']);
    echo '✅ Customer record criado' . PHP_EOL;
} else {
    \$customerTest->syncRoles(['customer']);
    echo 'ℹ️  Customer Test já existe: ' . \$customerTest->email . PHP_EOL;
}

// Update admin password
\$admin = \App\Models\User::where('email', 'admin@consultoriawk.com')->first();
if (\$admin) {
    \$admin->password = \Hash::make('Admin@123456');
    \$admin->save();
    \$admin->syncRoles(['admin']);
    echo '✅ admin@consultoriawk.com: senha Admin@123456, role admin' . PHP_EOL;
}

echo PHP_EOL . '=== TODOS OS USUÁRIOS ===' . PHP_EOL;
\$users = \App\Models\User::orderBy('email')->get();
foreach (\$users as \$user) {
    \$roles = \$user->roles->pluck('name')->join(', ');
    echo \$user->email . ' | ' . \$user->name . ' | Roles: ' . (\$roles ?: 'nenhuma') . PHP_EOL;
}
"
