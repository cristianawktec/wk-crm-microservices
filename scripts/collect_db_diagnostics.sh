#!/bin/bash
set -euo pipefail
cd /opt/wk-crm

echo "---COLUMNS---"
docker compose exec -T postgres psql -U wk_user -d wk_main -c "SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_name='opportunities' ORDER BY ordinal_position;"

echo "---MIGRATIONS---"
docker compose exec -T postgres psql -U wk_user -d wk_main -c "SELECT id, migration, batch FROM migrations ORDER BY id DESC LIMIT 50;"

echo "---OPPORTUNITIES_COUNT---"
docker compose exec -T postgres psql -U wk_user -d wk_main -c "SELECT COUNT(*) as cnt, COALESCE(SUM(value),0) as total FROM opportunities;"

echo "---LARAVEL_LOG---"
docker compose exec -T wk-crm-laravel sh -c "tail -n 200 storage/logs/laravel.log"

echo "---POSTGRES_LOG---"
docker compose logs --tail=200 postgres
