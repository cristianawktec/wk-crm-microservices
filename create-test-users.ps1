# Create test users for quick login buttons
$password = "6y6-@Qw88-b)"

Write-Host "Creating test users..." -ForegroundColor Cyan

echo $password | ssh root@72.60.254.100 @'
docker exec wk_crm_laravel php artisan tinker << 'PHPCODE'
// Check and create Admin Test user
$adminTest = App\Models\User::where('email', 'admin-test@wkcrm.local')->first();
if (!$adminTest) {
    $adminTest = App\Models\User::create([
        'email' => 'admin-test@wkcrm.local',
        'name' => 'Admin WK',
        'password' => Hash::make('password123')
    ]);
    $adminTest->syncRoles(['admin']);
    echo "✅ Admin Test criado: admin-test@wkcrm.local\n";
} else {
    echo "ℹ️  Admin Test já existe: admin-test@wkcrm.local\n";
}

// Check and create Customer Test user
$customerTest = App\Models\User::where('email', 'customer-test@wkcrm.local')->first();
if (!$customerTest) {
    $customerTest = App\Models\User::create([
        'email' => 'customer-test@wkcrm.local',
        'name' => 'Customer Test',
        'password' => Hash::make('password123')
    ]);
    $customerTest->syncRoles(['customer']);
    echo "✅ Customer Test criado: customer-test@wkcrm.local\n";
    
    // Create Customer record
    $customer = App\Models\Customer::firstOrCreate(
        ['email' => 'customer-test@wkcrm.local'],
        [
            'id' => $customerTest->id,
            'name' => 'Customer Test',
            'phone' => '000000000'
        ]
    );
    echo "✅ Customer record criado\n";
} else {
    echo "ℹ️  Customer Test já existe: customer-test@wkcrm.local\n";
}

// Verify admin@consultoriawk.com has correct password
$admin = App\Models\User::where('email', 'admin@consultoriawk.com')->first();
if ($admin) {
    if (!Hash::check('Admin@123456', $admin->password)) {
        $admin->password = Hash::make('Admin@123456');
        $admin->save();
        echo "✅ Senha do admin@consultoriawk.com atualizada para: Admin@123456\n";
    } else {
        echo "ℹ️  admin@consultoriawk.com já tem a senha correta\n";
    }
    $admin->syncRoles(['admin']);
    echo "✅ Role admin confirmada para admin@consultoriawk.com\n";
}

echo "\n=== RESUMO DOS USUÁRIOS ===\n";
$users = App\Models\User::orderBy('email')->get();
foreach ($users as $user) {
    $roles = $user->roles->pluck('name')->join(', ');
    echo $user->email . " | " . $user->name . " | Roles: " . ($roles ?: 'nenhuma') . "\n";
}
PHPCODE
'@
