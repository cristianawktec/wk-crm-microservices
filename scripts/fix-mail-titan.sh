#!/bin/sh
set -e

# Atualiza MAIL_* no .env para Titan SMTP (SSL 465)
# Executar no host: bash /tmp/fix-mail-titan.sh

echo "==> Atualizando MAIL_* para Titan SMTP (smtp0101:465 ssl)"

docker exec wk_crm_laravel sh -c '
cd /var/www/html
cp .env .env.bak.$(date +%s)
# Remove linhas MAIL_ existentes
grep -v "^MAIL_" .env > /tmp/base.env || true
# Adiciona novas
cat > /tmp/mail.env << "EOFMAIL"
MAIL_MAILER=smtp
MAIL_HOST=smtp0101.titan.email
MAIL_PORT=465
MAIL_USERNAME=noreply@consultoriawk.com
MAIL_PASSWORD="cris1tian#"
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@consultoriawk.com
MAIL_FROM_NAME="WK CRM"
EOFMAIL
cat /tmp/base.env /tmp/mail.env > .env
rm -f /tmp/base.env /tmp/mail.env
# Mostrar resultado
grep "^MAIL_" .env
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
'

echo "==> Reiniciando container wk_crm_laravel"
docker restart wk_crm_laravel
sleep 2

echo "==> Pronto"