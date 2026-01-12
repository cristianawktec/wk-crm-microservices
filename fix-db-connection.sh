#!/bin/bash
# Fix Laravel database connection on VPS

cd /root/wk-crm-microservices/wk-crm-laravel

# Update .env with correct database settings
sed -i 's/DB_HOST=.*/DB_HOST=172.17.0.1/' .env
sed -i 's/DB_PORT=.*/DB_PORT=5433/' .env
sed -i 's/DB_DATABASE=.*/DB_DATABASE=wk_main/' .env
sed -i 's/DB_USERNAME=.*/DB_USERNAME=wk_user/' .env
sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=secure_password_123/' .env

echo "=== Updated .env ==="
cat .env | grep -E '^DB_'

echo ""
echo "=== Restarting Laravel container ==="
docker compose restart app

sleep 3

echo ""
echo "=== Clearing caches ==="
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear

echo ""
echo "=== Running migrations ==="
docker compose exec app php artisan migrate --force

echo ""
echo "=== Testing database connection ==="
docker compose exec app php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected successfully!';"
