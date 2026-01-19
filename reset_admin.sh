#!/bin/bash
HASH='$2y$12$6ZJDyQs1cRqQjLCPlEjCPud.CCSE8QQMzh9o9rCLxDgTpGKfWJNli'
docker exec wk_postgres psql -U wk_user -d wk_main -c "UPDATE users SET password = '$HASH' WHERE email = 'admin@consultoriawk.com';"
echo "✅ Senha: Admin@123"
