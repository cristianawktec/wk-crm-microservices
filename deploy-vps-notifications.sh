#!/bin/bash
set -e

echo "🚀 Deploy VPS - Notifications System"
echo "======================================"

# 1. Clone/Pull repository
if [ ! -d "/root/wk-crm-microservices" ]; then
    echo "📦 Cloning repository..."
    cd /root
    git clone https://github.com/cristianawktec/wk-crm-microservices.git
else
    echo "🔄 Pulling latest changes..."
    cd /root/wk-crm-microservices
    git pull origin main
fi

# 2. Copy Laravel files
echo "📋 Copying Laravel files..."
rsync -av --delete /root/wk-crm-microservices/wk-crm-laravel/ /var/www/html/wk-crm-laravel/ \
    --exclude=vendor \
    --exclude=node_modules \
    --exclude=storage/logs \
    --exclude=storage/framework/cache \
    --exclude=storage/framework/sessions \
    --exclude=.env

# 3. Copy Vue Customer App
echo "📋 Copying Vue app..."
rsync -av --delete /root/wk-crm-microservices/wk-customer-app/dist/ /var/www/html/app/ \
    || echo "⚠️  Vue dist not found - need to build locally first"

# 4. Set permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data /var/www/html/wk-crm-laravel/storage
chown -R www-data:www-data /var/www/html/wk-crm-laravel/bootstrap/cache
chown -R www-data:www-data /var/www/html/app
chmod -R 775 /var/www/html/wk-crm-laravel/storage
chmod -R 775 /var/www/html/wk-crm-laravel/bootstrap/cache

# 5. Run migrations
echo "🗄️  Running migrations..."
cd /var/www/html/wk-crm-laravel
docker compose exec -T wk-crm-laravel php artisan migrate --force

# 6. Clear cache
echo "🧹 Clearing cache..."
docker compose exec -T wk-crm-laravel php artisan config:clear
docker compose exec -T wk-crm-laravel php artisan cache:clear
docker compose exec -T wk-crm-laravel php artisan route:clear

# 7. Restart containers (optional, only if needed)
# echo "🔄 Restarting containers..."
# cd /var/www/html/wk-crm-laravel
# docker compose restart wk-crm-laravel

echo ""
echo "✅ Deploy completo!"
echo "🌐 Backend: https://api.consultoriawk.com"
echo "🌐 App: https://app.consultoriawk.com"
echo "🌐 Admin: https://api.consultoriawk.com/admin"
