#!/bin/bash
# Script para corrigir o .env no VPS
set -e

echo "==> Entrando no container..."
docker exec wk_crm_laravel sh -c '
cd /var/www/html
echo "==> Criando backup..."
cp .env .env.backup.$(date +%s)

echo "==> Removendo linhas MAIL_* antigas..."
grep -v "^MAIL_" .env > /tmp/base.env || true

echo "==> Adicionando novas configurações MAIL_*..."
cat > /tmp/mail.env << '\''EOF'\''
MAIL_MAILER=smtp
MAIL_HOST=smtp.titan.email
MAIL_PORT=587
MAIL_USERNAME=noreply@consultoriawk.com
MAIL_PASSWORD="cris1tian#"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@consultoriawk.com
MAIL_FROM_NAME="WK CRM"
EOF

cat /tmp/base.env /tmp/mail.env > .env
echo "==> .env atualizado! Últimas linhas:"
tail -10 .env

echo "==> Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

echo "==> Pronto!"
'

echo "==> Reiniciando container..."
docker restart wk_crm_laravel
sleep 3

echo "==> Testando endpoint..."
curl -sS -i https://api.consultoriawk.com/api/auth/test-customer?role=admin | head -20
