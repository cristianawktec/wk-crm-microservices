#!/bin/bash
cd /root/wk-crm-microservices/wk-crm-laravel
sed -i 's/DB_HOST=.*/DB_HOST=172.17.0.1/' .env
sed -i 's/DB_PORT=.*/DB_PORT=5433/' .env
echo "=== .env atualizado ==="
grep -E '^DB_' .env
echo ""
echo "=== Reiniciando container ==="
docker compose restart app
sleep 3
echo ""
echo "=== Limpando cache ==="
docker compose exec app php artisan config:clear 2>/dev/null || true
echo ""
echo "=== Testando migrations ==="
docker compose exec app php artisan migrate --force
