#!/bin/bash
# Quick test script for WK AI Service

echo "🤖 WK AI Service - Quick Test"
echo "================================"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

BASE_URL="http://localhost:8000"

# Test 1: Health
echo -e "${YELLOW}1. Testing Health Endpoint${NC}"
curl -s "$BASE_URL/health" | jq . || echo "❌ Failed"
echo ""

# Test 2: Root
echo -e "${YELLOW}2. Testing Root Endpoint${NC}"
curl -s "$BASE_URL/" | jq . || echo "❌ Failed"
echo ""

# Test 3: Opportunity Analysis
echo -e "${YELLOW}3. Testing Opportunity Analysis${NC}"
curl -s -X POST "$BASE_URL/analyze" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Projeto ERP Cloud - Enterprise",
    "description": "Implementação completa de sistema ERP na nuvem",
    "value": 500000,
    "probability": 75,
    "status": "proposal",
    "customer_name": "Multinacional XYZ",
    "sector": "Manufatura"
  }' | jq . || echo "❌ Failed"
echo ""

# Test 4: Chat
echo -e "${YELLOW}4. Testing Chat Endpoint${NC}"
curl -s -X POST "$BASE_URL/api/v1/chat" \
  -H "Content-Type: application/json" \
  -d '{
    "question": "Qual é a melhor estratégia para fechar uma grande oportunidade?"
  }' | jq . || echo "❌ Failed"
echo ""

# Test 5: Legacy endpoint
echo -e "${YELLOW}5. Testing Legacy Endpoint (backward compatibility)${NC}"
curl -s -X POST "$BASE_URL/ai/opportunity-insights" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Suporte técnico",
    "value": 10000,
    "probability": 90
  }' | jq . || echo "❌ Failed"
echo ""

echo -e "${GREEN}✅ Tests completed!${NC}"
echo ""
echo "📊 Summary:"
echo "- If you see JSON responses above, the service is working!"
echo "- If risk_score is 50 and cached=true, GEMINI_API_KEY is not configured"
echo "- Configure it with: export GEMINI_API_KEY='your_key_here'"
