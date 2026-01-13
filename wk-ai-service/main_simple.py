#!/usr/bin/env python
"""
WK AI Service - Versão Simplificada (sem dependências externas)
Para testes e validação locais
"""

import json
import random
from http.server import HTTPServer, BaseHTTPRequestHandler
import urllib.parse
from datetime import datetime

class AIServiceHandler(BaseHTTPRequestHandler):
    def do_GET(self):
        """Handle GET requests"""
        if self.path == '/health':
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.end_headers()
            response = {
                "status": "ok",
                "service": "wk-ai-service",
                "version": "1.0.0-simple",
                "gemini_configured": False,
                "timestamp": datetime.now().isoformat()
            }
            self.wfile.write(json.dumps(response, ensure_ascii=False).encode())
        
        elif self.path == '/':
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.end_headers()
            response = {
                "message": "WK AI Service - Plataforma de Inteligência Artificial para CRM",
                "version": "1.0.0",
                "endpoints": {
                    "analyze": "POST /analyze - Análise de risco de oportunidade",
                    "chat": "POST /api/v1/chat - Chat com assistente de IA",
                    "health": "GET /health - Status do serviço"
                }
            }
            self.wfile.write(json.dumps(response, ensure_ascii=False).encode())
        else:
            self.send_response(404)
            self.end_headers()
    
    def do_POST(self):
        """Handle POST requests"""
        content_length = int(self.headers.get('Content-Length', 0))
        body = self.rfile.read(content_length).decode('utf-8')
        
        try:
            data = json.loads(body) if body else {}
        except:
            data = {}
        
        if self.path == '/analyze':
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            
            # Gerar análise fictícia baseada em dados
            title = data.get('title', 'Oportunidade')
            value = data.get('value', 50000)
            probability = data.get('probability', 50)
            
            # Cálculo simples de risco
            if probability >= 80:
                risk_score = 20
                risk_label = "baixo"
            elif probability >= 60:
                risk_score = 45
                risk_label = "médio"
            else:
                risk_score = 70
                risk_label = "alto"
            
            response = {
                "risk_score": risk_score,
                "risk_label": risk_label,
                "next_action": "Agendar reunião com o cliente",
                "recommendation": "Prepare proposta técnica detalhada",
                "summary": f"Análise de {title}: risco {risk_label}, probabilidade {probability}%",
                "model": "demo-mode",
                "cached": False
            }
            self.wfile.write(json.dumps(response, ensure_ascii=False).encode())
        
        elif self.path == '/api/v1/chat':
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            
            question = data.get('question', 'Olá')
            
            # Respostas simples em português
            responses = {
                'vend': 'Para aumentar vendas, foque em: 1) Qualificação de leads, 2) Acompanhamento proativo, 3) Personalizando propostas.',
                'risco': 'Sempre analise: valor, probabilidade, setor e tempo de fechamento. Use IA para prever riscos.',
                'converte': 'Taxa de conversão ideal é 20-30%. Melhore com follow-up consistente e propostas customizadas.',
                'default': 'Ótima pergunta! Na plataforma WK CRM, você pode analisar oportunidades, tendências e tomar decisões baseadas em dados.'
            }
            
            # Encontrar resposta apropriada
            answer = responses.get('default')
            question_lower = question.lower()
            for key in responses:
                if key in question_lower:
                    answer = responses[key]
                    break
            
            response = {
                "answer": answer,
                "model": "demo-mode",
                "source": "ai_service"
            }
            self.wfile.write(json.dumps(response, ensure_ascii=False).encode())
        
        elif self.path == '/ai/opportunity-insights' or self.path == '/analyze':
            # Legacy endpoint
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            
            response = {
                "risk_score": random.randint(20, 80),
                "risk_label": random.choice(["baixo", "médio", "alto"]),
                "next_action": "Agir rápido",
                "recommendation": "Ativar plano de ação",
                "summary": "Análise concluída",
                "model": "demo-mode",
                "cached": False
            }
            self.wfile.write(json.dumps(response, ensure_ascii=False).encode())
        
        else:
            self.send_response(404)
            self.end_headers()
    
    def do_OPTIONS(self):
        """Handle CORS preflight"""
        self.send_response(200)
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type')
        self.end_headers()
    
    def log_message(self, format, *args):
        """Suppress default logging"""
        pass


def run_server(port=8000):
    """Start the HTTP server"""
    server_address = ('0.0.0.0', port)
    httpd = HTTPServer(server_address, AIServiceHandler)
    print(f"✅ WK AI Service running on http://localhost:{port}")
    print(f"   - GET  /health")
    print(f"   - POST /analyze")
    print(f"   - POST /api/v1/chat")
    print(f"\nPress Ctrl+C to stop...")
    
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        print("\n\n✅ Server stopped")
        httpd.server_close()


if __name__ == '__main__':
    run_server(8000)
