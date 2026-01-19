#!/bin/bash
echo "✅ Verificando usuários após restaurar backup..."
docker exec wk_postgres psql -U wk_user -d wk_main -c "SELECT id, name, email FROM public.users ORDER BY created_at;"

echo ""
echo "🔍 Buscando admin@consultoriawk.com..."
docker exec wk_postgres psql -U wk_user -d wk_main -c "SELECT id, name, email, password FROM public.users WHERE email = 'admin@consultoriawk.com';"
