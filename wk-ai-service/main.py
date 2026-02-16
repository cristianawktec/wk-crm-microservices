import os
from typing import Optional
import concurrent.futures

import google.generativeai as genai
from fastapi import FastAPI
from pydantic import BaseModel, Field

app = FastAPI(title="WK AI Service", version="0.1.0")


class OpportunityInput(BaseModel):
    id: Optional[str] = None
    title: str
    description: Optional[str] = None
    value: Optional[float] = Field(default=None, ge=0)
    probability: Optional[float] = Field(default=None, ge=0, le=100)
    status: Optional[str] = None
    customer_name: Optional[str] = None
    sector: Optional[str] = None


class OpportunityInsight(BaseModel):
    risk_score: float = Field(ge=0, le=1)
    risk_label: str
    next_action: str
    recommendation: str
    summary: str
    model: str
    cached: bool = False


class ChatRequest(BaseModel):
    question: str
    context: Optional[dict] = None
    api_key: Optional[str] = None


class ChatResponse(BaseModel):
    answer: str
    model: str
    source: str = "ai_service"


def get_model():
    api_key = os.getenv("GEMINI_API_KEY")
    model_override = os.getenv("GEMINI_MODEL")
    print(f"DEBUG: GEMINI_API_KEY present: {bool(api_key)}, length: {len(api_key) if api_key else 0}")
    if not api_key:
        print("ERROR: GEMINI_API_KEY not found")
        return None
    try:
        genai.configure(api_key=api_key)
        # Try models in order of availability (AI Studio 2026)
        model_names = []
        if model_override:
            model_names.append(model_override)
        model_names.extend([
            "models/gemini-flash-latest",    # Current working model
            "models/gemini-2.5-flash",
            "models/gemini-2.0-flash-001",
            "gemini-2.0-flash",
            "models/gemini-pro-latest",
        ])
        for model_name in model_names:
            try:
                model = genai.GenerativeModel(model_name)
                print(f"SUCCESS: Gemini model initialized with {model_name}")
                return model
            except Exception as model_error:
                print(f"Failed to init {model_name}: {str(model_error)}")
                continue
        print("ERROR: No available Gemini model found")
        return None
    except Exception as e:
        print(f"ERROR initializing Gemini: {str(e)}")
        import traceback
        traceback.print_exc()
        return None


def build_prompt(payload: OpportunityInput) -> str:
    return (
        "You are a CRM sales assistant. Given an opportunity,"
        " return: risk_score (0-1), risk_label, next_action, recommendation, summary.\n"
        f"Title: {payload.title}\n"
        f"Description: {payload.description or 'n/a'}\n"
        f"Value: {payload.value or 'n/a'}\n"
        f"Probability: {payload.probability or 'n/a'}%\n"
        f"Status: {payload.status or 'n/a'}\n"
        f"Customer: {payload.customer_name or 'n/a'}\n"
        f"Sector: {payload.sector or 'n/a'}\n"
        "Respond in JSON with keys: risk_score, risk_label, next_action, recommendation, summary."
    )


def parse_response(text: str) -> OpportunityInsight:
    # Very small parser: assume the model returns JSON-like content.
    import json

    try:
        data = json.loads(text)
        return OpportunityInsight(
            risk_score=float(data.get("risk_score", 0.4)),
            risk_label=str(data.get("risk_label", "medium")),
            next_action=str(data.get("next_action", "Reengajar o cliente")),
            recommendation=str(data.get("recommendation", "Envie um follow-up com proposta revisada.")),
            summary=str(data.get("summary", "Oportunidade com risco moderado.")),
            model="gemini-pro",
        )
    except Exception:
        # Fallback if parsing fails
        return OpportunityInsight(
            risk_score=0.4,
            risk_label="medium",
            next_action="Reengajar o cliente com próxima reunião",
            recommendation="Envie um follow-up reforçando o valor e prazos.",
            summary="Oportunidade com risco moderado; avance no relacionamento.",
            model="gemini-pro",
        )


def generate_insight(payload: OpportunityInput) -> OpportunityInsight:
    model = get_model()
    if not model:
        print("ERROR: GEMINI_API_KEY not configured")
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
        print(f"Calling Gemini for opportunity: {payload.title}")
        timeout_seconds = int(os.getenv("GEMINI_TIMEOUT_SECONDS", "12"))
        generation_config = {
            "temperature": 0.3,
            "max_output_tokens": 256,
        }

        with concurrent.futures.ThreadPoolExecutor(max_workers=1) as executor:
            future = executor.submit(model.generate_content, prompt, generation_config)
            result = future.result(timeout=timeout_seconds)
        text = result.text or "{}"
        print(f"Gemini response received: {text[:100]}...")
        return parse_response(text)
    except concurrent.futures.TimeoutError:
        print("ERROR: Gemini request timed out")
        return OpportunityInsight(
            risk_score=0.5,
            risk_label="unknown",
            next_action="Solicitar mais contexto ao cliente",
            recommendation="Tente novamente; o provedor de IA demorou para responder.",
            summary="Falha por timeout no provedor; usando fallback.",
            model="gemini-timeout",
            cached=True,
        )
    except Exception as e:
        print(f"ERROR calling Gemini: {str(e)}")
        import traceback
        traceback.print_exc()
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
            context_str += f"User ID: {context['user_id']}\n"
        if "timestamp" in context:
            context_str += f"Time: {context['timestamp']}\n"
    
    prompt = (
        "You are a helpful CRM sales assistant for WK CRM platform. "
        "Answer the user's question about sales, opportunities, trends, and business insights. "
        "Keep responses concise, actionable, and in Portuguese (Brasil). "
        "Use markdown formatting when helpful.\n\n"
        f"Context:\n{context_str}\n"
        f"Question: {question}\n\n"
        "Provide a helpful, specific answer."
    )
    
    try:
        if not model:
            return (
                "Desculpe, não consegui processar sua pergunta no momento. "
                "O serviço de IA não está configurado. "
                "Por favor, tente novamente mais tarde ou entre em contato com o suporte."
            )

        timeout_seconds = int(os.getenv("GEMINI_TIMEOUT_SECONDS", "12"))
        generation_config = {
            "temperature": 0.3,
            "max_output_tokens": 256,
        }

        with concurrent.futures.ThreadPoolExecutor(max_workers=1) as executor:
            future = executor.submit(model.generate_content, prompt, generation_config)
            result = future.result(timeout=timeout_seconds)

        return result.text or "Não consegui gerar uma resposta."
    except concurrent.futures.TimeoutError:
        return (
            "O provedor de IA demorou para responder. "
            "Tente novamente em alguns instantes."
        )
    except Exception as e:
        print(f"Error generating chat response: {str(e)}")
        return (
            "Desculpe, ocorreu um erro ao processar sua pergunta. "
            "Por favor, tente novamente com uma pergunta mais específica."
        )


@app.get("/health")
async def health():
    return {"status": "ok", "service": "wk-ai-service"}


@app.get("/")
async def root():
    return {"message": "WK AI Service - ready"}


@app.post("/ai/opportunity-insights", response_model=OpportunityInsight)
async def opportunity_insights(payload: OpportunityInput):
    return generate_insight(payload)


@app.post("/api/v1/chat", response_model=ChatResponse)
async def chat(request: ChatRequest):
    """Handle chat messages and generate AI responses"""
    answer = generate_chat_response(request.question, request.context)
    return ChatResponse(
        answer=answer,
        model="gemini-pro",
        source="ai_service"
    )
