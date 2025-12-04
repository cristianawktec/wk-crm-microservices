# 🚀 Workflow CI/CD - WK CRM Microservices

## 📋 Metodologia: GitFlow + CI/CD

### 🌲 Estratégia de Branches
```
main (production) 
├── develop (integration)
├── feature/ssl-fix (nossa branch atual)
├── hotfix/* (correções urgentes)
└── release/* (preparação para produção)
```

### 🔄 Fluxo de Desenvolvimento

#### 1. **Desenvolvimento Local**
- Criar branch feature
- Implementar correções
- Testes locais
- Commit com padrões

#### 2. **Code Review**
- Push para GitHub
- Pull Request (PR)
- Review de código
- Testes automatizados

#### 3. **Deploy Staging**
- Merge em develop
- Deploy automático para staging
- Testes de integração

#### 4. **Deploy Production**
- Merge em main
- Deploy automático para VPS
- Monitoramento

---

## 🎯 Tarefa Atual: SSL Fix

### **Branch:** `feature/ssl-certificate-fix`
### **Objetivo:** Corrigir certificados SSL expirados

### **Checklist de Desenvolvimento:**
- [ ] Criar branch feature/ssl-certificate-fix
- [ ] Diagnosticar problema SSL atual
- [ ] Criar script de renovação automática
- [ ] Implementar health check SSL
- [ ] Documentar solução
- [ ] Testes locais
- [ ] Commit com padrões
- [ ] Push e PR
- [ ] Code Review
- [ ] Deploy em VPS

---

## 📝 Padrões de Commit

### **Formato:**
```
<tipo>(<escopo>): <descrição>

<corpo opcional>

<footer opcional>
```

### **Tipos:**
- `feat`: Nova funcionalidade
- `fix`: Correção de bug
- `docs`: Documentação
- `style`: Formatação
- `refactor`: Refatoração
- `test`: Testes
- `chore`: Manutenção

### **Exemplos:**
```bash
feat(ssl): implementar renovação automática de certificados
fix(nginx): corrigir configuração SSL para HTTPS
docs(deploy): adicionar guia de troubleshooting SSL
chore(ci): configurar GitHub Actions para deploy
```

---

## 🔧 Scripts CI/CD

### **1. Script de Health Check**
```bash
#!/bin/bash
# scripts/health-check.sh
echo "🔍 Verificando saúde do sistema..."
curl -f https://api.consultoriawk.com/api/health || exit 1
curl -f https://admin.consultoriawk.com/ || exit 1
echo "✅ Sistema saudável!"
```

### **2. Script de Deploy**
```bash
#!/bin/bash
# scripts/deploy-production.sh
echo "🚀 Iniciando deploy em produção..."
git checkout main
git pull origin main
./deploy.sh
./scripts/health-check.sh
echo "✅ Deploy concluído!"
```

### **3. Script de Rollback**
```bash
#!/bin/bash
# scripts/rollback.sh
echo "🔄 Fazendo rollback..."
git checkout HEAD~1
./deploy.sh
echo "✅ Rollback concluído!"
```

---

## 🧪 Estratégia de Testes

### **Testes Locais:**
- ✅ SSL certificate validation
- ✅ API endpoints funcionais
- ✅ AdminLTE carregando
- ✅ Database connectivity

### **Testes de Staging:**
- ✅ Deploy script validation
- ✅ Service restart verification
- ✅ External URL accessibility
- ✅ Performance check

### **Testes de Produção:**
- ✅ Health check endpoints
- ✅ SSL certificate expiry
- ✅ Load balancer status
- ✅ Database backup

---

## 📊 Monitoramento Contínuo

### **Métricas:**
- 🔒 SSL certificate expiry
- 🌐 API response time
- 📊 Database performance
- 💾 Server resources

### **Alertas:**
- SSL expirando em 7 dias
- API response > 2s
- Database connection errors
- Server CPU > 80%

---

## 🎯 Próximos Passos

### **Fase 1: SSL Fix (Atual)**
1. Criar branch `feature/ssl-certificate-fix`
2. Diagnosticar e corrigir SSL
3. Implementar renovação automática
4. Code review e merge

### **Fase 2: CI/CD Pipeline**
1. Configurar GitHub Actions
2. Implementar deploy automático
3. Configurar testes automatizados
4. Setup de monitoramento

### **Fase 3: Infraestrutura**
1. Docker production
2. Load balancer
3. Database replication
4. Backup automático

---

**🚀 Vamos começar com a Fase 1: SSL Fix!**