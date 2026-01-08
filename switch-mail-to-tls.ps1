#!/usr/bin/env pwsh
# Atualiza MAIL_PORT/MAIL_ENCRYPTION para 587/tls dentro do container e limpa caches

$SERVER = "root@72.60.254.100"

$SCRIPT = @'
#!/bin/sh
set -e
cd /var/www/html
cp -f .env .env.bak || true

# Atualizar MAIL_PORT para 587
if grep -q '^MAIL_PORT=' .env; then
  sed -i -E 's/^MAIL_PORT=.*/MAIL_PORT=587/' .env
else
  echo 'MAIL_PORT=587' >> .env
fi

# Atualizar MAIL_ENCRYPTION para tls
if grep -q '^MAIL_ENCRYPTION=' .env; then
  sed -i -E 's/^MAIL_ENCRYPTION=.*/MAIL_ENCRYPTION=tls/' .env
else
  echo 'MAIL_ENCRYPTION=tls' >> .env
fi

php artisan config:clear || true
php artisan cache:clear || true
php artisan optimize:clear || true

# Mostrar variáveis
php -r 'echo "MAIL_PORT=".getenv("MAIL_PORT")."\n"; echo "MAIL_ENCRYPTION=".getenv("MAIL_ENCRYPTION")."\n";'
'@

# Enviar e executar no container
$SCRIPT | ssh $SERVER "cat > /tmp/switch-mail.sh"
ssh $SERVER "docker cp /tmp/switch-mail.sh wk_crm_laravel:/tmp/ && docker exec wk_crm_laravel sh /tmp/switch-mail.sh"