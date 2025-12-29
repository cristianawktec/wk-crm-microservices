import os
from typing import Optional

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


def get_model():
    api_key = os.getenv("GEMINI_API_KEY")
    if not api_key:
        return None
    genai.configure(api_key=api_key)
    return genai.GenerativeModel("gemini-pro")


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


@app.get("/health")
async def health():
    return {"status": "ok", "service": "wk-ai-service"}


@app.get("/")
async def root():
    return {"message": "WK AI Service - ready"}


@app.post("/ai/opportunity-insights", response_model=OpportunityInsight)
async def opportunity_insights(payload: OpportunityInput):
    return generate_insight(payload)
