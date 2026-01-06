#!/bin/bash
# SCRIPT DE CORREÇÃO COMPLETA DO BANCO
# Execute na VPS: bash corrigir-banco-completo.sh

echo "====================================="
echo "  CORREÇÃO COMPLETA DO BANCO"
echo "====================================="
echo ""

cd /var/www/html/wk-crm-laravel

# Backup do .env
echo "📦 Fazendo backup do .env..."
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ Backup criado!"
echo ""

# Mostrar configuração atual
echo "📋 Configuração atual:"
echo "---------------------"
grep "^DB_" .env
echo ""

# Perguntar pela senha
echo "🔐 Configure a senha do PostgreSQL"
echo "-----------------------------------"
read -sp "Digite uma senha forte para o banco: " DB_PASS
echo ""
read -sp "Confirme a senha: " DB_PASS_CONFIRM
echo ""
echo ""

if [ "$DB_PASS" != "$DB_PASS_CONFIRM" ]; then
    echo "❌ As senhas não conferem!"
    exit 1
fi

# Obter o usuário do banco
DB_USER=$(grep "^DB_USERNAME=" .env | cut -d'=' -f2)
DB_NAME=$(grep "^DB_DATABASE=" .env | cut -d'=' -f2)

if [ -z "$DB_USER" ]; then
    DB_USER="wk_crm_user"
    echo "⚠️  DB_USERNAME não encontrado, usando: $DB_USER"
fi

if [ -z "$DB_NAME" ]; then
    DB_NAME="wk_crm_production"
    echo "⚠️  DB_DATABASE não encontrado, usando: $DB_NAME"
fi

echo ""
echo "🔧 Configurando PostgreSQL..."
echo "-----------------------------"

# Criar usuário e banco se não existir
sudo -u postgres psql << EOF
-- Criar usuário se não existir
DO \$\$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_user WHERE usename = '$DB_USER') THEN
        CREATE USER $DB_USER WITH PASSWORD '$DB_PASS';
        RAISE NOTICE 'Usuário criado: $DB_USER';
    ELSE
        ALTER USER $DB_USER WITH PASSWORD '$DB_PASS';
        RAISE NOTICE 'Senha atualizada para: $DB_USER';
    END IF;
END
\$\$;

-- Criar banco se não existir
SELECT 'CREATE DATABASE $DB_NAME OWNER $DB_USER'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = '$DB_NAME')\gexec

-- Garantir permissões
GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;
ALTER DATABASE $DB_NAME OWNER TO $DB_USER;

\c $DB_NAME
GRANT ALL ON SCHEMA public TO $DB_USER;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO $DB_USER;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO $DB_USER;

\q
EOF

echo "✅ PostgreSQL configurado!"
echo ""

echo "📝 Atualizando .env..."
echo "----------------------"

# Atualizar .env
sed -i "s|^DB_HOST=.*|DB_HOST=127.0.0.1|" .env
sed -i "s|^DB_PORT=.*|DB_PORT=5432|" .env
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" .env
sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env
sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|" .env

echo "✅ .env atualizado!"
echo ""

echo "🧹 Limpando cache..."
echo "--------------------"
php artisan config:clear
php artisan cache:clear
php artisan config:cache
echo "✅ Cache limpo!"
echo ""

echo "🧪 Testando conexão..."
echo "----------------------"
php artisan tinker --execute="
try {
    \$pdo = DB::connection()->getPdo();
    echo '✅ CONEXÃO ESTABELECIDA COM SUCESSO!\n';
    echo 'Database: ' . \$pdo->query('SELECT current_database()')->fetchColumn() . '\n';
    echo 'Versão: ' . \$pdo->query('SELECT version()')->fetchColumn() . '\n';
} catch (Exception \$e) {
    echo '❌ ERRO NA CONEXÃO\n';
    echo 'Mensagem: ' . \$e->getMessage() . '\n';
    exit(1);
}
"

if [ $? -eq 0 ]; then
    echo ""
    echo "📊 Contando registros..."
    echo "------------------------"
    php artisan tinker --execute="
    try {
        echo 'Usuários: ' . App\Models\User::count() . '\n';
        echo 'Oportunidades: ' . App\Models\Opportunity::count() . '\n';
        echo 'Notificações: ' . App\Models\Notification::count() . '\n';
    } catch (Exception \$e) {
        echo '⚠️  Tabelas não encontradas. Execute: php artisan migrate\n';
    }
    "
    
    echo ""
    echo "====================================="
    echo "  ✅ CORREÇÃO CONCLUÍDA!"
    echo "====================================="
    echo ""
    echo "Próximos passos:"
    echo "1. Se viu 'Tabelas não encontradas', execute:"
    echo "   php artisan migrate"
    echo ""
    echo "2. Teste o login em:"
    echo "   https://app.consultoriawk.com/login"
    echo ""
    echo "3. Backup do .env antigo está em:"
    echo "   $(ls -1t .env.backup.* | head -1)"
    echo ""
else
    echo ""
    echo "❌ A conexão falhou!"
    echo ""
    echo "Verifique:"
    echo "1. Se o PostgreSQL está rodando: sudo systemctl status postgresql"
    echo "2. Se a porta 5432 está aberta: netstat -tuln | grep 5432"
    echo "3. O arquivo de log: tail -50 /var/log/postgresql/postgresql-*.log"
    echo ""
fi
