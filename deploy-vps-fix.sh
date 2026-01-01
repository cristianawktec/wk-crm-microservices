#!/bin/bash
# 🚀 Deploy Rápido VPS - Fix Logout + IA Insights
# Execute este script no VPS via SSH

set -e

echo "🚀 Iniciando deploy completo..."

# 1. Subir containers (se estiverem parados)
echo "📦 [1/5] Verificando e iniciando containers Docker..."
cd /var/www/html/wk-crm-laravel
docker compose up -d || {
    echo "⚠️  Containers com conflito, removendo e recriando..."
    docker rm -f wk_postgres wk_crm_laravel wk_ai_service 2>/dev/null || true
    docker compose up -d
}

# Aguardar containers iniciarem
sleep 5

# 2. Verificar status dos containers
echo "🔍 [2/5] Verificando status dos containers..."
docker compose ps

# 3. Atualizar código do backend
echo "📥 [3/5] Atualizando código Laravel..."
git pull origin main || echo "⚠️  Não é repositório git, pulando..."

# 4. Limpar caches do Laravel
echo "🧹 [4/5] Limpando caches do Laravel..."
docker compose exec -T wk-crm-laravel php artisan config:clear
docker compose exec -T wk-crm-laravel php artisan cache:clear
docker compose exec -T wk-crm-laravel php artisan route:clear

# 5. Verificar .env
echo "⚙️  [5/5] Verificando configuração .env..."
if ! grep -q "AI_SERVICE_URL" .env; then
    echo "AI_SERVICE_URL=http://wk-ai-service:8000" >> .env
    echo "✅ AI_SERVICE_URL adicionado ao .env"
    docker compose exec -T wk-crm-laravel php artisan config:clear
fi

echo ""
echo "✅ Deploy do backend concluído!"
echo ""
echo "📋 Próximo passo:"
echo "   No seu PC, copie os arquivos Vue:"
echo "   scp -r C:\\xampp\\htdocs\\crm\\wk-customer-app\\dist\\* root@72.60.254.100:/var/www/html/app/"
echo ""
echo "🌐 Depois teste em: https://app.consultoriawk.com"
