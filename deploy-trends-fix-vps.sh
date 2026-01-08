#!/bin/bash
# Deploy fix auto-refresh token trends na VPS
set -e

echo "🚀 Deploy: Fix auto-refresh token trends"

# Atualizar código
cd /var/www/consultoriawk-crm
git fetch origin
git reset --hard origin/main
git pull origin main

# Verificar/corrigir configuração Nginx do app
echo "📝 Verificando configuração Nginx..."
if [ -f "app.consultoriawk.com.nginx.conf" ]; then
    cp app.consultoriawk.com.nginx.conf /etc/nginx/sites-available/app.consultoriawk.com
    ln -sf /etc/nginx/sites-available/app.consultoriawk.com /etc/nginx/sites-enabled/
    echo "✅ Configuração Nginx atualizada"
fi

# Testar configuração Nginx
nginx -t

# Build Vue Customer App
echo "📦 Building Vue Customer App..."
cd wk-customer-app
npm ci
npm run build

# Copiar dist para pasta app
echo "📁 Copiando arquivos para /var/www/consultoriawk-crm/app..."
rm -rf /var/www/consultoriawk-crm/app/*
cp -r dist/* /var/www/consultoriawk-crm/app/

# Ajustar permissões
chown -R www-data:www-data /var/www/consultoriawk-crm/app/
chmod -R 755 /var/www/consultoriawk-crm/app/

# Reload nginx
echo "🔄 Recarregando Nginx..."
systemctl reload nginx

echo ""
echo "✅ Deploy concluído!"
echo "🌐 Teste: https://app.consultoriawk.com/trends"
echo "🧪 Limpe cache do navegador (Ctrl+F5)"
