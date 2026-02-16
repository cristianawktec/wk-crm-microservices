#!/bin/bash
# Deploy updated main.py to VPS

echo "🚀 Atualizando código AI Service no VPS..."

ssh root@72.60.254.100 << 'ENDSSH'
cd /var/www/wk-crm-api/wk-ai-service

# Backup do arquivo atual
cp main.py main.py.backup

# Criar novo main.py com código atualizado
cat > main.py << 'ENDPYTHON'
ENDPYTHON

# Copiar conteúdo do main.py local
cat wk-ai-service/main.py >> deploy-main-py-vps.sh

cat >> deploy-main-py-vps.sh << 'ENDSCRIPT'
ENDPYTHON

echo "✅ Arquivo main.py atualizado"
echo "📦 Rebuilding AI Service container..."

cd /var/www/wk-crm-api
docker-compose build wk-ai-service
docker-compose stop wk-ai-service  
docker-compose rm -f wk-ai-service
docker-compose up -d wk-ai-service

echo "⏳ Aguardando container inicializar..."
sleep 10

echo "🧪 Testando GEMINI..."
docker exec wk_ai_service python3 -c "
import google.generativeai as genai, os
genai.configure(api_key=os.getenv('GEMINI_API_KEY'))
model = genai.GenerativeModel('models/gemini-flash-latest')
try:
    response = model.generate_content('Say: OK')
    print('✅ GEMINI FUNCIONANDO:', response.text)
except Exception as e:
    print('❌ Erro:', str(e)[:150])
"

echo ""
echo "✅ Atualização concluída!"
echo "Teste em: https://app.consultoriawk.com"
ENDSSH
ENDSCRIPT
