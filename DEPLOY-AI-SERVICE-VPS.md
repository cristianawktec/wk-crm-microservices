# 🚀 Deploy da AI Service na VPS

**Data:** 13/01/2026  
**Status:** Pronto para Deploy ✅  
**Repositório:** Push completo ✅

---

## 📋 Pré-requisitos

- ✅ Código commitado e pushado para main
- ✅ Serviço testado localmente (todos testes passando)
- ✅ SSH acesso à VPS (72.60.254.100)
- ✅ Python 3.6+ instalado na VPS

---

## 🔧 Passos de Deployment

### 1. SSH na VPS

```bash
ssh root@72.60.254.100
```

### 2. Entrar no diretório do projeto

```bash
cd /var/www/wk-crm-api
```

### 3. Fazer pull do repositório

```bash
git pull origin main
```

### 4. Parar serviço anterior (se estiver rodando)

```bash
pkill -f "python.*server.py"
sleep 2
```

### 5. Iniciar o AI Service

```bash
cd wk-ai-service

# Opção 1: Com nohup (background permanente)
nohup python server.py > /var/log/wk-ai-service/service.log 2>&1 &

# Opção 2: Com screen (para ter controle)
screen -S ai-service -d -m python server.py
```

### 6. Verificar se está rodando

```bash
# Verificar porta 8000
netstat -tlnp | grep 8000

# Ou verificar processo
ps aux | grep server.py

# Ou ver o log
tail -f /var/log/wk-ai-service/service.log
```

### 7. Configurar Nginx (Reverse Proxy)

Editar `/etc/nginx/sites-available/api.consultoriawk.com`:

```bash
sudo nano /etc/nginx/sites-available/api.consultoriawk.com
```

Adicionar este bloco dentro do `server {}`:

```nginx
# AI Service Reverse Proxy
location /ai/ {
    proxy_pass http://localhost:8000/;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_cache_bypass $http_upgrade;
}

# AI Service Health Check
location /ai/health {
    access_log off;
    proxy_pass http://localhost:8000/health;
    proxy_http_version 1.1;
}
```

### 8. Testar configuração Nginx

```bash
sudo nginx -t
```

### 9. Recarregar Nginx

```bash
sudo systemctl reload nginx
```

---

## ✅ Verificação Pós-Deploy

### Local (VPS)

```bash
# Health check direto
curl http://localhost:8000/health

# API root
curl http://localhost:8000/

# Analyze endpoint
curl -X POST http://localhost:8000/analyze \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","value":100000,"probability":50}'
```

### Remoto (da sua máquina)

```bash
# Via reverse proxy
curl https://api.consultoriawk.com/ai/health

# Via porta direta (se liberada no firewall)
curl http://72.60.254.100:8000/health
```

---

## 🔍 Monitoramento

### Ver logs em tempo real

```bash
tail -f /var/log/wk-ai-service/service.log
```

### Verificar processo

```bash
ps aux | grep "server.py"
ps aux | grep "python"
```

### Verificar porta

```bash
netstat -tlnp | grep 8000
lsof -i :8000
```

### Restart do serviço

```bash
pkill -f "python.*server.py"
sleep 2
nohup python server.py > /var/log/wk-ai-service/service.log 2>&1 &
```

---

## 📊 URLs Disponíveis Após Deploy

| Endpoint | URL |
|----------|-----|
| Health Check | `https://api.consultoriawk.com/ai/health` |
| API Root | `https://api.consultoriawk.com/ai/` |
| Analyze | `https://api.consultoriawk.com/ai/analyze` (POST) |
| Chat | `https://api.consultoriawk.com/ai/api/v1/chat` (POST) |

---

## 🐛 Troubleshooting

### Porta 8000 não está acessível

```bash
# 1. Verificar se processo está rodando
ps aux | grep server.py

# 2. Verificar logs
tail -50 /var/log/wk-ai-service/service.log

# 3. Reiniciar
pkill -f "python.*server.py"
sleep 2
cd /var/www/wk-crm-api/wk-ai-service
nohup python server.py > /var/log/wk-ai-service/service.log 2>&1 &
```

### Nginx não consegue conectar

```bash
# 1. Verificar se proxy está conectando
curl http://127.0.0.1:8000/health

# 2. Verificar firewall
sudo ufw status
sudo ufw allow 8000

# 3. Verificar logs Nginx
sudo tail -50 /var/log/nginx/error.log
```

### Python não encontrado

```bash
# Verificar versão disponível
python --version
python3 --version
which python3

# Se usar python3, atualizar script
sed -i 's/python/python3/g' server.py
```

---

## 📝 Próximas Fases

✅ **Phase 1:** Backend AI Service (COMPLETO - DEPLOYED)

⏳ **Phase 2:** Integração Laravel (2-3h)
- Criar AiController.php
- Endpoint POST /api/opportunities/{id}/ai-analysis
- Chamar AI Service via Guzzle

⏳ **Phase 3:** Vue Frontend (3-4h)
- Card de análise de risco
- Visual com gauge
- Botão de análise

⏳ **Phase 4:** Chatbot Widget (4-5h)
- Componente flutuante
- Chat em tempo real
- Deploy

---

## 🎯 Checklist de Deployment

- [ ] Código commitado em português
- [ ] Push para repositório concluído
- [ ] SSH conectado à VPS
- [ ] Git pull executado
- [ ] Python verificado (versão 3.6+)
- [ ] Serviço iniciado na porta 8000
- [ ] Health check respondendo (localhost:8000/health)
- [ ] Nginx configurado (reverse proxy /ai/)
- [ ] Nginx recarregado
- [ ] Health check remoto (api.consultoriawk.com/ai/health)
- [ ] Logs verificados
- [ ] Pronto para Phase 2

---

**Deploy Date:** 13/01/2026  
**Status:** Ready to Deploy ✅  
**Next Step:** Execute deploy-ai-service.sh ou siga passos manualmente
