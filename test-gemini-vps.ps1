#!/usr/bin/env pwsh
# Script para testar quota do GEMINI no VPS

Write-Host "`n🔍 Testando GEMINI API no VPS...`n" -ForegroundColor Cyan

$testScript = @'
docker exec wk_ai_service python3 -c "
import google.generativeai as genai
import os

genai.configure(api_key=os.getenv('GEMINI_API_KEY'))
model = genai.GenerativeModel('gemini-2.0-flash')

try:
    response = model.generate_content('Say only: WORKING')
    print('✅ GEMINI FUNCIONANDO:', response.text)
except Exception as e:
    error = str(e)
    if 'quota' in error.lower() or '429' in error:
        print('⏳ Aguardando reset de quota...')
        print('   Tente novamente em alguns minutos')
    else:
        print('❌ ERRO:', error[:200])
"
'@

# Salvar script no VPS
$testScript | ssh root@72.60.254.100 "cat > /var/www/wk-crm-api/test-gemini.sh && chmod +x /var/www/wk-crm-api/test-gemini.sh && bash /var/www/wk-crm-api/test-gemini.sh"
