#!/bin/bash
# Update by ID to be absolutely sure
HASH='$2y$12$6ZJDyQs1cRqQjLCPlEjCPud.CCSE8QQMzh9o9rCLxDgTpGKfWJNli'
docker exec wk_postgres psql -U wk_user -d wk_main -c "UPDATE public.users SET password = '$HASH' WHERE id = '84e04541-656e-4358-bc47-4e8450bd';"
echo "✅ Senha atualizada!"
echo "Email: admin@consultoriawk.com"
echo "Senha: Admin@123"
