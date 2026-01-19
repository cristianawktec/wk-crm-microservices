#!/bin/bash
echo "🔍 Buscando admin pelo UUID parcial..."
docker exec wk_postgres psql -U wk_user -d wk_main -c "SELECT id, name, email FROM public.users WHERE id::text LIKE '84e04541%';"
