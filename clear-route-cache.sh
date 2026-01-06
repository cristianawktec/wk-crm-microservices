#!/bin/bash

# Script para limpar cache de rotas em produção
echo "🔄 Limpando cache de rotas e configurações..."

cd /var/www/html

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Recache routes e config para produção
php artisan config:cache
php artisan route:cache

echo "✅ Caches limpos e recacheados com sucesso!"
echo "📍 Arquivo de rotas: /var/www/html/routes/api.php"
echo "🔗 Testando endpoint: curl https://api.consultoriawk.com/api/health"
