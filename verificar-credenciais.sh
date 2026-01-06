#!/bin/bash
# Execute na VPS: ssh root@72.60.254.100 < verificar-credenciais.sh

cd /var/www/html/wk-crm-laravel

echo "====================================="
echo "  VERIFICAÇÃO DE CREDENCIAIS"
echo "====================================="
echo ""

echo "1. CONFIGURAÇÃO ATUAL DO .env"
echo "------------------------------"
echo "DB_CONNECTION: $(grep '^DB_CONNECTION=' .env | cut -d'=' -f2)"
echo "DB_HOST: $(grep '^DB_HOST=' .env | cut -d'=' -f2)"
echo "DB_PORT: $(grep '^DB_PORT=' .env | cut -d'=' -f2)"
echo "DB_DATABASE: $(grep '^DB_DATABASE=' .env | cut -d'=' -f2)"
echo "DB_USERNAME: $(grep '^DB_USERNAME=' .env | cut -d'=' -f2)"
echo "DB_PASSWORD: $(grep '^DB_PASSWORD=' .env | cut -d'=' -f2 | sed 's/./*/g')"
echo ""

echo "2. VERIFICAR SE AS VARIÁVEIS ESTÃO VAZIAS"
echo "-----------------------------------------"

if ! grep -q '^DB_PASSWORD=.' .env; then
    echo "❌ DB_PASSWORD está VAZIA ou comentada!"
    echo ""
    echo "SOLUÇÃO: Adicione a senha do PostgreSQL"
else
    echo "✅ DB_PASSWORD está configurada"
fi

if ! grep -q '^DB_USERNAME=.' .env; then
    echo "❌ DB_USERNAME está VAZIO!"
else
    echo "✅ DB_USERNAME está configurado: $(grep '^DB_USERNAME=' .env | cut -d'=' -f2)"
fi

if ! grep -q '^DB_DATABASE=.' .env; then
    echo "❌ DB_DATABASE está VAZIO!"
else
    echo "✅ DB_DATABASE está configurado: $(grep '^DB_DATABASE=' .env | cut -d'=' -f2)"
fi
echo ""

echo "3. LISTAR DATABASES DISPONÍVEIS"
echo "--------------------------------"
sudo -u postgres psql -l | grep -E "Name|wk_crm|---"
echo ""

echo "4. LISTAR USUÁRIOS DO POSTGRESQL"
echo "--------------------------------"
sudo -u postgres psql -c "\du" | head -10
echo ""

echo "====================================="
echo "  INSTRUÇÕES"
echo "====================================="
echo ""
echo "Se DB_PASSWORD está vazia, você precisa:"
echo ""
echo "1. Ver qual senha foi usada na criação do banco"
echo "2. Ou resetar a senha do usuário PostgreSQL"
echo ""
echo "Para resetar a senha:"
echo "---------------------"
echo "sudo -u postgres psql"
echo "ALTER USER wk_crm_user WITH PASSWORD 'SUA_SENHA_AQUI';"
echo "\q"
echo ""
echo "Depois atualize o .env:"
echo "----------------------"
echo "nano .env"
echo "DB_PASSWORD=SUA_SENHA_AQUI"
echo ""
