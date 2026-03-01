# 🎯 Próximos Passos - Prioridades de Desenvolvimento

**Data:** 01 de março de 2026  
**Última atualização:** 01 de março de 2026  
**Status Geral:** Plataforma estável em produção local/VPS, com foco em hardening, governança e fechamento das pendências de produto.

---

## ✅ Resumo Geral do que já foi feito

### 1) Base da plataforma (concluída)
- Arquitetura de microserviços consolidada (Laravel, Vue, Angular, Node, Python AI).
- Fluxo de deploy e operação em VPS estabelecido.
- CI/CD multi-serviço já estruturado.
- Correções de roteamento/baseHref e estabilidade dos frontends aplicadas.

### 2) Funcionalidades principais (concluídas)
- Dashboard, CRUD de oportunidades e autenticação funcionando.
- Notificações em tempo real (SSE) implementadas.
- Integrações de IA e análise de tendências implementadas.
- Admin Simple e suíte inicial de testes já desenvolvidos.

### 3) Estabilização recente de login/dados (concluída)
- Correções críticas no fluxo de login para ambiente localhost.
- Ajustes para não quebrar exibição de dados por usuário.
- Envio de auditoria de login por email validado novamente.

### 4) Higienização do repositório público (concluída em `main`)
- Remoção do versionamento de artefatos operacionais/sensíveis:
  - `.md` internos de incidente/diagnóstico (mantendo docs essenciais)
  - `.sh`, `.txt`, `.zip`, `.ps1`, `.bat`, `.gz`
- Regras de bloqueio adicionadas no `.gitignore` para evitar republicação.

---

## 🚧 Em andamento agora

### Feature de avatar de perfil (`feature/profile-image`)
- Backend com endpoint de upload de avatar preparado.
- Frontend com botão “Trocar imagem” já visível e funcional localmente.
- Branch ainda contém mudanças não finalizadas para merge limpo.

---

## 📌 Próximos passos priorizados (ordem real)

## PRIORIDADE A — Fechar e mergear Avatar de Perfil
**Objetivo:** entregar feature completa, estável e sem resíduos de build no commit.

### Tasks
1. Revisar mudanças pendentes na branch `feature/profile-image`.
2. Garantir payload/retorno de `avatar` consistente entre backend e frontend.
3. Validar upload (JPG/PNG, limite de tamanho, tratamento de erro).
4. Garantir `storage:link` e exibição pública da imagem.
5. Commitar apenas código-fonte necessário (sem artefatos desnecessários).
6. Abrir PR e mergear para `main`.

**Status:** 🔄 Em andamento  
**Estimativa:** 2-4h

---

## PRIORIDADE B — Qualidade e Governança (curto prazo)
**Objetivo:** reduzir risco operacional e melhorar previsibilidade de entrega.

### Tasks
1. Consolidar checklist de PR (segurança + dados + deploy).
2. Reforçar regra de “sem alteração destrutiva de banco em login/auth”.
3. Padronizar convenção de commits e branches para features/fixes.
4. Revisar arquivos ainda públicos que podem expor contexto operacional (ex.: `.sql`, `.html` de debug), se necessário.

**Status:** ⏳ Pendente  
**Estimativa:** 2-3h

---

## PRIORIDADE C — Roadmap funcional pendente

### C1) Paginação global
- Backend com paginação padronizada.
- Frontends (Vue/Angular) com componente reutilizável.
- Busca + paginação + filtros sem regressão.

**Status:** ⏳ Pendente  
**Estimativa:** 3-4h

### C2) Permissões granulares
- Revisão de roles/permissions.
- Proteção de rotas backend + frontend.
- Matriz de acesso por perfil (admin, vendedor, cliente).

**Status:** ⏳ Pendente  
**Estimativa:** 4-6h

### C3) Auditoria estendida
- Registrar ações críticas (não só login).
- Filtros por usuário/ação/data.
- Visão administrativa de auditoria.

**Status:** ⏳ Pendente  
**Estimativa:** 4-6h

### C4) Expansão de testes
- Completar cobertura nos fluxos mais críticos.
- Integrar testes no pipeline para bloqueio de regressão.

**Status:** ⏳ Pendente  
**Estimativa:** 4-6h

---

## 🧭 Plano de execução sugerido (próximas iterações)

### Sprint 1 (imediata)
- Fechar feature avatar.
- PR + merge em `main`.
- Sanity check pós-merge.

### Sprint 2
- Paginação global.
- Ajustes de UX e performance nas listas.

### Sprint 3
- Permissões granulares (backend/frontend).

### Sprint 4
- Auditoria estendida + incremento de testes.

---

## 🔐 Regras obrigatórias de segurança operacional

1. Não executar mudanças destrutivas de banco sem backup validado.
2. Não remover containers/volumes sem confirmação explícita.
3. Login/auth não deve alterar dados de negócio indevidamente.
4. Artefatos operacionais não devem ser versionados no GitHub público.

---

## ✅ Critério de “pronto” para o próximo release

- Feature avatar mergeada e validada.
- Paginação entregue em todos os módulos críticos.
- Permissões mínimas funcionando por perfil.
- Auditoria estendida habilitada para ações-chave.
- Pipeline com testes essenciais no CI.

---

## 🔄 Workflow recomendado por task

1. Criar branch específica (`feature/...` ou `fix/...`).
2. Implementar + testar localmente.
3. Commit limpo (sem artefatos gerados).
4. Abrir PR com checklist técnico.
5. Merge para `main` após validação funcional.
6. Tag de versão quando fechar bloco de prioridades.
