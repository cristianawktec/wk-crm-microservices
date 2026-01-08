#!/bin/bash
set -e

# Script para corrigir .env no servidor VPS
echo "==> Conectando no container wk_crm_laravel..."

docker exec wk_crm_laravel sh -c '
cd /var/www/html

# Backup
cp .env .env.backup.$(date +%s)

# Remover linhas MAIL_* antigas
grep -v "^MAIL_" .env > /tmp/env_clean

# Adicionar configurações MAIL corretas
cat >> /tmp/env_clean << "MAILEOF"
MAIL_MAILER=smtp
MAIL_HOST=smtp.titan.email
MAIL_PORT=587
MAIL_USERNAME=noreply@consultoriawk.com
MAIL_PASSWORD="cris1tian#"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@consultoriawk.com
MAIL_FROM_NAME="WK CRM"
MAILEOF

# Substituir .env
mv /tmp/env_clean .env

echo "==> .env atualizado com sucesso"
tail -8 .env
'

echo ""
echo "==> Limpando caches do Laravel..."
docker exec wk_crm_laravel php artisan config:clear
docker exec wk_crm_laravel php artisan cache:clear
docker exec wk_crm_laravel php artisan route:clear
docker exec wk_crm_laravel php artisan view:clear

echo ""
echo "==> Reiniciando container..."
docker restart wk_crm_laravel

echo ""
echo "==> Aguardando 5 segundos..."
sleep 5

echo ""
echo "==> Testando endpoint de login rápido..."
curl -i https://api.consultoriawk.com/api/auth/test-customer?role=admin

echo ""
echo "==> CONCLUÍDO!"
