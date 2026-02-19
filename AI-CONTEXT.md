# Contexto do Projeto WK CRM

- Monorepo de microserviços
- VPS Ubuntu 24.04
- Deploy via SSH
- Pastas principais:
  - wk-gateway
  - wk-crm-laravel
  - wk-admin-frontend
  - wk-customer-app
- Versão atual: **v1.0.1** (19/02/2026)
- Status: **Stable - Database Safety Fix Applied**

## ✅ CRITICAL FIX: Database Safety (28/01/2025)
- ✅ DISABLED: Customer record creation in login endpoint
- ✅ DISABLED: Login audit recording (was modifying database)  
- ✅ DISABLED: test-customer endpoint (was overwriting credentials)
- Result: Login endpoint is now READ-ONLY for authentication
- See: DATABASE-SAFETY-FIX.md for complete details

## ✅ Última Sprint Concluída (v1.0.1)
- Fix permanente: Admin baseHref: / (sem hotfix manual VPS)
- Menu customer-app corrigido para subdomain admin
- CI/CD estendido para deploy multi-serviço
- Localhost SPA asset routing corrigido
- Workflow de branches implementado (feature/fix branches)

## 🎯 Objetivo Atual
- Prioridade 5: Testes Unitários, Paginação, Permissões, Auditoria
- Manter workflow de branch por task
- Deploy automático via GitHub Actions

## 📋 Próxima Task
Baseado em PROXIMOS-PASSOS-PRIORIDADES.md:
- Sprint 2: Testes Unitários (continuar feature/unit-tests)
- Sprint 3: Paginação (nova branch)
- Sprint 4: Permissões granulares (nova branch)
- Sprint 5: Auditoria estendida (nova branch)

## Cuidados a seguir
- 🔒 **CRITICAL**: NUNCA MODIFICAR BANCO EM ENDPOINTS DE AUTENTICAÇÃO/LOGIN
  - Sem firstOrCreate/updateOrCreate em customers table
  - Sem criação automática de usuários/clientes
  - Sem audit trail writes durante login
  - Login = validação de credenciais + geração de token (READ-ONLY)
- Cuidar ao fazer qualquer alteração para não quebrar a estabilidade atual
- Testar localmente antes de pushar
- Nunca e jamais deletar qualquer dados do banco de dados sem backup prévio
- Antes de excluir containers antigos, verificar logs e dados persistentes para nao perder informações importantes
- Manter comunicação clara no time sobre o status de cada task e possíveis bloqueios

# Depois, no chat novo, comece com: Leia o arquivo AI-CONTEXT.md e continue exatamente de onde paramos. Não reexplique contexto já documentado.

