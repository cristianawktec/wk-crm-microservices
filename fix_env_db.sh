#!/bin/bash
cat >> /var/www/wk-crm-api/.env << 'EOF'

DB_CONNECTION=pgsql
DB_HOST=wk_postgres
DB_PORT=5432
DB_DATABASE=wk_main
DB_USERNAME=wk_user
DB_PASSWORD=secure_password_123
EOF

echo "✅ Configurações do banco adicionadas!"
cat /var/www/wk-crm-api/.env
