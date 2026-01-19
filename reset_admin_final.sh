#!/bin/bash
# Hash for password "Admin@123" with bcrypt
HASH='$2y$12$6ZJDyQs1cRqQjLCPlEjCPud.CCSE8QQMzh9o9rCLxDgTpGKfWJNli'
docker exec wk_postgres psql -U wk_user -d wk_main -c "UPDATE public.users SET password = '$HASH' WHERE email = 'admin@consultoriawk.com';"
echo "✅ Senha atualizada!"
docker exec wk_postgres psql -U wk_user -d wk_main -c "SELECT email, name FROM public.users WHERE email = 'admin@consultoriawk.com';"
