# 📋 Convenção de Nomenclatura de Branches - WK CRM

## 🎯 **Padrão Adotado:**

### **Formato:** `<tipo>/<descrição-da-tarefa>`

### **Tipos de Branch:**
- `feature/` - Novas funcionalidades
- `bugfix/` - Correções de bugs
- `hotfix/` - Correções urgentes em produção
- `improvement/` - Melhorias e otimizações
- `docs/` - Atualizações de documentação
- `refactor/` - Refatoração de código
- `test/` - Implementação de testes

### **Exemplos Práticos:**
```
✅ CORRETO:
feature/sistema-autenticacao
bugfix/correcao-api-routing
hotfix/ssl-certificados
improvement/performance-database
docs/api-documentation
refactor/laravel-controllers
test/unit-customers

❌ EVITAR:
feature/ssl-certificate-fix (muito genérico)
fix/bug (sem contexto)
improvement/stuff (sem especificidade)
```

### **Nomenclatura Descritiva:**
- Use **kebab-case** (palavras separadas por hífen)
- Seja **específico** sobre o que está sendo feito
- Use **português** para clareza da equipe
- Máximo **3-4 palavras** na descrição

---

## 🚀 **Para Nossa Tarefa Atual:**

### **Branch Atual:** `bugfix/correcao-api-routing-vps`

**Por que este nome?**
- `bugfix/` - É uma correção de bug (API retorna 404)
- `correcao-api-routing` - Específica sobre o problema
- `vps` - Indica que é específico da VPS (não local)

### **Retrospectiva da Branch Anterior:**
- **Era:** `feature/ssl-certificate-fix`
- **Deveria ser:** `hotfix/correcao-ssl-certificados`

---

## 📊 **Benefícios da Nova Convenção:**

### **✅ Organização:**
- Histórico claro do Git
- Identificação rápida do propósito
- Facilita code review

### **✅ Colaboração:**
- Equipe entende imediatamente o contexto
- Pull requests mais organizados
- Menos confusão em projetos grandes

### **✅ Manutenção:**
- Facilita encontrar branches antigas
- Melhor rastreabilidade de mudanças
- Documentação automática via Git

---

## 🎯 **Próximas Branches Planejadas:**

```
bugfix/correcao-api-routing-vps     ← ATUAL
improvement/github-actions-ci-cd    ← Próxima
feature/dashboard-analytics         ← Futura
feature/sistema-autenticacao        ← Futura
improvement/performance-database    ← Futura
docs/api-documentation             ← Futura
test/cobertura-testes-api          ← Futura
```

---

## ✅ **Checklist para Criar Branch:**

1. **Nome descritivo?** ✅
2. **Tipo correto?** ✅
3. **Português/Inglês consistente?** ✅
4. **Máximo 4 palavras?** ✅
5. **Kebab-case?** ✅

**Exemplo de comando:**
```bash
git checkout -b bugfix/correcao-api-routing-vps
```

---

**Convenção aprovada e implementada! 🎯**