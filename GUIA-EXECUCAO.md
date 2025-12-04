# Guia de Execução - WK CRM Microservices

## ✅ Status Atual
- **Localhost**: Funcionando (Angular + Laravel)
- **VPS Produção**: Funcionando (https://admin.consultoriawk.com)
- **Deploy Automatizado**: Configurado e testado

---

## 🖥️ LOCALHOST - Como Rodar

### 1. Backend Laravel (API)
```powershell
cd C:\xampp\htdocs\crm\wk-crm-laravel
php artisan serve --port=8000
```

## Guia de Execução Local (Laravel sem Docker)

### 1. Instale as dependências

No terminal, acesse a pasta do projeto Laravel:

```sh
cd wk-crm-laravel
composer install
```

### 2. Gere o arquivo `.env`

Se não existir, copie o exemplo:

```sh
copy .env.example .env
```

### 3. Configure o banco PostgreSQL

No arquivo `.env`, altere as linhas de banco para:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=wk_main
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

Crie o banco e o usuário no PostgreSQL local, se necessário:

```sql
CREATE DATABASE wk_main;
CREATE USER seu_usuario WITH PASSWORD 'sua_senha';
GRANT ALL PRIVILEGES ON DATABASE wk_main TO seu_usuario;
```

## Comandos via terminal no banco do postgres

docker exec -it wk_postgres psql -U postgres -c "\dt"

docker exec wk_postgres psql -U wk_user -d wk_main -c "SELECT column_name, data_type, is_nullable, column_default FROM information_schema.columns WHERE table_name='opportunities' ORDER BY ordinal_position;"


### 4. Gere a chave do app

```sh
php artisan key:generate
```

### 5. Rode as migrations

```sh
php artisan migrate
```

### 6. Inicie o servidor local

```sh
php artisan serve
```

O sistema estará disponível em http://localhost:8000

---
Se precisar rodar sem banco, basta pular as etapas de banco/migration e ajustar os controllers para não depender de dados persistidos.
### 2. Frontend Angular (Dashboard)
```powershell
cd C:\xampp\htdocs\crm\wk-admin-frontend
npm start
npm start -- --port=4300
```

### 3. Banco de Dados Local
- PostgreSQL via XAMPP ou Docker
- Credenciais em `.env` do Laravel

### 4. Estrutura Localhost
```
Laravel API (8080) ← Frontend Angular (4200)
     ↓
PostgreSQL Local
```

---

## 🌐 VPS PRODUÇÃO - Arquitetura Atual

### Serviços Rodando
```
Nginx HOST (80/443)
    ↓ (proxy reverso)
    ├─→ api.consultoriawk.com → Docker Container (Laravel:8000)
    │                                    ↓
    │                               PostgreSQL Container
    │                                    ↓
    │                               Redis Container
    │
    └─→ admin.consultoriawk.com → /var/www/html/admin (Angular build estático)
```

### Como Rodar/Gerenciar na VPS

#### 1. Iniciar Sistema (via SSH)
```bash
# Conectar na VPS
ssh root@72.60.254.100

# Ir para o diretório do projeto
cd /opt/wk-crm

# Iniciar todos os containers
docker compose up -d

# Verificar se subiram
docker compose ps
```

#### 2. Parar Sistema
```bash
cd /opt/wk-crm

# Parar todos os containers
docker compose down

# Parar apenas Laravel (mantém DB/Redis)
docker compose stop wk-crm-laravel
```

#### 3. Reiniciar Sistema
```bash
cd /opt/wk-crm

# Reiniciar todos
docker compose restart

# Reiniciar apenas Laravel
docker compose restart wk-crm-laravel

# Reiniciar Laravel + PostgreSQL
docker compose restart wk-crm-laravel postgres
```

#### 4. Ver Logs
```bash
# Logs em tempo real (Laravel)
docker compose logs -f wk-crm-laravel

# Logs PostgreSQL
docker compose logs -f postgres

# Logs de todos os containers
docker compose logs -f

# Últimas 50 linhas
docker compose logs --tail=50 wk-crm-laravel
```

#### 5. Status e Diagnóstico
```bash
# Ver status dos containers
docker compose ps

# Ver recursos (CPU/Memória)
docker stats

# Executar comandos dentro do container Laravel
docker compose exec wk-crm-laravel php artisan --version
docker compose exec wk-crm-laravel php artisan route:list
```

### Comandos Úteis VPS
```bash
# Limpar cache Laravel
docker compose exec wk-crm-laravel php artisan optimize:clear

# Migrations
docker compose exec wk-crm-laravel php artisan migrate

# Verificar conexão DB
docker compose exec wk-crm-laravel php artisan tinker
# >>> DB::connection()->getPdo();

# Reload Nginx (após mudanças de configuração)
nginx -t && systemctl reload nginx

# Verificar se Nginx está rodando
systemctl status nginx

# Restart Nginx
systemctl restart nginx
```

### Nginx Configuração
- **API**: `/etc/nginx/sites-available/api.consultoriawk.com`
  - Proxy para: `http://localhost:8000` (container Laravel)
- **Admin**: `/etc/nginx/sites-available/admin.consultoriawk.com`
  - Root: `/var/www/html/admin` (build Angular estático)

### URLs Produção
- API Backend: https://api.consultoriawk.com/api/dashboard
- Frontend Admin: https://admin.consultoriawk.com

---

## 🚀 DEPLOY - Como Atualizar VPS

### Deploy Automático (Recomendado)
```powershell
# Na sua máquina local (Windows)
cd C:\xampp\htdocs\crm
.\deploy-angular-vps.ps1
```

**O que o script faz:**
1. Build de produção local (`npm run build:prod`)
2. Backup automático no VPS
3. Upload via SCP (limpa arquivos antigos)
4. Reload do Nginx
5. Validação HTTP 200

### Deploy Manual (Alternativa)
```powershell
# 1. Build local
cd wk-admin-frontend
npm run build:prod

# 2. Upload
scp -r dist/admin-frontend/browser/* root@72.60.254.100:/var/www/html/admin/

# 3. Reload Nginx (via SSH)
ssh root@72.60.254.100 "nginx -t && systemctl reload nginx"
```

---

## 📁 Estrutura de Pastas VPS

```
/opt/wk-crm/
├── wk-crm-laravel/          # Código Laravel + Dockerfile
│   ├── app/
│   ├── public/
│   ├── storage/logs/
│   └── docker-compose.yml
│
├── wk-admin-frontend/       # Código-fonte Angular (opcional na VPS)
│   ├── src/
│   ├── angular.json
│   └── package.json
│
└── scripts/                 # Scripts de manutenção
    ├── diagnose-container.sh
    ├── fix-nginx-proxy.sh
    └── verify-angular-deploy.sh

/var/www/html/admin/         # Build Angular SERVIDO pelo Nginx
├── index.html
├── main-*.js
├── styles-*.css
└── polyfills-*.js
```

---

## 🔧 Configurações Importantes

### Environment Angular
**Produção** (`environment.prod.ts`):
```typescript
export const environment = {
  production: true,
  apiUrl: 'https://api.consultoriawk.com/api'
};
```

**Desenvolvimento** (`environment.ts`):
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8080/api'
};
```

### Output Path Angular
**`angular.json`** (já configurado):
```json
{
  "outputPath": "dist/admin-frontend"
}
```

### Docker Compose (VPS)
```yaml
services:
  wk-crm-laravel:
    ports:
      - "8000:8000"
    environment:
      DB_HOST: postgres
      DB_DATABASE: wk_main
