$sourcePath = "c:\xampp\htdocs\crm\wk-crm-laravel\routes\api.php"
$destPath = "root@srv1057865:/var/www/html/wk-crm-laravel/routes/api.php"

# Copy file via SSH
& scp -o "StrictHostKeyChecking=no" $sourcePath $destPath

# Execute cleanup on server
ssh -o "StrictHostKeyChecking=no" root@srv1057865 @"
cd /var/www/html/wk-crm-laravel

# Remove duplicate imports
sed -i '/^use App\\Http\\Controllers\\Api\\TrendsController;$/!b;N;s/^\(.*\)\n\1$/\1/;P;D' routes/api.php 2>/dev/null || true

# Clear all caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# Rebuild caches
php artisan route:cache
php artisan config:cache

# Verify route
php artisan route:list | grep trends

echo "✅ Complete!"
"@
