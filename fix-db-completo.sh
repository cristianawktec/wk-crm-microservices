#!/bin/bash
set -e

echo "========== FIX POSTGRESQL =========="
echo ""

# 1. Backup
echo "1/7 Fazendo backup..."
cp /etc/postgresql/16/main/pg_hba.conf /etc/postgresql/16/main/pg_hba.conf.backup
echo "✅ Backup criado"
echo ""

# 2. Configurar trust
echo "2/7 Configurando acesso sem senha..."
sed -i 's/peer$/trust/g' /etc/postgresql/16/main/pg_hba.conf
sed -i 's/scram-sha-256$/trust/g' /etc/postgresql/16/main/pg_hba.conf
sed -i 's/md5$/trust/g' /etc/postgresql/16/main/pg_hba.conf
echo "✅ Configurado"
echo ""

# 3. Reiniciar
echo "3/7 Reiniciando PostgreSQL..."
systemctl restart postgresql
sleep 3
echo "✅ Reiniciado"
echo ""

# 4. Criar usuário e banco
echo "4/7 Criando usuário e banco..."
sudo -u postgres psql -p 5433 << 'EOSQL'
DROP USER IF EXISTS wk_user;
CREATE USER wk_user WITH PASSWORD 'secure_password_123' SUPERUSER;
DROP DATABASE IF EXISTS wk_main;
CREATE DATABASE wk_main OWNER wk_user;
GRANT ALL PRIVILEGES ON DATABASE wk_main TO wk_user;
\c wk_main
GRANT ALL ON SCHEMA public TO wk_user;
ALTER SCHEMA public OWNER TO wk_user;
EOSQL
echo "✅ Usuário e banco criados"
echo ""

# 5. Restaurar segurança
echo "5/7 Restaurando segurança..."
sed -i 's/trust$/scram-sha-256/g' /etc/postgresql/16/main/pg_hba.conf
systemctl restart postgresql
sleep 3
echo "✅ Segurança restaurada"
echo ""

# 6. Atualizar .env
echo "6/7 Atualizando .env..."
cd /var/www/html/wk-crm-laravel
cp .env .env.backup
sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i 's/^DB_PORT=.*/DB_PORT=5433/' .env
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=wk_main/' .env
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=wk_user/' .env
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=secure_password_123/' .env
echo "✅ .env atualizado"
echo ""
grep "^DB_" .env
echo ""

# 7. Testar
echo "7/7 Testando conexão..."
php artisan config:clear > /dev/null 2>&1
php artisan config:cache > /dev/null 2>&1
php artisan tinker --execute="DB::connection()->getPdo(); echo 'CONECTADO!\n';"

echo ""
echo "========================================="
echo "  ✅✅✅ SUCESSO! ✅✅✅"
echo "========================================="
echo ""
echo "Acesse: https://app.consultoriawk.com/login"
