# 🔒 SSL Certificate Fix - Feature Branch

## 📋 Branch: `feature/ssl-certificate-fix`

### 🎯 **Objetivo**
Corrigir certificados SSL expirados/inválidos nos domínios:
- `api.consultoriawk.com`
- `admin.consultoriawk.com`

### 🔍 **Problema Identificado**
```
curl: (35) schannel: next InitializeSecurityContext failed: SEC_E_ILLEGAL_MESSAGE
```
- Certificados SSL expirados ou inválidos
- URLs externas inacessíveis via HTTPS
- Let's Encrypt precisa renovação

---

## 🛠️ **Solução Implementada**

### **1. Scripts de Diagnóstico e Correção**

#### `scripts/ssl-check.sh`
- 🔍 Diagnóstico completo dos certificados
- 📊 Verificação de datas de expiração
- 🧪 Teste de conectividade SSL
- 📋 Relatório detalhado

#### `scripts/ssl-renew.sh`
- 🔄 Renovação automática via Certbot
- 🔧 Recriação de certificados se necessário
- 💾 Backup automático das configurações
- ⚙️ Configuração de renovação automática (cron)

#### `scripts/health-check.sh`
- 🏥 Verificação completa do sistema
- 🌐 Teste de URLs externas
- 📊 Monitoramento de recursos
- 📝 Análise de logs de erro

### **2. Scripts de Deploy Automatizado**

#### `ssl-fix-deploy.ps1` (PowerShell)
- 🚀 Deploy automático dos scripts na VPS
- 🧪 Execução remota via SSH
- 📋 Múltiplos modos: check, renew, health, all
- 🔍 Modo dry-run para testes

---

## 🚀 **Como Usar**

### **Desenvolvimento Local**
```powershell
# Verificar branch atual
git status

# Testar deploy em modo dry-run
.\ssl-fix-deploy.ps1 -Action all -DryRun

# Deploy real
.\ssl-fix-deploy.ps1 -Action all
```

### **Execução Individual**
```powershell
# Apenas diagnóstico
.\ssl-fix-deploy.ps1 -Action check

# Apenas renovação SSL
.\ssl-fix-deploy.ps1 -Action renew

# Apenas health check
.\ssl-fix-deploy.ps1 -Action health
```

### **Execução Manual na VPS**
```bash
# Conectar à VPS
ssh root@72.60.254.100

# Navegar para o projeto
cd /opt/wk-crm

# Executar scripts individuais
bash scripts/ssl-check.sh     # Diagnóstico
bash scripts/ssl-renew.sh     # Renovação
bash scripts/health-check.sh  # Health check
```

---

## 🧪 **Testes de Validação**

### **1. Testes Locais (antes do deploy)**
- ✅ Scripts executam sem erro de sintaxe
- ✅ PowerShell script funciona em modo dry-run
- ✅ SSH conexão está funcionando
- ✅ Permissões dos scripts estão corretas

### **2. Testes na VPS**
- ✅ Scripts copiados corretamente
- ✅ Certificados SSL renovados
- ✅ Nginx configuração válida
- ✅ Serviços reiniciados com sucesso

### **3. Testes de Integração**
- ✅ `https://api.consultoriawk.com/api/health` acessível
- ✅ `https://admin.consultoriawk.com/` acessível
- ✅ Certificados válidos por > 60 dias
- ✅ Renovação automática configurada

---

## 📊 **Critérios de Aceitação**

### **✅ Funcional**
- [ ] Certificados SSL válidos e funcionais
- [ ] URLs externas acessíveis via HTTPS
- [ ] Renovação automática configurada
- [ ] Health check passa sem erros críticos

### **✅ Não-Funcional**
- [ ] Scripts são idempotentes (podem ser executados múltiplas vezes)
- [ ] Backup automático das configurações
- [ ] Logs detalhados de todas as operações
- [ ] Rollback automático em caso de falha

### **✅ Documentação**
- [ ] README com instruções claras
- [ ] Comentários nos scripts
- [ ] Guia de troubleshooting
- [ ] Plano de rollback

---

## 🔄 **Processo CI/CD**

### **1. Desenvolvimento**
```bash
git checkout -b feature/ssl-certificate-fix
# Implementar mudanças
git add .
git commit -m "feat(ssl): implementar renovação automática de certificados"
```

### **2. Testes Locais**
```powershell
# Testar scripts
.\ssl-fix-deploy.ps1 -DryRun

# Validar sintaxe
Get-Content scripts/*.sh | ForEach-Object { bash -n $_ }
```

### **3. Push e PR**
```bash
git push origin feature/ssl-certificate-fix
# Criar Pull Request no GitHub
# Solicitar code review
```

### **4. Deploy em Staging/Produção**
```bash
# Após aprovação do PR
git checkout main
git merge feature/ssl-certificate-fix
.\ssl-fix-deploy.ps1 -Action all
```

---

## 🚨 **Plano de Rollback**

### **Em caso de falha:**
1. **Restaurar backup do Nginx:**
   ```bash
   rm -rf /etc/nginx/sites-available
   mv /etc/nginx/sites-available.backup.* /etc/nginx/sites-available
   systemctl reload nginx
   ```

2. **Restaurar certificados:**
   ```bash
   rm -rf /etc/letsencrypt
   mv /etc/letsencrypt.backup.* /etc/letsencrypt
   ```

3. **Reverter mudanças no Git:**
   ```bash
   git revert HEAD
   git push origin main
   ```

---

## 📈 **Monitoramento Pós-Deploy**

### **URLs para monitorar:**
- https://api.consultoriawk.com/api/health
- https://admin.consultoriawk.com/

### **Logs para verificar:**
- `/var/log/nginx/error.log`
- `/var/log/letsencrypt/letsencrypt.log`
- `/opt/wk-crm/wk-crm-laravel/storage/logs/laravel.log`

### **Comandos úteis:**
```bash
# Verificar status dos certificados
certbot certificates

# Verificar configuração do nginx
nginx -t

# Verificar serviços
systemctl status nginx php8.2-fpm postgresql

# Health check manual
bash /opt/wk-crm/scripts/health-check.sh
```

---

## 🎯 **Resultados Esperados**

Após a implementação desta feature:
- ✅ SSL funcionando 100%
- ✅ URLs externas acessíveis
- ✅ Renovação automática configurada
- ✅ Monitoramento implementado
- ✅ Processo CI/CD estabelecido

**Status: Pronto para Code Review e Deploy** 🚀