#!/bin/bash
set -e

echo "🚀 VPS Deployment Script"
echo "========================"

# Variáveis
VPS_HOST="72.60.254.100"
VPS_USER="root"
VPS_LARAVEL="/var/www/html/wk-crm-laravel"
VPS_APP="/var/www/html/app"

echo "📌 Step 1: Clean up Docker containers"
docker compose -H "ssh://${VPS_USER}@${VPS_HOST}:22" down || true

echo "📌 Step 2: Start Docker containers"  
docker compose -H "ssh://${VPS_USER}@${VPS_HOST}:22" up -d

echo "📌 Step 3: Wait for containers to be ready"
sleep 10

echo "📌 Step 4: Run migrations"
docker compose -H "ssh://${VPS_USER}@${VPS_HOST}:22" exec -T wk-crm-laravel php artisan migrate --force

echo "📌 Step 5: Clear cache"
docker compose -H "ssh://${VPS_USER}@${VPS_HOST}:22" exec -T wk-crm-laravel php artisan config:clear
docker compose -H "ssh://${VPS_USER}@${VPS_HOST}:22" exec -T wk-crm-laravel php artisan cache:clear

echo "✅ Deployment complete!"
