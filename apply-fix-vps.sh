#!/bin/bash
set -e

echo "🔄 Aplicando correção do modal de IA..."

cd /opt/wk-crm

echo "📥 Fazendo git pull..."
git pull origin main

echo "📦 Descompactando assets..."
mkdir -p /tmp/customer-app-fix
cd /tmp/customer-app-fix
tar -xzf ~/customer-app-fix.tar.gz

echo "📋 Copiando para Laravel..."
cp -f /tmp/customer-app-fix/index.html /opt/wk-crm/wk-crm-laravel/public/customer-app/index.html
cp -rf /tmp/customer-app-fix/assets/* /opt/wk-crm/wk-crm-laravel/public/assets/

echo "🧹 Limpando arquivos temporários..."
rm -rf /tmp/customer-app-fix

echo "🔄 Reiniciando Laravel..."
cd /opt/wk-crm
docker compose restart wk-crm-laravel

echo "✅ Deploy concluído!"
echo "🌐 Teste em: https://app.consultoriawk.com"
