# 🐳 WK CRM Brasil - Guia de Configuração Docker

## ⚠️ Problemas Identificados

### 1. Docker Desktop não está rodando
**Erro:** `O sistema não pode encontrar o arquivo especificado`

**Solução:**
1. Abra o **Docker Desktop** no Windows
2. Aguarde até aparecer "Docker Desktop is running" no canto inferior esquerdo
3. Teste com: `docker --version`

### 2. Versão do docker-compose.yml obsoleta
**Aviso:** `the attribute 'version' is obsolete`

**Solução:** Remover a linha `version: '3.8'` do docker-compose.yml

---

## 🚀 Passos para Inicializar Corretamente

### 1. Iniciar Docker Desktop
```powershell
# No Windows, procure por "Docker Desktop" e execute
# Ou via PowerShell (se instalado via winget/chocolatey):
Start-Process "Docker Desktop"
```

### 2. Verificar Status
```powershell
# Aguarde até o Docker estar pronto
docker --version
docker compose version
```

### 3. Inicializar Microservices
```powershell
# No diretório C:\xampp\htdocs\crm\
.\start-quick.bat
```

---

## 🌐 URLs Após Inicialização

| Serviço | URL Local | Descrição |
|---------|-----------|-----------|
| **Laravel API** | http://localhost:8000 | API principal DDD |
| **Gateway** | http://localhost:3000 | API Gateway Node.js |
| **Admin Panel** | http://localhost:4200 | Frontend Angular |
| **Customer App** | http://localhost:3002 | App Vue.js |
| **Health Check** | http://localhost:8000/api/health | Status da API |
| **Dashboard** | http://localhost:8000/api/dashboard | Métricas |

---

## 🔧 Comandos Úteis

```powershell
# Parar todos containers
docker compose down

# Ver containers rodando
docker compose ps

# Ver logs em tempo real
docker compose logs -f

# Reconstruir containers
docker compose build --no-cache

# Iniciar serviços específicos
docker compose up -d postgres redis wk-crm-laravel
```

---

## 📊 Arquitetura Atual

```
🌐 Frontend (Angular Admin + Vue.js Customer)
        ↓
🚪 API Gateway (Node.js) - :3000
        ↓
📱 Microservices:
   ├── 🔴 Laravel API (DDD) - :8000
   ├── 🔵 .NET Core API - :5000  
   ├── 🟢 Products API (Node.js) - :3001
   └── 🟡 AI Service (Python) - :8080
        ↓
💾 Dados:
   ├── 🐘 PostgreSQL - :5432
   └── 🔺 Redis - :6379
```

---

## 🇧🇷 Status Atual - Totalmente em Português

✅ **Funcionando:**
- API Laravel com DDD + SOLID + TDD
- Dados brasileiros (CPF, CEP, Real)
- Endpoints em português
- Admin panel AdminLTE

⭐ **Próximos Passos:**
1. Iniciar Docker Desktop
2. Executar containers
3. Testar todos os microservices
4. Conectar frontends às APIs