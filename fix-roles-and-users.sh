#!/bin/bash

echo "Criando roles e usuários..."

# Create roles in PostgreSQL
PGPASSWORD=secure_password_123 psql -h localhost -p 5433 -U wk_user -d wk_main << 'SQL'
-- Create roles with UUIDs
INSERT INTO roles (id, name, guard_name, created_at, updated_at)
VALUES 
    (gen_random_uuid(), 'admin', 'web', NOW(), NOW()),
    (gen_random_uuid(), 'customer', 'web', NOW(), NOW()),
    (gen_random_uuid(), 'seller', 'web', NOW(), NOW())
ON CONFLICT (name, guard_name) DO NOTHING;

SELECT id, name, guard_name FROM roles;
SQL

# Now create users via Laravel
docker exec wk_crm_laravel php artisan tinker --execute="
\$adminTest = \App\Models\User::firstOrCreate(
    ['email' => 'admin-test@wkcrm.local'],
    ['name' => 'Admin WK', 'password' => \Hash::make('password123')]
);
\$adminTest->syncRoles(['admin']);
echo '✅ admin-test@wkcrm.local (password123)' . PHP_EOL;

\$customerTest = \App\Models\User::firstOrCreate(
    ['email' => 'customer-test@wkcrm.local'],
    ['name' => 'Customer Test', 'password' => \Hash::make('password123')]
);
\$customerTest->syncRoles(['customer']);
\App\Models\Customer::firstOrCreate(
    ['email' => 'customer-test@wkcrm.local'],
    ['id' => \$customerTest->id, 'name' => 'Customer Test', 'phone' => '000000000']
);
echo '✅ customer-test@wkcrm.local (password123)' . PHP_EOL;

\$admin = \App\Models\User::where('email', 'admin@consultoriawk.com')->first();
if (\$admin) {
    \$admin->password = \Hash::make('Admin@123456');
    \$admin->save();
    \$admin->syncRoles(['admin']);
    echo '✅ admin@consultoriawk.com (Admin@123456)' . PHP_EOL;
}

echo PHP_EOL . 'USUÁRIOS:' . PHP_EOL;
foreach (\App\Models\User::orderBy('email')->get() as \$u) {
    echo \$u->email . ' - ' . \$u->roles->pluck('name')->join(',') . PHP_EOL;
}
"
