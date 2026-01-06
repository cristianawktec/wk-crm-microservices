#!/bin/bash
# Script que corrige PostgreSQL sem precisar entrar manualmente no psql
# Execute na VPS: bash fix-postgres-automatico.sh

set -e

echo "========================================="
echo "  FIX POSTGRESQL AUTOMÁTICO"
echo "========================================="
echo ""

# 1. Backup do pg_hba.conf
echo "📦 Fazendo backup do pg_hba.conf..."
sudo cp /etc/postgresql/16/main/pg_hba.conf /etc/postgresql/16/main/pg_hba.conf.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ Backup criado!"
echo ""

# 2. Modificar pg_hba.conf temporariamente para trust
echo "🔧 Configurando acesso temporário sem senha..."
sudo sed -i 's/^local.*all.*postgres.*peer$/local   all             postgres                                trust/' /etc/postgresql/16/main/pg_hba.conf
sudo sed -i 's/^local.*all.*all.*peer$/local   all             all                                     trust/' /etc/postgresql/16/main/pg_hba.conf
sudo sed -i 's/^host.*all.*all.*127.0.0.1.*scram-sha-256$/host    all             all             127.0.0.1\/32            trust/' /etc/postgresql/16/main/pg_hba.conf
sudo sed -i 's/^host.*all.*all.*127.0.0.1.*md5$/host    all             all             127.0.0.1\/32            trust/' /etc/postgresql/16/main/pg_hba.conf
echo "✅ pg_hba.conf configurado para 'trust'!"
echo ""

# 3. Reiniciar PostgreSQL
echo "🔄 Reiniciando PostgreSQL..."
sudo systemctl restart postgresql
sleep 3
echo "✅ PostgreSQL reiniciado!"
echo ""

# 4. Criar usuário e banco
echo "👤 Criando/atualizando usuário e banco..."
sudo -u postgres psql -p 5433 << 'EOSQL'
-- Criar usuário se não existir
DO $$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_user WHERE usename = 'wk_user') THEN
        CREATE USER wk_user WITH PASSWORD 'secure_password_123' SUPERUSER;
        RAISE NOTICE 'Usuário criado: wk_user';
    ELSE
        ALTER USER wk_user WITH PASSWORD 'secure_password_123' SUPERUSER;
        RAISE NOTICE 'Senha atualizada para: wk_user';
    END IF;
END
$$;

-- Criar banco se não existir
SELECT 'CREATE DATABASE wk_main OWNER wk_user'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'wk_main')\gexec

-- Garantir permissões
GRANT ALL PRIVILEGES ON DATABASE wk_main TO wk_user;
ALTER DATABASE wk_main OWNER TO wk_user;

\c wk_main
GRANT ALL ON SCHEMA public TO wk_user;
ALTER SCHEMA public OWNER TO wk_user;

\q
EOSQL
echo "✅ Usuário e banco configurados!"
echo ""

# 5. Restaurar pg_hba.conf para segurança
echo "🔒 Restaurando configuração segura..."
sudo sed -i 's/^local.*all.*postgres.*trust$/local   all             postgres                                peer/' /etc/postgresql/16/main/pg_hba.conf
sudo sed -i 's/^local.*all.*all.*trust$/local   all             all                                     peer/' /etc/postgresql/16/main/pg_hba.conf
sudo sed -i 's/^host.*all.*all.*127.0.0.1.*trust$/host    all             all             127.0.0.1\/32            scram-sha-256/' /etc/postgresql/16/main/pg_hba.conf
sudo systemctl restart postgresql
sleep 3
echo "✅ Configuração segura restaurada!"
echo ""

# 6. Atualizar .env do Laravel
echo "📝 Atualizando .env do Laravel..."
cd /var/www/html/wk-crm-laravel

# Backup do .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Atualizar valores
sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i 's/^DB_PORT=.*/DB_PORT=5433/' .env
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=wk_main/' .env
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=wk_user/' .env
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=secure_password_123/' .env

echo "✅ .env atualizado!"
echo ""
echo "Nova configuração:"
grep "^DB_" .env
echo ""

# 7. Limpar cache do Laravel
echo "🧹 Limpando cache do Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan config:cache
echo "✅ Cache limpo!"
echo ""

# 8. Testar conexão
echo "🧪 Testando conexão..."
php artisan tinker --execute="
try {
    \$pdo = DB::connection()->getPdo();
    echo '\n✅✅✅ CONEXÃO ESTABELECIDA COM SUCESSO! ✅✅✅\n\n';
    echo 'Database: ' . \$pdo->query('SELECT current_database()')->fetchColumn() . '\n';
    echo 'Versão PostgreSQL: ' . \$pdo->query('SELECT version()')->fetchColumn() . '\n\n';
} catch (Exception \$e) {
    echo '\n❌ ERRO NA CONEXÃO\n';
    echo 'Mensagem: ' . \$e->getMessage() . '\n';
    exit(1);
}
"

if [ $? -eq 0 ]; then
    echo "📊 Verificando registros..."
    php artisan tinker --execute="
    try {
        echo 'Usuários: ' . App\Models\User::count() . '\n';
        echo 'Oportunidades: ' . App\Models\Opportunity::count() . '\n';
        echo 'Notificações: ' . App\Models\Notification::count() . '\n';
    } catch (Exception \$e) {
        echo '\n⚠️  Tabelas não encontradas. Execute: php artisan migrate\n';
    }
    "
    
    echo ""
    echo "========================================="
    echo "  ✅✅✅ TUDO FUNCIONANDO! ✅✅✅"
    echo "========================================="
    echo ""
    echo "Próximos passos:"
    echo "1. Teste o login em: https://app.consultoriawk.com/login"
    echo "2. Usuário: admin@consultoriawk.com"
    echo "3. Se as tabelas não existirem, execute: php artisan migrate"
    echo ""
    echo "📂 Backups criados:"
    ls -lh /etc/postgresql/16/main/pg_hba.conf.backup.* 2>/dev/null | tail -1
    ls -lh .env.backup.* 2>/dev/null | tail -1
    echo ""
else
    echo ""
    echo "❌ Algo deu errado na conexão."
    echo ""
    echo "Verifique:"
    echo "1. Logs do PostgreSQL: sudo tail -50 /var/log/postgresql/postgresql-16-main.log"
    echo "2. Status do serviço: sudo systemctl status postgresql"
    echo "3. Portas: netstat -tuln | grep 5433"
    echo ""
fi
