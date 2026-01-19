#!/bin/bash
cd /var/www/html/wk-crm-laravel
php artisan tinker <<EOF
\$user = App\Models\User::where('email', 'admin@consultoriawk.com')->first();
\$user->password = Hash::make('Admin@2026');
\$user->save();
echo "Password updated for: " . \$user->name . "\n";
echo "New password: Admin@2026\n";
exit
EOF
