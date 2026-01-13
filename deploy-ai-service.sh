#!/bin/bash
# Deploy wk-ai-service na VPS
# Execute em: /var/www/wk-crm-api

echo "🚀 Iniciando deploy da AI Service na VPS..."
echo "================================================"

# 1. Parar serviço anterior se estiver rodando
echo "1️⃣ Parando serviço anterior..."
pkill -f "python.*server.py" || echo "Nenhum serviço anterior encontrado"
sleep 2

# 2. Fazer pull do repositório
echo "2️⃣ Fazendo pull do repositório..."
cd /var/www/wk-crm-api
git pull origin main

# 3. Verificar se Python está disponível
echo "3️⃣ Verificando Python..."
python --version || python3 --version

# 4. Iniciar serviço AI
echo "4️⃣ Iniciando WK AI Service na porta 8001..."
cd /var/www/wk-crm-api/wk-ai-service

# Criar log directory se não existir
mkdir -p /var/log/wk-ai-service

# Iniciar em background com nohup
nohup python server.py > /var/log/wk-ai-service/service.log 2>&1 &

sleep 2

# 5. Verificar se o serviço está rodando
echo "5️⃣ Verificando se o serviço está rodando..."
if netstat -tlnp | grep -q ":8000"; then
    echo "✅ Serviço AI rodando na porta 8000"
else
    echo "⚠️  Porta 8000 não encontrada. Verifique o log:"
    tail -20 /var/log/wk-ai-service/service.log
fi

# 6. Configurar Nginx (se necessário)
echo "6️⃣ Verificando configuração Nginx..."
if ! grep -q "wk-ai-service" /etc/nginx/sites-available/api.consultoriawk.com; then
    echo "⚠️  Nginx ainda não está configurado para AI Service"
    echo "   Execute manualmente para atualizar Nginx reverse proxy"
else
    echo "✅ Nginx já está configurado"
fi

# 7. Recarregar Nginx
echo "7️⃣ Recarregando Nginx..."
nginx -s reload || sudo nginx -s reload || echo "⚠️  Falha ao recarregar Nginx (execute como sudo)"

echo "================================================"
echo "✅ Deploy finalizado!"
echo ""
echo "📊 Status do serviço:"
echo "   Porta: 8000 (local) ou 8001 (via reverse proxy)"
echo "   Log: /var/log/wk-ai-service/service.log"
echo ""
echo "🧪 Testar endpoints:"
echo "   curl http://localhost:8000/health"
echo "   curl http://api.consultoriawk.com:8001/health (via Nginx)"
