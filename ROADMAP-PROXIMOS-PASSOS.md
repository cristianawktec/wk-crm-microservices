# 🚀 Roadmap - Próximos Passos WK CRM

## 📊 **Status Atual (18/10/2025)**

### ✅ **Concluído:**
- SSL funcionando (certificados válidos por 81 dias)
- Laravel API rodando com PostgreSQL VPS
- AdminLTE interface deployada
- Processo CI/CD implementado
- Scripts de monitoramento automatizados
- Documentação técnica completa

### 🔶 **Problemas Identificados:**
- API retorna 404 nas rotas (configuração de routing)
- AdminLTE com erro SSL específico do Windows
- Algumas configurações de produção pendentes

---

## 🎯 **PRIORIDADE 1: Correções Críticas (Esta Semana)**

### **1.1 Corrigir Roteamento da API Laravel**
**Problema:** API retorna 404 para `/api/health`
**Solução:**
```bash
# Verificar rotas na VPS
ssh root@72.60.254.100 "cd /opt/wk-crm/wk-crm-laravel && php artisan route:list"

# Verificar configuração Nginx para Laravel
# Ajustar root path e index files
```

**Arquivos a revisar:**
- `laravel_nginx.conf` - configuração Nginx
- `routes/api.php` - rotas da API
- `.htaccess` - regras de reescrita

### **1.2 Corrigir AdminLTE SSL no Windows**
**Problema:** Erro SSL handshake específico
**Soluções:**
1. Testar com diferentes clientes SSL
2. Verificar configuração SSL cipher suites
3. Atualizar certificados se necessário

### **1.3 Configurar Database Seeding**
**Objetivo:** Popular banco com dados de teste
```bash
# Na VPS
php artisan db:seed --class=CustomerSeeder
php artisan db:seed --class=LeadSeeder
```

---

## 🚀 **PRIORIDADE 2: Melhorias de Infraestrutura (Próximas 2 Semanas)**

### **2.1 GitHub Actions CI/CD**
**Objetivo:** Automatizar deploy no push para main
```yaml
# .github/workflows/deploy.yml
name: Deploy to VPS
on:
  push:
    branches: [main]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Deploy to VPS
        run: |
          ssh -o StrictHostKeyChecking=no root@72.60.254.100 'cd /opt/wk-crm && git pull && ./deploy.sh'
```

### **2.2 Docker Production Setup**
**Objetivo:** Containerizar para produção
```dockerfile
# Dockerfile.production
FROM php:8.2-fpm-alpine
# ... configuração otimizada para produção
```

### **2.3 Monitoramento Avançado**
**Implementar:**
- Uptime monitoring (Pingdom/UptimeRobot)
- Log aggregation (ELK Stack ou similar)
- Performance monitoring (New Relic/Datadog)
- SSL certificate expiry alerts

---

## 🎨 **PRIORIDADE 3: Desenvolvimento de Features (Próximo Mês)**

### **3.1 Dashboard Analytics**
**Implementar:**
- Gráficos de vendas (Chart.js)
- KPIs em tempo real
- Relatórios exportáveis
- Filtros por data/período

### **3.2 Sistema de Autenticação**
**Laravel Sanctum/Passport:**
```php
// API Authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);
});
```

### **3.3 API REST Completa**
**Endpoints a implementar:**
```
GET    /api/customers         # Listar clientes
POST   /api/customers         # Criar cliente
GET    /api/customers/{id}    # Detalhes cliente
PUT    /api/customers/{id}    # Atualizar cliente
DELETE /api/customers/{id}    # Deletar cliente

GET    /api/leads            # Listar leads
POST   /api/leads            # Criar lead
GET    /api/opportunities    # Listar oportunidades
POST   /api/opportunities    # Criar oportunidade
```

---

## 🔧 **PRIORIDADE 4: Otimizações (Longo Prazo)**

### **4.1 Performance**
- Redis caching
- Database indexing
- CDN para assets estáticos
- Image optimization

### **4.2 Segurança**
- Rate limiting
- Input validation
- SQL injection protection
- XSS protection
- CSRF tokens

### **4.3 Escalabilidade**
- Load balancer (Nginx/HAProxy)
- Database replication
- Horizontal scaling
- Microservices completos

---

## 🎯 **MINHA SUGESTÃO PARA O PRÓXIMO PASSO:**

## **✅ AÇÃO CONCLUÍDA: API Routing RESOLVIDO!**

### **🎉 SUCESSO COMPLETO!**
1. **SSL funcionando** ✅  
2. **Infrastructure pronta** ✅
3. **API funcionando 100%** ✅
4. **Problema resolvido rapidamente** ✅

### **✅ Plano Executado com Sucesso:**
1. **✅ Diagnosticado problema** - diretiva `root` no local incorreto
2. **✅ Corrigida configuração Nginx** - `root` movida para nível servidor
3. **✅ Testados endpoints da API** - todos funcionando
4. **✅ Deploy aplicado** - configuração ativa
5. **✅ Validado funcionamento** - API 200 OK

### **🌐 Resultado Alcançado:**
- ✅ `https://api.consultoriawk.com/api/health` → **200 OK**  
- ✅ `https://api.consultoriawk.com/api/dashboard` → **200 OK**
- ✅ **CORS headers** configurados e funcionando
- ✅ **SSL redirecionamento** automático ativo
- ✅ Base sólida para próximas features

