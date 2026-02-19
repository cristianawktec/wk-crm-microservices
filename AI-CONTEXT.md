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
- Status: **Estável - Pronto para produção**

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

# Depois, no chat novo, comece com: Leia o arquivo AI-CONTEXT.md e continue exatamente de onde paramos. Não reexplique contexto já documentado.