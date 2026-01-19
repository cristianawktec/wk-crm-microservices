#!/bin/bash
# Reset admin password
docker exec wk_crm_laravel php artisan tinker << 'EOF'
$user = App\Models\User::where('email', 'admin@consultoriawk.com')->first();
$user->password = bcrypt('Admin@123');
$user->save();
echo "Senha atualizada para Admin@123\n";
exit
EOF

# Clear caches
docker exec wk_crm_laravel php artisan cache:clear
docker exec wk_crm_laravel php artisan config:clear
docker exec wk_crm_laravel php artisan route:clear

echo "=== Correções concluídas ==="
