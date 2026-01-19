#!/bin/bash
echo "🔍 Buscando usuários @consultoriawk.com..."
docker exec wk_postgres psql -U wk_user -d wk_main -c "SELECT id, name, email FROM public.users WHERE email LIKE '%consultoriawk.com';"
