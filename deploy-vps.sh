#!/bin/bash

# Deploy script for WK CRM to VPS

set -e

echo "🚀 Iniciando deploy na VPS..."

# 1. Pull latest code
echo "📥 Atualizando código..."
cd /var/www/crm
git pull origin main

# 2. Install dependencies
echo "📦 Instalando dependências (Laravel)..."
cd /var/www/crm/wk-crm-laravel
composer install --no-dev --optimize-autoloader

# 3. Run migrations
echo "🗄️ Executando migrations..."
php artisan migrate --force

# 4. Clear cache
echo "🧹 Limpando cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Deploy frontend
echo "📄 Fazendo deploy do frontend..."
rm -rf /var/www/html/admin/*
cp -r /var/www/crm/wk-admin-frontend/dist/admin-frontend/* /var/www/html/admin/

# 6. Set permissions
echo "🔐 Ajustando permissões..."
chown -R www-data:www-data /var/www/crm
chown -R www-data:www-data /var/www/html/admin
chmod -R 755 /var/www/crm/storage
chmod -R 755 /var/www/crm/bootstrap/cache

# 7. Restart services
echo "🔄 Reiniciando serviços..."
systemctl restart php-fpm
systemctl restart nginx

echo "✅ Deploy concluído com sucesso!"
echo ""
echo "URLs:"
echo "  Frontend: https://admin.consultoriawk.com"
echo "  API: https://api.consultoriawk.com/api"
