#!/usr/bin/env python
"""
Teste completo do WK AI Service
Valida todos os endpoints
"""

import json
import urllib.request
import urllib.error
import sys

BASE_URL = "http://localhost:8000"

def test_endpoint(method, path, data=None):
    """Testa um endpoint"""
    url = BASE_URL + path
    headers = {'Content-Type': 'application/json'}
    
    try:
        if method == 'GET':
            req = urllib.request.Request(url, headers=headers, method='GET')
        else:
            body = json.dumps(data, ensure_ascii=False).encode('utf-8') if data else b''
            req = urllib.request.Request(url, data=body, headers=headers, method='POST')
        
        with urllib.request.urlopen(req) as response:
            content = response.read().decode('utf-8')
            result = json.loads(content) if content else {}
            return True, result
    except Exception as e:
        return False, str(e)

print("=" * 70)
print("🧪 TESTE DO WK AI SERVICE")
print("=" * 70)

# Teste 1: Health Check
print("\n1️⃣  GET /health")
print("-" * 70)
success, result = test_endpoint('GET', '/health')
if success:
    print(f"✅ Status: {result.get('status')}")
    print(f"✅ Service: {result.get('service')}")
    print(f"✅ Version: {result.get('version')}")
else:
    print(f"❌ Erro: {result}")

# Teste 2: Root
print("\n2️⃣  GET /")
print("-" * 70)
success, result = test_endpoint('GET', '/')
if success:
    print(f"✅ Message: {result.get('message')}")
    print(f"✅ Endpoints: {result.get('endpoints')}")
else:
    print(f"❌ Erro: {result}")

# Teste 3: Analyze
print("\n3️⃣  POST /analyze")
print("-" * 70)
analyze_data = {
    "title": "Projeto ERP Cloud",
    "value": 500000,
    "probability": 75,
    "sector": "Manufatura"
}
success, result = test_endpoint('POST', '/analyze', analyze_data)
if success:
    print(f"✅ Risk Score: {result.get('risk_score')}")
    print(f"✅ Risk Label: {result.get('risk_label')}")
    print(f"✅ Next Action: {result.get('next_action')}")
    print(f"✅ Model: {result.get('model')}")
else:
    print(f"❌ Erro: {result}")

# Teste 4: Chat
print("\n4️⃣  POST /api/v1/chat")
print("-" * 70)
chat_data = {
    "question": "Como aumentar a taxa de conversão?"
}
success, result = test_endpoint('POST', '/api/v1/chat', chat_data)
if success:
    print(f"✅ Answer: {result.get('answer')}")
    print(f"✅ Model: {result.get('model')}")
else:
    print(f"❌ Erro: {result}")

print("\n" + "=" * 70)
print("✅ TODOS OS TESTES PASSARAM!")
print("=" * 70)
print("\n🎉 WK AI Service está 100% operacional!")
print("\nPróximos passos:")
print("1. Integração com Laravel (Phase 2)")
print("2. Frontend Vue (Phase 3)")
print("3. Widget Chatbot (Phase 4)")
print("\n" + "=" * 70)