---

## 🚀 **Quer começar agora?**

**Posso executar imediatamente:**
1. **Criar branch:** `feature/api-routing-fix`
2. **Diagnosticar** problema na VPS
3. **Implementar** correção
4. **Testar** localmente
5. **Deploy** via processo CI/CD estabelecido

**Comando para começar:**
```bash
git checkout -b feature/api-routing-fix
```

### **✅ TAREFA CONCLUÍDA COM SUCESSO!**
- ✅ ~~**Corrigir API routing**~~ **RESOLVIDO 19/10/2025**

---

## 🎯 **PRÓXIMOS PASSOS APÓS SUCESSO DA API:**

### **Opções para Continuar:**
- 📊 **Dashboard Data** (dados mais ricos) ← **RECOMENDADO**
- 🎨 **Melhorar AdminLTE** (interface visual)
- 🚀 **Implementar GitHub Actions** (automação)
- 🏗️ **Desenvolver nova feature** (expansão)

### **🌟 RECOMENDAÇÃO: Dashboard Data**
**Por que é a próxima prioridade:**
1. **API funcionando** = base sólida para dados ✅
2. **Alto impacto visual** para demonstrações
3. **Complementa infraestrutura** existente
4. **Prepara terreno** para frontend React

**Tempo estimado:** 2-3 horas  
**ROI:** 🟢 Alto - dados impressionantes para clientes
- 📊 **Analisar outro aspecto**

**Qual sua preferência?** 🎯

**Correções e Testes — 05/12/2025**

- **Resumo das correções aplicadas:**
  - Removido `add_header Access-Control-*` do host Nginx para evitar duplicação de CORS (agora o Laravel gerencia CORS via middleware).
  - Corrigida ordem de rotas em `routes/api.php` (ex.: `Route::get('leads/sources', ...)` movida antes de `Route::apiResource('leads', ...)`) para evitar captura do literal `sources` como `{lead}`.
  - Aplicada migration corretiva para garantir que `opportunities.value` exista quando necessário (migration idempotente criada).
  - Inserção temporária de seeds de teste (apenas para validação UI) — backup do DB criado antes de qualquer alteração.

- **Arquivos adicionados/alterados para verificação e testes:**
  - `wk-crm-laravel/tests/Feature/LeadsRoutesTest.php` — PHPUnit / Laravel Feature test que valida:
    - `GET /api/leads/sources` retorna 200
    - tabela `opportunities` contém a coluna `value`
  - `scripts/verify-fix.sh` — script rápido para checar endpoints (curl) e executar `artisan test` / `phpunit` quando disponível.

- **Como rodar as verificações (exemplos):**

  - Via SSH na VPS (bash):

    ```bash
    # entre no repositório
    cd /opt/wk-crm

    # executar script de verificação (faz curl nos endpoints e tenta rodar tests)
    bash scripts/verify-fix.sh
    ```

  - Executando apenas os testes Laravel (dentro do container ou host):

    ```bash
    # dentro do container app (exemplo: serviço "app")
    docker compose exec -T app php artisan test --filter=LeadsRoutesTest

    # ou localmente no diretório do app
    cd wk-crm-laravel
    php artisan test --filter=LeadsRoutesTest
    # ou
    ./vendor/bin/phpunit tests/Feature/LeadsRoutesTest.php
    ```

  - Comandos em PowerShell (se preferir executar localmente em devbox Windows):

    ```powershell
    cd C:\xampp\htdocs\crm
    bash .\scripts\verify-fix.sh
    # ou entrar no diretório do app e executar artisan/phpunit
    cd .\wk-crm-laravel
    php artisan test --filter=LeadsRoutesTest
    ```

- **Como reverter ou limpar seeds temporários (opções):**
  - Preferível: restaurar o dump de backup gerado antes das alterações.
    - Exemplo de restauração (FAÇA APENAS SE TIVER BACKUP E PERMISSÃO):

      ```bash
      # Exemplo - RESTAURAR (atenção: isso substitui os dados atuais)
      pg_restore --clean --no-owner --dbname=wk_main /opt/wk-crm/backups/wk_main_backup_YYYYmmdd_HHMMSS.dump
      ```

  - Se preferir apenas remover linhas de teste, ajuste a cláusula WHERE conforme os dados inseridos. Exemplo:

    ```sql
    -- conectar com psql e executar (modifique WHERE para corresponder aos registros de teste inseridos)
    DELETE FROM opportunities WHERE title ILIKE 'TEST %' OR created_at >= '2025-12-05';
    DELETE FROM leads WHERE email ILIKE 'dev-test@%';
    DELETE FROM sellers WHERE email ILIKE 'dev-test@%';
    ```

- **Observações e recomendações:**
  - Backups foram criados antes das migrações/seed.
  - O script `scripts/verify-fix.sh` é uma verificação rápida e não substitui uma suíte de integração completa em CI.
  - Recomendo adicionar um job de CI (GitHub Actions) que rode `php artisan test` após cada PR para evitar regressões de rota/middleware.
  - Se quiser, eu removo os seeds temporários agora ou executo o tail dos logs enquanto você replica fluxos no frontend — diga qual prefere.

**Arquivos adicionados nesta alteração:**
- `wk-crm-laravel/tests/Feature/LeadsRoutesTest.php`
- `scripts/verify-fix.sh`

---
