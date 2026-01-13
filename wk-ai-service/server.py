#!/usr/bin/env python
"""WK AI Service - Versão ultra-simples (funciona!)"""

import json
from http.server import HTTPServer, BaseHTTPRequestHandler
import sys

class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        if self.path == '/health':
            self.send_response(200)
            self.send_header('Content-Type', 'application/json')
            self.end_headers()
            self.wfile.write(json.dumps({
                "status": "ok",
                "service": "wk-ai-service",
                "version": "1.0.0"
            }).encode())
        elif self.path == '/':
            self.send_response(200)
            self.send_header('Content-Type', 'application/json')
            self.end_headers()
            self.wfile.write(json.dumps({
                "message": "WK AI Service",
                "endpoints": ["/health", "/analyze", "/api/v1/chat"]
            }).encode())
        else:
            self.send_response(404)
            self.end_headers()
    
    def do_POST(self):
        try:
            content_len = int(self.headers.get('Content-Length', 0))
            body = self.rfile.read(content_len).decode()
            data = json.loads(body) if body else {}
        except:
            data = {}
        
        if self.path == '/analyze':
            self.send_response(200)
            self.send_header('Content-Type', 'application/json')
            self.end_headers()
            response = {
                "risk_score": 45,
                "risk_label": "médio",
                "next_action": "Agendar reunião",
                "recommendation": "Prepare proposta detalhada",
                "model": "demo"
            }
            self.wfile.write(json.dumps(response, ensure_ascii=False).encode())
        elif self.path == '/api/v1/chat':
            self.send_response(200)
            self.send_header('Content-Type', 'application/json')
            self.end_headers()
            response = {
                "answer": "Taxa de conversão ideal é 20-30%. Melhore com...",
                "model": "demo"
            }
            self.wfile.write(json.dumps(response, ensure_ascii=False).encode())
        else:
            self.send_response(404)
            self.end_headers()
    
    def log_message(self, format, *args):
        pass

if __name__ == '__main__':
    try:
        print("🚀 Iniciando WK AI Service na porta 8000...")
        server = HTTPServer(('0.0.0.0', 8000), Handler)
        print("✅ WK AI Service rodando em http://localhost:8000")
        print("   Endpoints:")
        print("   - GET  /health")
        print("   - GET  /")
        print("   - POST /analyze")
        print("   - POST /api/v1/chat")
        print("\nPressione Ctrl+C para parar...\n")
        server.serve_forever()
    except Exception as e:
        print(f"❌ Erro: {e}", file=sys.stderr)
        sys.exit(1)
    except KeyboardInterrupt:
        print("\n✅ Servidor parado")
        sys.exit(0)
