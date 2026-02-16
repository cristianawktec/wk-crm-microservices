#!/bin/bash
# Fix VPS GEMINI model to match localhost

cd /var/www/wk-crm-api

echo "🔧 Atualizando modelo GEMINI no VPS..."

# Update .env
sed -i 's/GEMINI_MODEL=.*/GEMINI_MODEL=models\/gemini-flash-latest/' .env

# Update docker-compose.yml  
sed -i 's/- GEMINI_MODEL=.*/- GEMINI_MODEL=models\/gemini-flash-latest/' docker-compose.yml

echo "✅ Arquivos atualizados:"
grep GEMINI_MODEL .env docker-compose.yml

echo ""
echo "🔄 Recriando container AI Service..."
docker-compose stop wk-ai-service
docker-compose rm -f wk-ai-service
docker-compose up -d wk-ai-service

echo ""
echo "⏳ Aguardando container ficar pronto..."
sleep 8

echo ""
echo "🧪 Testando GEMINI..."
docker exec wk_ai_service python3 -c "
import google.generativeai as genai, os
genai.configure(api_key=os.getenv('GEMINI_API_KEY'))
model = genai.GenerativeModel('models/gemini-flash-latest')
try:
    response = model.generate_content('Say only: WORKING')
    print('✅ GEMINI OK:', response.text)
except Exception as e:
    error_str = str(e)
    if '429' in error_str or 'quota' in error_str.lower():
        print('⏳ Quota limit - aguarde alguns minutos')
    else:
        print('❌ ERRO:', error_str[:150])
"

echo ""
echo "📋 Verificar logs:"
echo "docker logs wk_ai_service --tail 20"
