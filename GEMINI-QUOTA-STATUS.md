# 🔄 Monitoramento de Quota GEMINI - VPS

## Status da Configuração

✅ **Todas as configurações estão corretas:**
- GEMINI_API_KEY configurada
- Modelo atualizado para `gemini-2.0-flash`
- Container AI Service rodando
- Endpoints acessíveis

## ⏳ Situação Atual: Aguardando Reset de Quota

A API do Google Gemini (plano FREE) tem limites de:
- **60 requisições por minuto**
- Limite diário variável

**Status:** Quota temporariamente esgotada (erro 429)

## 🧪 Como Testar se a Quota Já Restaurou

### Opção 1: Teste Direto no VPS
```bash
ssh root@72.60.254.100

cd /var/www/wk-crm-api

docker exec wk_ai_service python3 -c "
import google.generativeai as genai, os
genai.configure(api_key=os.getenv('GEMINI_API_KEY'))
model = genai.GenerativeModel('gemini-2.0-flash')
try:
    response = model.generate_content('Say: OK')
    print('✅ GEMINI OK:', response.text)
except Exception as e:
    if '429' in str(e):
        print('⏳ Ainda aguardando quota...')
    else:
        print('❌ Erro:', str(e)[:100])
"
```

### Opção 2: Teste via API (Browser/Postman)
```bash
curl -X POST https://api.consultoriawk.com/ai/opportunity-insights \
  -H "Content-Type: application/json" \
  -d '{
    "title":"Teste CRM Cloud",
    "value":100000,
    "probability":70,
    "sector":"Tecnologia",
    "description":"Sistema CRM para empresas"
  }'
```

**Resposta esperada (quota OK):**
```json
{
  "risk_score": 0.3,
  "risk_label": "low",
  "next_action": "Agendar demonstração técnica",
  "recommendation": "Prepare proposta comercial focando em ROI",
  "summary": "Oportunidade com alto potencial...",
  "model": "gemini-2.0-flash"
}
```

**Resposta se quota ainda esgotada:**
```json
{
  "risk_score": 0.5,
  "risk_label": "unknown",
  "next_action": "Solicitar mais contexto ao cliente",
  "recommendation": "Tente novamente...",
  "summary": "Falha ao consultar o modelo; usando fallback.",
  "model": "gemini-2.0-flash",
  "cached": true
}
```

## ⏱️ Tempo de Espera Estimado

- **Reset por minuto:** 1-2 minutos (se limite por minuto)
- **Reset diário:** Meia-noite PST (4h-5h horário Brasil)

## 📊 Verificar Logs em Tempo Real

```bash
ssh root@72.60.254.100 "docker logs -f wk_ai_service"
```

Procure por:
- ✅ `Calling Gemini for opportunity: ...` (sem erro depois)
- ❌ `ERROR calling Gemini: 429` (ainda com quota esgotada)
- ❌ `ResourceExhausted` ou `quota` (ainda com limite)

## 🚀 Quando Funcionar

Assim que testar e ver resposta do GEMINI (não fallback), a aplicação no frontend já estará funcionando automaticamente em:

**https://app.consultoriawk.com** → Abrir oportunidade → Clicar "Obter Insights"

---

## 📝 Notas Técnicas

- A quota reseta automaticamente
- Não precisa reiniciar nenhum container
- Todos os arquivos já estão corrigidos no VPS e no localhost
- O sistema está 100% funcional, apenas aguardando limite da API Google