```

---

## 🛠️ Comandos Úteis

### Localhost
```powershell
# Parar servidor Laravel
taskkill /F /IM php.exe

# Parar Angular
# Ctrl+C no terminal ou:
Get-Process node | Where-Object {(Get-NetTCPConnection -OwningProcess $_.Id -ErrorAction SilentlyContinue).LocalPort -eq 4200} | Stop-Process -Force

# Limpar cache Laravel
cd wk-crm-laravel
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### VPS (via SSH)
```bash
# Ver logs Laravel
docker compose logs -f wk-crm-laravel

# Limpar cache Laravel
docker compose exec wk-crm-laravel php artisan optimize:clear

# Restart containers
docker compose restart wk-crm-laravel postgres

# Verificar Nginx
nginx -t
systemctl reload nginx

# Verificar processos
docker compose ps
```

---

## ⚠️ IMPORTANTE - Não Sobrepor

### ✅ O que está funcionando (NÃO MEXER):
1. **Localhost**:
   - Laravel serve na porta 8080
   - Angular dev server na porta 4200
   - Conexão via environment.ts

2. **VPS Produção**:
   - Laravel em Docker container (porta 8000)
   - Nginx proxy para api.consultoriawk.com
   - Angular build estático em /var/www/html/admin
   - Nginx serve admin.consultoriawk.com

3. **Deploy**:
   - Script PowerShell `deploy-angular-vps.ps1`
   - Build local → Upload → Nginx reload
   - Output path: `dist/admin-frontend`

### ❌ O que NÃO fazer:
- ❌ Não mudar `outputPath` do `angular.json` (já é `dist/admin-frontend`)
- ❌ Não trocar proxy do Nginx API (já aponta para localhost:8000)
- ❌ Não rodar `npm start` na VPS (usa build estático)
- ❌ Não mexer no `docker-compose.yml` sem testar
- ❌ Não alterar environment.prod.ts (já aponta para api.consultoriawk.com)

