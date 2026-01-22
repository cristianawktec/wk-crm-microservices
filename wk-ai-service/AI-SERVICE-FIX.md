# Correção do Serviço de IA - GEMINI_API_KEY

## Problema Identificado
Modal "Análise de IA" exibindo erro: **"Stub sem GEMINI_API_KEY: retornando valores padrão"**

## Causa Raiz
A variável `GEMINI_API_KEY` no arquivo `.env` continha um caractere extra "s" no início:
- ❌ **Incorreto:** `GEMINI_API_KEY=sAIzaSyBIK4hMMFNmSGVivAngyh5bp8apJ0luHBQ`
- ✅ **Correto:** `GEMINI_API_KEY=AIzaSyBIK4hMMFNmSGVivAngyh5bp8apJ0luHBQ`

## Correções Aplicadas

### 1. Ambiente Local
✅ Arquivo `wk-ai-service/.env` corrigido
✅ API KEY agora inicia corretamente com `AIzaSy...`

### 2. Servidor VPS (72.60.254.100)
✅ Arquivo `/var/www/wk-ai-service-test/.env` corrigido
✅ Container `wk_ai_service` recriado com `--env-file .env`
✅ Variável de ambiente carregada corretamente no container
✅ Serviço reiniciado e funcionando

### 3. Comandos Executados no VPS
```bash
# Corrigir .env
sed -i 's/GEMINI_API_KEY=sAIzaSyBIK4h/GEMINI_API_KEY=AIzaSyBIK4h/' /var/www/wk-ai-service-test/.env

# Remover container antigo
docker stop wk_ai_service && docker rm wk_ai_service

# Recriar container com env correto
docker run -d --name wk_ai_service \
  --env-file /var/www/wk-ai-service-test/.env \
  -p 8001:8000 \
  --health-cmd='curl -f http://localhost:8000/health || exit 1' \
  --health-interval=30s --health-timeout=10s --health-retries=3 \
  wk-crm-api-wk-ai-service
```

## Validação
✅ Container rodando com API KEY correta
✅ Variável confirmada: `docker exec wk_ai_service printenv GEMINI_API_KEY`
✅ Serviço respondendo em: http://localhost:8001
✅ Endpoint testado: `POST /ai/opportunity-insights`

## ⚠️ Problema Adicional Descoberto

Ao testar o serviço, descobrimos que a **API KEY do Google Gemini foi SUSPENSA**:

```
ERROR: 403 Permission denied: Consumer 'api_key:AIzaSyBIK4hMMFNmSGVivAngyh5bp8apJ0luHBQ' 
has been suspended. [reason: "CONSUMER_SUSPENDED"]
```

### Possíveis Causas da Suspensão:
1. **Violação dos Termos de Serviço** do Google Cloud/Gemini API
2. **Falta de pagamento** ou billing não configurado
3. **Limite de uso excedido** (free tier esgotado)
4. **API KEY vazada** ou uso indevido detectado

### Ações Necessárias:
1. 🔴 **Acessar Google Cloud Console**: https://console.cloud.google.com
2. 🔴 **Verificar o projeto**: `projects/948197590697`
3. 🔴 **Checar billing e pagamentos**
4. 🔴 **Criar nova API KEY** se necessário
5. 🔴 **Configurar billing** se ainda não configurado
6. 🔴 **Verificar limites de uso** da API

### Solução Temporária
O serviço está configurado com **fallback gracioso**:
- ✅ Quando a API KEY não funciona, retorna análises mock
- ✅ A aplicação continua funcionando (sem análises reais de IA)
- ✅ Usuário vê análises padrão em vez de erro

## Próximos Passos
1. 🔴 **URGENTE:** Resolver suspensão da API KEY do Google Gemini
2. ✅ Testar modal "Análise de IA" na aplicação (com dados mock)
3. ⏸️ Aguardar nova API KEY para testar análises reais
4. ✅ Monitorar logs para outros erros

## Data da Correção
22 de Janeiro de 2026 - 15:15 BRT

## Status
🟡 **PARCIALMENTE RESOLVIDO** 
- ✅ Configuração técnica corrigida (API KEY carregada)
- ✅ Serviço operacional com fallback
- 🔴 **API KEY do Google suspensa** - requer ação no Google Cloud Console
