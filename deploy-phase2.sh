#!/bin/bash

# Deploy Phase 2 - AI Integration to VPS

echo "🚀 DEPLOYING PHASE 2 - AI INTEGRATION"
echo "====================================="
echo ""

VPS_IP="72.60.254.100"
REPO_URL="https://github.com/cristianawktec/wk-crm-microservices.git"
DEPLOY_PATH="/var/www/wk-crm-api"

echo "1️⃣ Conectando ao VPS..."
ssh root@$VPS_IP "cd $DEPLOY_PATH && git pull origin main" && echo "✅ Git pull completo"

echo ""
echo "2️⃣ Rodando migrations Laravel..."
ssh root@$VPS_IP "cd $DEPLOY_PATH/wk-crm-laravel && php artisan migrate --force" && echo "✅ Migrations completas"

echo ""
echo "3️⃣ Limpando cache..."
ssh root@$VPS_IP "cd $DEPLOY_PATH/wk-crm-laravel && php artisan cache:clear && php artisan config:cache" && echo "✅ Cache limpo"

echo ""
echo "4️⃣ Testando endpoints AI..."
echo ""

# Teste health endpoint
echo "Testando GET /api/ai/health..."
curl -s https://api.consultoriawk.com/api/ai/health | jq '.'

echo ""
echo "✅ DEPLOY PHASE 2 COMPLETO!"
echo ""
echo "📍 Novos Endpoints:"
echo "  POST /api/opportunities/{id}/ai-analysis - Analisar oportunidade com IA"
echo "  GET /api/opportunities/{id}/ai-analysis - Ver análises anteriores"
echo "  POST /api/ai/chat - Chat com IA"
echo "  GET /api/ai/health - Status do serviço AI"
echo ""
