#!/bin/bash
# Execute este script na VPS via SSH
# ssh root@72.60.254.100 < verifica-banco.sh

cd /var/www/html/wk-crm-laravel

echo "====================================="
echo "  DIAGNÓSTICO DO BANCO DE DADOS"
echo "====================================="
echo ""

echo "1. CONFIGURAÇÃO DO .env"
echo "------------------------"
grep "^DB_" .env
echo ""

echo "2. POSTGRESQL ESTÁ RODANDO?"
echo "----------------------------"
if ps aux | grep -v grep | grep postgres > /dev/null; then
    echo "✅ SIM - PostgreSQL está rodando"
    ps aux | grep postgres | grep -v grep | head -3
else
    echo "❌ NÃO - PostgreSQL não está rodando"
fi
echo ""

echo "3. PORTA 5432 ESTÁ ABERTA?"
echo "---------------------------"
if netstat -tuln | grep 5432 > /dev/null; then
    echo "✅ SIM - Porta 5432 está aberta"
    netstat -tuln | grep 5432
else
    echo "❌ NÃO - Porta 5432 não está aberta"
fi
echo ""

echo "4. TESTE DE CONEXÃO"
echo "-------------------"
php artisan tinker --execute="
try {
    \$pdo = DB::connection()->getPdo();
    echo '✅ CONEXÃO OK\n';
    echo 'Database: ' . \$pdo->query('SELECT current_database()')->fetchColumn() . '\n';
} catch (Exception \$e) {
    echo '❌ CONEXÃO FALHOU\n';
    echo 'Erro: ' . \$e->getMessage() . '\n';
}
"
echo ""

echo "5. CONTAGEM DE REGISTROS"
echo "------------------------"
php artisan tinker --execute="
try {
    echo 'Usuários: ' . App\Models\User::count() . '\n';
    echo 'Oportunidades: ' . App\Models\Opportunity::count() . '\n';
    echo 'Notificações: ' . App\Models\Notification::count() . '\n';
} catch (Exception \$e) {
    echo 'Erro: ' . \$e->getMessage() . '\n';
}
"
echo ""

echo "====================================="
echo "  ANÁLISE"
echo "====================================="
echo ""

# Verificar se DB_HOST está como postgres
if grep "^DB_HOST=postgres" .env > /dev/null; then
    echo "⚠️  PROBLEMA ENCONTRADO!"
    echo ""
    echo "DB_HOST está configurado como 'postgres' (nome de container Docker)"
    echo "Mas a VPS não está usando Docker."
    echo ""
    echo "SOLUÇÃO:"
    echo "--------"
    echo "1. Edite o arquivo .env:"
    echo "   nano .env"
    echo ""
    echo "2. Altere a linha:"
    echo "   DB_HOST=postgres"
    echo ""
    echo "3. Para:"
    echo "   DB_HOST=localhost"
    echo ""
    echo "4. Salve e execute:"
    echo "   php artisan config:clear"
    echo "   php artisan config:cache"
    echo ""
fi

echo "====================================="
