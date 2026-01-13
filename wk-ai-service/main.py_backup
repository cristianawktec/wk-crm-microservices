import os
from typing import Optional
import json
import logging
from datetime import datetime, timedelta

import google.generativeai as genai
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field
from fastapi.middleware.cors import CORSMiddleware

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(title="WK AI Service", version="1.0.0")

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Simple in-memory cache (in production, use Redis)
_insight_cache = {}


class OpportunityInput(BaseModel):
    """Input data for opportunity analysis"""
    id: Optional[str] = None
    title: str = Field(..., min_length=3, max_length=255)
    description: Optional[str] = None
    value: Optional[float] = Field(default=None, ge=0)
    probability: Optional[float] = Field(default=None, ge=0, le=100)
    status: Optional[str] = None
    customer_name: Optional[str] = None
    sector: Optional[str] = None


class OpportunityInsight(BaseModel):
    """AI-generated insight for an opportunity"""
    risk_score: float = Field(ge=0, le=100)
    risk_label: str
    next_action: str
    recommendation: str
    summary: str
    model: str
    cached: bool = False


class ChatRequest(BaseModel):
    """Chat request from frontend"""
    question: str = Field(..., min_length=3)
    context: Optional[dict] = None
    api_key: Optional[str] = None


class ChatResponse(BaseModel):
    """Chat response from AI"""
    answer: str
    model: str
    source: str = "ai_service"


def get_model():
    """Get Gemini model instance"""
    api_key = os.getenv("GEMINI_API_KEY")
    if not api_key:
        logger.warning("GEMINI_API_KEY not configured")
        return None
    try:
        genai.configure(api_key=api_key)
        return genai.GenerativeModel("gemini-pro")
    except Exception as e:
        logger.error(f"Failed to configure Gemini: {e}")
        return None