---

## 🔄 Fluxo de Desenvolvimento

1. **Desenvolver no Localhost**:
   ```powershell
   # Terminal 1: Laravel
   cd wk-crm-laravel
   php artisan serve --port=8080
   
   # Terminal 2: Angular
   cd wk-admin-frontend
   npm start
   ```

2. **Testar Localmente**:
   - Abrir: http://localhost:4200
   - Verificar console do navegador
   - Testar API: http://localhost:8080/api/dashboard

3. **Deploy para VPS**:
   ```powershell
   cd C:\xampp\htdocs\crm
   .\deploy-angular-vps.ps1
   ```

4. **Validar Produção**:
   - Abrir: https://admin.consultoriawk.com
   - Forçar refresh: Ctrl+F5
   - Verificar API: https://api.consultoriawk.com/api/dashboard

---

## 📝 Troubleshooting

### Localhost não conecta na API
```powershell
# Verificar se Laravel está rodando
curl http://localhost:8080/api/dashboard

# Ver logs Laravel
cd wk-crm-laravel
tail -f storage/logs/laravel.log
```

### VPS retorna 500
```bash
# SSH na VPS
ssh root@72.60.254.100

# Ver logs do container
docker compose logs --tail=50 wk-crm-laravel

# Verificar conexão DB
docker compose exec wk-crm-laravel php artisan tinker
# >>> DB::connection()->getPdo();
```

### Deploy falha
```powershell
# Verificar build local
cd wk-admin-frontend
npm run build:prod
ls dist/admin-frontend/browser

# Testar SSH
ssh root@72.60.254.100 "echo OK"
```

### Cache do navegador
```
Ctrl+F5 (Windows/Linux)
Cmd+Shift+R (Mac)

Ou abrir aba anônima
```

---

## 📊 Endpoints Ativos

### API (Backend Laravel)
- **Dashboard**: `GET /api/dashboard`
  - Localhost: http://localhost:8080/api/dashboard
  - Produção: https://api.consultoriawk.com/api/dashboard

- **Clientes (Customers)**: `GET /api/clientes`
  - Campos retornados: `id`, `name`, `email`, `phone`, `created_at`, `updated_at`
  - Operações: GET (list/show), POST (create), PUT (update), DELETE

- **Leads**: `GET /api/leads`
  - Campos retornados: `id`, `name`, `email`, `phone`, `company`, `source`, `status`, `created_at`, `updated_at`
  - Operações: GET (list/show), POST (create), PUT (update), DELETE

- **Oportunidades (Opportunities)**: `GET /api/oportunidades`
  - Campos retornados: `id`, `title`, `value`, `status`, `customer_id`, `created_at`, `updated_at`
  - Operações: GET (list/show), POST (create), PUT (update), DELETE

**⚠️ Importante - Padronização de Campos:**
- A API agora retorna **apenas campos em inglês** (conforme OpenAPI spec)
- Frontend normaliza automaticamente campos em português (legacy) para inglês
- Ao enviar dados, prefira campos em inglês: `name` (não `nome`), `title` (não `titulo`), `value` (não `valor`)
- Backend aceita ambos formatos na entrada por compatibilidade

### Frontend (Angular)
- **Admin Dashboard**: `/dashboard`
  - Localhost: http://localhost:4200/dashboard
  - Produção: https://admin.consultoriawk.com/dashboard

- **Clientes**: `/clientes` - CRUD completo com validações
- **Leads**: `/leads` - CRUD completo com validações  
- **Oportunidades**: `/oportunidades` - CRUD completo com validações

---

## 🎯 Checklist de Validação

### Localhost ✅
- [ ] Laravel responde em http://localhost:8080
- [ ] Angular abre em http://localhost:4200
- [ ] Dashboard carrega dados da API local
- [ ] Gráficos renderizam corretamente

### VPS ✅
- [ ] API responde em https://api.consultoriawk.com/api/dashboard
- [ ] Admin abre em https://admin.consultoriawk.com
- [ ] Dashboard carrega dados da API produção
- [ ] Sem erros no console do navegador
- [ ] Containers Docker rodando: `docker compose ps`

---

## 📞 Suporte

Se algo não funcionar:
1. Verificar logs do Laravel (local ou VPS)
2. Verificar console do navegador (F12)
3. Testar API diretamente (curl ou Postman)
4. Verificar configuração Nginx (VPS)
5. Validar que containers estão rodando (VPS)

---

**Última atualização**: 23/11/2025  
**Status**: ✅ Funcionando em Localhost e VPS
