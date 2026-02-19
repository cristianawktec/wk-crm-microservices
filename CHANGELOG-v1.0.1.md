# Changelog - Versão 1.0.1

**Data:** 19 de fevereiro de 2026  
**Branch:** `fix/admin-basehref-and-menu`  
**Status:** Release Estável

---

## 🎯 Resumo da Versão 1.0.1

### Objetivo
Consolidar correções de deploy do admin e customer-app, garantindo sincronização permanente entre repositório e VPS, removendo hotfixes manuais.

---

## ✅ Correções Aplicadas

### 1. **Fix Permanente - Admin Base Href**
- **Problema:** Hotfix manual aplicado direto na VPS (`/var/www/html/admin/index.html`) alterando `base href` de `/admin/` para `/` não estava no repo
- **Solução:** 
  - Validado que `angular.json` já tinha `baseHref: "/"` configurado
  - Rebuild do admin Angular em produção (`npm run build:prod`)
  - Dist gerado com `<base href="/">` correto sem necessidade de alteração manual
- **Impacto:** Admin agora deployável via CI/CD sem necessidade de pós-processamento manual
- **Arquivos:** 
  - `wk-admin-frontend/dist/admin-frontend/index.html` (gerado)
  - `wk-admin-frontend/angular.json` (já configurado)

### 2. **Ajuste de Link - Menu Customer App → Admin Subdomain**
- **Problema:** Link do menu "Acessos ao Sistema" apontava para hash route (`https://admin.consultoriawk.com/#/admin/login-audits`)
- **Solução:** 
  - Alterado link em `AppLayout.vue` para rota direta: `https://admin.consultoriawk.com/admin/login-audits`
  - Rebuild do customer-app Vue (`npm run build`)
  - Dist sincronizado em `wk-crm-laravel/public/customer-app/`
- **Impacto:** Menu admin funcional com navegação correta para subdomain
- **Arquivos:**
  - `wk-customer-app/src/components/layout/AppLayout.vue`
  - `wk-customer-app/dist/` (novo build)
  - `wk-crm-laravel/public/customer-app/` (sincronizado)

### 3. **CI/CD Multi-Service Deploy** (do commit anterior)
- Workflow estendido para deploy condicional de Laravel, Gateway e Admin Frontend
- Detecção de mudanças por pasta com `dorny/paths-filter`
- Deploy isolado por serviço (`deploy-laravel`, `deploy-gateway`, `deploy-admin-frontend`)
- **Arquivo:** `.github/workflows/deploy-to-vps.yml`

### 4. **Localhost SPA Asset Routing** (do commit anterior)
- Fallback de assets `/assets/*` para `/customer-app/assets/*` apenas em localhost
- Correção de MIME types para módulos JS (`application/javascript`)
- Isolamento total de mudanças ao ambiente local (sem impacto VPS)
- **Arquivo:** `wk-crm-laravel/routes/web.php`

---

## 📋 Checklist de Validação

### ✅ Pré-Deploy
- [x] Admin build gerado com `base href="/"`
- [x] Customer-app menu aponta para `https://admin.consultoriawk.com/admin/login-audits`
- [x] Dist do customer-app sincronizado em Laravel public/
- [x] Localhost funcionando (tela branca corrigida)
- [x] Containers Docker ativos (wk_crm_laravel, wk_ai_service, postgres, redis)

### ⏳ Pós-Deploy VPS
- [ ] Admin acessível em `https://admin.consultoriawk.com/admin/login-audits`
- [ ] Link do menu customer-app redirecionando corretamente
- [ ] Usuario `admin@consultoriawk.com` com `role:admin` ativo
- [ ] Health check Laravel: `GET /api/health` retorna 200
- [ ] Sem hotfixes manuais residuais na VPS

---

## 🔄 Workflow de Branch Adotado

A partir desta versão, **todas as novas tasks seguem o modelo:**
1. Criar branch de feature a partir de `main`: `git checkout -b feature/nome-da-task`
2. Implementar, testar localmente
3. Commit com mensagem semântica (feat/fix/docs/refactor)
4. Push e criar Pull Request para `main`
5. Após merge, criar tag de versão se aplicável

**Branch atual:** `fix/admin-basehref-and-menu`

---

## 📊 Próximos Passos (Alinhado com PROXIMOS-PASSOS-PRIORIDADES.md)

### Prioridade 1, 2 e 3: ✅ Concluídas
- Reports & Analytics (Chart.js, KPIs)
- Sistema de Notificações (SSE, Email)
- Integrações de IA (Chatbot, Trends)

### Prioridade 4: ✅ Admin Simple (AdminLTE) - Concluído
- Modal de edição funcional
- Validação + toasts animados

### Prioridade 5: ⏳ Pendente
1. **Testes Unitários** (em andamento - branch `feature/unit-tests`)
   - 57+ testes criados (Laravel 22, Vue 35+)
   - Falta: Angular tests, CI/CD integration
2. **Paginação** (TODO)
3. **Permissões Granulares** (TODO)
4. **Auditoria Estendida** (TODO)

---

## 🏷️ Versionamento

**Versão anterior:** 1.0.0 (implícita)  
**Versão atual:** 1.0.1  
**Próxima versão:** 1.0.2 (após conclusão de Sprint 2 - Testes)

### Convenção Adotada
- **MAJOR.MINOR.PATCH** (Semantic Versioning)
- Patch (+1): Correções de bugs, hotfixes
- Minor (+1): Novas features sem breaking changes
- Major (+1): Breaking changes, refatorações grandes

---

## 👥 Autoria
**Desenvolvedor:** Cristian MS + GitHub Copilot  
**Revisão:** N/A (deploy individual)  
**Aprovação:** Auto-aprovado (projeto individual)

---

## 📝 Notas Técnicas

### Dependências
- Angular 18 (Admin Frontend)
- Vue 3 (Customer App)
- Laravel 11 (API Backend)
- Docker Compose (Infraestrutura)

### Ambientes
- **Local:** `localhost:8000` (Laravel), `localhost:8001` (AI Service)
- **VPS:** `api.consultoriawk.com`, `app.consultoriawk.com`, `admin.consultoriawk.com`

### Observações
- Hotfix de `base href` removido da VPS após este deploy
- Role `admin` na VPS validada para `admin@consultoriawk.com`
- Sistema estável e pronto para próximas sprints

---

**Fim do Changelog v1.0.1**