def build_prompt(payload: OpportunityInput) -> str:
    """Build a detailed prompt for Gemini to analyze an opportunity"""
    return f"""Você é um assistente de vendas especializado em CRM. Analise a oportunidade abaixo e retorne uma análise de risco.

OPORTUNIDADE:
- Título: {payload.title}
- Descrição: {payload.description or 'Não informada'}
- Valor: R$ {payload.value or 'N/A'}
- Probabilidade: {payload.probability or 'N/A'}%
- Status: {payload.status or 'Não informado'}
- Cliente: {payload.customer_name or 'Não informado'}
- Setor: {payload.sector or 'Não informado'}

TAREFA:
Analise essa oportunidade e retorne um JSON VÁLIDO com as seguintes chaves:
- risk_score (0-100): score de risco numérico
- ri"""Parse Gemini response and extract risk analysis"""
    try:
        # Try to extract JSON from response (may contain markdown or extra text)
        text = text.strip()
        
        # If wrapped in markdown code blocks, extract content
        if "```json" in text:
            text = text.split("```json")[1].split("```")[0].strip()
        elif "```" in text:
            text = text.split("```")[1].split("```")[0].strip()
        
        data = json.loads(text)
        
        # Validate and normalize risk_score (0-100 range)
        risk_score = float(data.get("risk_score", 50))
        if risk_score > 1:
            # Already in 0-100 range
            risk_score = max(0, min(100, risk_score))
        else:
            # Convert from 0-1 to 0-100
            risk_score = risk_score * 100
        
        # Map risk_label if needed
        risk_label = str(data.get("risk_label", "médio")).lower().strip()
        if risk_label not in ["baixo", "médio", "alto"]:
            if risk_score < 33:
                risk_label = "baixo"
            elif risk_score < 66:
                risk_label = "médio"
            else:
                risk_label = "alto"
        
        return OpportunityInsight(
            risk_score=risk_score,
            risk_label=risk_label,
            next_action=str(data.get("next_action", "Agendar reunião de acompanhamento")),
            recommendation=str(data.get("recommendation", "Envie um follow-up personalizado.")),
    """Generate risk insight for an opportunity using Gemini"""
    
    # Check cache first
    cache_key = f"{payload.title}:{payload.value}:{payload.probability}"
    if cache_key in _insight_cache:
        cached_insight, cache_time = _insight_cache[cache_key]
        # Cache for 1 hour
        if datetime.now() - cache_time < timedelta(hours=1):
            logger.info(f"Cache hit for opportunity: {payload.title}")
            cached_insight.cached = True
            return cached_insight
    
    model = get_model()
    if not model:
        logger.warning("GEMINI_API_KEY not configured, returning default response")
        return OpportunityInsight(
            risk_score=50,
            risk_label="médio",
            next_action="Agendar reunião com o cliente",
            recommendation="Configure GEMINI_API_KEY para análises com IA real.",
            summary="Serviço de IA não configurado; usando análise padrão.",
            model="gemini-fallback",
            cached=True,
        )

    prompt = build_prompt(payload)
    try:
        logger.info(f"Calling Gemini API for opportunity: {payload.title}")
        result = model.generate_content(prompt)
        text = result.text or "{}"
        insight = parse_response(text)
        
        # Store in cache
        _insight_cache[cache_key] = (insight, datetime.now())
        logger.info(f"Insight generated successfully: {payload.title}")
        return insight
        
    except Exception as e:
        logger.error(f"Error generating insight: {e}")
        return OpportunityInsight(
            risk_score=50,
            risk_label="médio",
            next_action="Solicitar mais informações ao cliente",
            recommendation="Revise os dados da oportunidade e tente novamente.",
            summary="Erro ao gerar análise; usando valores padrão
        )


def generate_insight(payload: OpportunityInput) -> OpportunityInsight:
    model = get_model()
    if not model:
        return OpportunityInsight(
            risk_score=0.4,
            risk_label="medium",
            next_action="Reengajar o cliente",
            recommendation="Configurar GEMINI_API_KEY para respostas reais.",
            summary="Stub sem GEMINI_API_KEY; retornando valores padrão.",
            model="stub",
            cached=True,
        )

    prompt = build_prompt(payload)
    try:
        result = model.generate_content(prompt)
        text = result.text or "{}"
        return parse_response(text)
    except Exception:
        return OpportunityInsight(
            risk_score=0.5,
            risk_label="unknown",
            next_action="Solicitar mais contexto ao cliente",
            recommendation="Tente novamente ou revise os dados da oportunidade.",
            summary="Falha ao consultar o modelo; usando fallback.",
            model="gemini-pro",
            cached=True,
        )


def generate_chat_response(question: str, context: Optional[dict] = None) -> str:
    """Generate a response for a chat question using Gemini AI"""
    model = get_model()
    
    # Build context string
    context_str = ""
    if context:
        if "user_id" in context:
            context_str += f"ID do Usuário: {context['user_id']}\n"
        if "timestamp" in context:
            context_str += f"Hora: {context['timestamp']}\n"
        if "opportunity_id" in context:
            context_str += f"Oportunidade: {context['opportunity_id']}\n"
    
    prompt = (
        "Você é um assistente de vendas especializado da plataforma WK CRM. "
        "Responda as perguntas do usuário sobre vendas, oportunidades, tendências e insights de negócios. "
        "Mantenha as respostas concisas, acionáveis e em português brasileiro. "
        "Use formatação markdown quando apropriado.\n\n"
        f"Contexto:\n{context_str}\n"
        f"Pergunta do usuário: {question}\n\n"
        "Forneça uma resposta útil e específica."
    )
    
    try:
        if not model:
            return (
                "Desculpe, não consegui processar sua pergunta no momento. "
                "O serviço de IA não está configurado (GEMINI_API_KEY ausente). "
                "Por favor, tente novamente mais tarde ou entre em contato com o suporte."
            )
        
        result = model.generate_content(prompt)
        response = result.text or "Não consegui gerar uma resposta."
        logger.info(f"Chat response generated successfully")
        return response
    except Exception as e:
        logger.error(f"Error generating chat response: {e}")
        return (
            "Desculpe, ocorreu um erro ao processar sua pergunta. "
            "Por favor, tente novamente com uma pergunta mais específica."
        )


@app.get("/health")
async def health():
    """Health check endpoint"""
    gemini_configured = os.getenv("GEMINI_API_KEY") is not None
    return {
        "status": "ok",
        "service": "wk-ai-service",
        "version": "1.0.0",
        "gemini_configured": gemini_configured,
        "timestamp": datetime.now().isoformat()
    }


@app.get("/")
async def root():
    """Root endpoint"""
    return {
        "message": "WK AI Service - Plataforma de Inteligência Artificial para CRM",
        "version": "1.0.0",
        "endpoints": {
            "analyze": "POST /analyze - Análise de risco de oportunidade",
            "chat": "POST /chat - Chat com assistente de IA",
            "health": "GET /health - Status do serviço"
        }
    }


@app.post("/analyze", response_model=OpportunityInsight)
async def analyze_opportunity(payload: OpportunityInput):
    """
    Analyze an opportunity and return risk assessment.
    
    Input:
    - title: Título da oportunidade
    - value: Valor da oportunidade
    - probability: Probabilidade de fechamento (0-100)
    - status: Status atual (aberta, negociação, proposta, ganha, perdida)
    - customer_name: Nome do cliente
    - sector: Setor/indústria
    
    Output:
    - risk_score: Pontuação de risco (0-100)
    - risk_label: Classificação (baixo, médio, alto)
    - next_action: Próxima ação recomendada
    - recommendation: Recomendação específica
    - summary: Resumo executivo
    """
    try:
        insight = generate_insight(payload)
        logger.info(f"Analysis completed for opportunity: {payload.title}")
        return insight
    except Exception as e:
        logger.error(f"Error in analyze_opportunity: {e}")
        raise HTTPException(status_code=500, detail="Failed to analyze opportunity")


@app.post("/api/v1/chat", response_model=ChatResponse)
async def chat(request: ChatRequest):
    """
    Handle chat messages and generate AI responses.
    
    Input:
    - question: Pergunta do usuário
    - context: Contexto adicional (user_id, opportunity_id, etc.)
    """
    try:
        if not request.question or len(request.question.strip()) < 3:
            raise HTTPException(status_code=400, detail="Question must be at least 3 characters")
        
        answer = generate_chat_response(request.question, request.context)
        return ChatResponse(
            answer=answer,
            model="gemini-pro",
            source="ai_service"
        )
    except Exception as e:
        logger.error(f"Error in chat: {e}")
        raise HTTPException(status_code=500, detail="Failed to process chat message")


# Legacy endpoints for backward compatibility
@app.post("/ai/opportunity-insights", response_model=OpportunityInsight)
async def opportunity_insights(payload: OpportunityInput):
    """Legacy endpoint - use /analyze instead"""
    return await analyze_opportunity(payload)
