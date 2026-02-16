#!/bin/bash
set -e

echo "🔄 Iniciando deploy da correção do modal de IA..."

cd /opt/wk-crm

echo "📥 Fazendo git pull..."
git pull origin main

echo "🏗️  Rebuilding Vue app..."
cd wk-customer-app
npm run build

echo "📋 Copiando assets para Laravel..."
cd ..
cp -f wk-customer-app/dist/index.html wk-crm-laravel/public/customer-app/index.html
cp -rf wk-customer-app/dist/assets/* wk-crm-laravel/public/assets/

echo "🧹 Limpando arquivos antigos..."
cd wk-crm-laravel/public/assets
rm -f AiAnalysisModal-1d7ae0b3.js AiAnalysisModal-45c45e03.js 
rm -f AiAnalysisModal-51df15dd.js AiAnalysisModal-65f003c7.js AiAnalysisModal-bb91c635.js
rm -f index-5ee73346.js index-60d55afe.js index-a3ac2e85.js index-ac3bb454.js

echo "🔄 Reiniciando Laravel..."
cd /opt/wk-crm
docker compose restart wk-crm-laravel

echo "✅ Deploy concluído com sucesso!"
echo "🌐 Acesse: https://app.consultoriawk.com"
