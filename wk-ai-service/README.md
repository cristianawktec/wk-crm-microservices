# WK AI Service - FastAPI + Google Gemini

Serviço de inteligência artificial para análise de oportunidades de vendas e suporte via chatbot.

## 🚀 Funcionalidades

- **Análise de Risco de Oportunidades** - Pontuação automática usando IA
- **Chat com Assistente** - Responde perguntas sobre vendas e estratégia
- **Cache Inteligente** - Evita chamadas repetidas ao Gemini
- **CORS Habilitado** - Funciona com frontends em diferentes domínios
- **Logging Detalhado** - Rastreia todas as operações

## 📋 Requisitos

- Python 3.9+
- FastAPI
- Google Generative AI API (Gemini)
- Redis (opcional, para cache distribuído)

## ⚙️ Instalação

```bash
# 1. Instalar dependências
pip install -r requirements.txt

# 2. Configurar variáveis de ambiente
export GEMINI_API_KEY="sua_chave_aqui"

# 3. Iniciar o servidor
python main.py

# Ou com auto-reload para desenvolvimento:
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

## 🔑 Configuração

### Google Gemini API Key

Para usar a IA de verdade:

1. Acesse https://makersuite.google.com/app/apikeys
2. Crie uma chave de API
3. Configure como variável de ambiente:

```bash
export GEMINI_API_KEY="AIzaSyD..."
```

Ou adicione ao `.env`:

```env
GEMINI_API_KEY=AIzaSyD...
```

## 📡 Endpoints

### GET `/health`
Status do serviço
```bash
curl http://localhost:8000/health
```

### POST `/analyze`
Analisa uma oportunidade e retorna risco
```bash
curl -X POST http://localhost:8000/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Projeto ERP Cloud",
    "value": 250000,
    "probability": 65,
    "status": "proposal",
    "customer_name": "TechCorp",
    "sector": "Tecnologia"
  }'
```

**Response:**
```json
{
  "risk_score": 35,
  "risk_label": "baixo",
  "next_action": "Apresentar proposta técnica",
  "recommendation": "Agendar reunião com CTO para validar arquitetura",
  "summary": "Oportunidade de alto valor com boa probabilidade.",
  "model": "gemini-pro",
  "cached": false
}
```

### POST `/api/v1/chat`
Chat com assistente de IA
```bash
curl -X POST http://localhost:8000/api/v1/chat \
  -H "Content-Type: application/json" \
  -d '{
    "question": "Como aumentar taxa de conversão?",
    "context": {
      "user_id": "user-123"
    }
  }'
```

**Response:**
```json
{
  "answer": "A taxa de conversão pode ser aumentada através de...",
  "model": "gemini-pro",
  "source": "ai_service"
}
```

## 🧪 Testes

```bash
# Executar teste automatizado (requer serviço rodando)
python test_api.py

# Ou individual com curl:
curl http://localhost:8000/analyze -X POST -d '{"title":"Test","value":100}' -H "Content-Type: application/json"
```

## 📊 Estrutura de Risk Score

| Score | Label | Cor | Ação |
|-------|-------|-----|------|
| 0-33 | Baixo | 🟢 Verde | Prosseguir normalmente |
| 34-66 | Médio | 🟡 Amarelo | Acompanhar atentamente |
| 67-100 | Alto | 🔴 Vermelho | Ativar plano de recuperação |

## 🔄 Fluxo de Integração com Laravel

1. **Frontend Vue/Angular** → POST `/api/opportunities/{id}/ai-analysis`
2. **Laravel Controller** → Valida dados
3. **Guzzle HTTP Client** → Chama `http://wk-ai-service:8000/analyze`
4. **FastAPI** → Processa com Gemini
5. **Response** → Armazenada no DB
6. **Frontend** → Exibe score e recomendações

## 🛠️ Desenvolvimento

```bash
# Modo desenvolvimento com auto-reload
uvicorn main:app --reload

# Estrutura do projeto
wk-ai-service/
├── main.py              # Aplicação FastAPI
├── requirements.txt     # Dependências Python
├── test_api.py         # Suite de testes
├── README.md           # Documentação
└── Dockerfile          # Containerização
```

## 📦 Docker

```bash
# Build
docker build -t wk-ai-service .

# Run
docker run -e GEMINI_API_KEY="AIzaSyD..." -p 8000:8000 wk-ai-service

# Docker Compose (já incluído no projeto)
docker-compose up wk-ai-service
```

## ⚠️ Limitações e Considerações

- **Rate Limiting**: Google Gemini tem limites de taxa (revise planos de uso)
- **Timeout**: Respostas podem levar 5-10 segundos
- **Cache**: Em-memory por padrão (considere Redis para produção)
- **Custo**: Gemini é pago - monitore uso da API

## 🐛 Debug

Logs detalhados estão habilitados. Para aumentar verbosidade:

```python
import logging
logging.basicConfig(level=logging.DEBUG)
```

## 📝 Changelog

### v1.0.0 (01/12/2026)
- Endpoint `/analyze` para análise de oportunidades
- Endpoint `/api/v1/chat` para chat com IA
- Cache em memória
- Suporte completo ao Gemini Pro
- Logging detalhado
- CORS habilitado
- Test suite completa

## 🤝 Suporte

Para questões:
1. Verifique logs: `docker logs wk_ai_service`
2. Confirme GEMINI_API_KEY está configurada
3. Teste endpoints com `test_api.py`
4. Revise documentação do Google Gemini

## 📄 Licença

MIT
