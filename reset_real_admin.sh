#!/bin/bash
# Update the correct user that actually exists
HASH='$2y$12$6ZJDyQs1cRqQjLCPlEjCPud.CCSE8QQMzh9o9rCLxDgTpGKfWJNli'
docker exec wk_postgres psql -U wk_user -d wk_main -c "UPDATE public.users SET password = '$HASH' WHERE email = 'admin-test@wkcrm.local';"
echo "✅ Senha atualizada!"
echo "Email: admin-test@wkcrm.local"
echo "Senha: Admin@123"
