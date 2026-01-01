# 🚀 Deploy Completo - Fix Auth + Database

## Problemas Encontrados e Corrigidos

### 1. ❌ Login Endpoint (500 Error)
- **Erro:** MassAssignmentException ao tentar criar User com 'role'
- **Fix:** Commit `71b08cd` - Usar `assignRole()` do Spatie Permission

### 2. ❌ PostgreSQL Authentication Failed
- **Erro:** `password authentication failed for user 'wk_user'`
- **Causa:** Senha diferente entre docker-compose (`secure_password`) e Laravel `.env` (`secure_password_123`)
- **Fix:** Commit `64e47aa` - Sincronizar senha como `secure_password_123` em ambos

---

## 📋 Deployment no VPS (COMPLETO)

### SSH para VPS
```bash
ssh root@72.60.254.100
```

### Passo 1: Pull do Código Atualizado
```bash
cd /root/wk-crm-microservices
git pull origin main
```

### Passo 2: Parar Containers
```bash
docker compose down
```

### Passo 3: **IMPORTANTE** - Remover Volume do PostgreSQL
```bash
# Remover o volume antigo (com senha errada)
docker volume rm wk-crm-microservices_postgres_data

# Ou force remove se necessário:
docker compose down -v
```

### Passo 4: Rebuild Containers
```bash
docker compose build --no-cache
```

### Passo 5: Subir Containers
```bash
docker compose up -d
```

### Passo 6: Aguardar Containers Iniciarem
```bash
sleep 10
docker ps
```

### Passo 7: Rodar Migrations
```bash
docker exec wk_crm_laravel php artisan migrate --force
```

### Passo 8: Limpar Cache do Laravel
```bash
docker exec wk_crm_laravel php artisan config:cache
docker exec wk_crm_laravel php artisan route:clear
docker exec wk_crm_laravel php artisan view:clear
```

---

## ✅ Verificação

### 1. Testar Conexão ao Banco
```bash
docker exec wk_crm_laravel php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database Connected!';"
```

### 2. Testar Endpoint de Login
```bash
curl "https://api.consultoriawk.com/api/auth/test-customer?role=admin"
```

**Resposta Esperada (200 OK):**
```json
{
  "success": true,
  "user": {
    "id": "...",
    "name": "Admin WK",
    "email": "admin-test@wkcrm.local"
  },
  "token": "..."
}
```

### 3. Testar Frontend
- Abrir: `https://app.consultoriawk.com/login`
- Clicar em **"Entrar como ADMIN"** → deve funcionar
- Clicar em **"Entrar como CLIENTE"** → deve funcionar

---

## 🔧 Troubleshooting

### Se Ainda Aparecer Erro de Senha
```bash
# Verificar se o volume foi realmente removido
docker volume ls | grep postgres

# Se aparecer, remover manualmente:
docker volume rm <nome_do_volume>

# Depois refazer Passo 4 e 5
```

### Se Migrations Falharem
```bash
# Verificar se banco está acessível
docker exec wk_crm_laravel php artisan db:show

# Recriar banco se necessário
docker exec -it wk_postgres psql -U wk_user -d wk_main
# Se conectar, o banco está OK
```

### Se Login Ainda Retornar 500
```bash
# Ver logs do Laravel
docker logs wk_crm_laravel --tail 50

# Ver logs do Postgres
docker logs wk_postgres --tail 50
```

---

## 📊 Commits Deste Deploy

```
64e47aa - fix: Sync PostgreSQL password across docker-compose and Laravel .env
b28cfe9 - docs: Add session summary for authentication fix work
39300b6 - docs: Add deployment checklist for VPS verification
558371d - docs: Add comprehensive fix status report
c2a035d - docs: Add deployment and quick fix guides for auth endpoint fix
71b08cd - Fix: Correcting test-customer endpoint to use proper role assignment
```

---

## ⚠️ IMPORTANTE

**Ao remover o volume do PostgreSQL (`docker compose down -v`), TODOS OS DADOS DO BANCO SERÃO PERDIDOS.**

Se você tem dados importantes:
1. Faça backup primeiro: `docker exec wk_postgres pg_dump -U wk_user wk_main > backup.sql`
2. Depois do deploy, restaure: `docker exec -i wk_postgres psql -U wk_user wk_main < backup.sql`

---

**Status:** ✅ Pronto para deploy  
**Tempo estimado:** 5-10 minutos  
**Data:** 01/01/2026
