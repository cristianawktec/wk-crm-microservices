# 🔧 Correção API Routing VPS - Instruções de Execução

## 📋 Resumo do Problema
- API Laravel na VPS retornando erro 404
- SSL funcionando corretamente
- Aplicação local funcionando perfeitamente
- Problema identificado: configuração de routing do Nginx

## 🚀 Execução da Correção

### 1. Upload do Script para VPS

```bash
# No Windows (PowerShell/Git Bash)
scp scripts/fix-api-routing.sh root@72.60.254.100:/tmp/

# Ou usando WinSCP/FileZilla:
# Arquivo: c:\xampp\htdocs\crm\scripts\fix-api-routing.sh
# Destino: /tmp/fix-api-routing.sh
```

### 2. Executar na VPS

```bash
# Conectar via SSH
ssh root@72.60.254.100

# Dar permissão de execução
chmod +x /tmp/fix-api-routing.sh

# Executar o script
/tmp/fix-api-routing.sh
```

## 🎯 O que o Script Faz

### ✅ Diagnósticos
- [x] Verifica se diretório Laravel existe
- [x] Verifica arquivo index.php
- [x] Testa permissões de arquivos

### 🔧 Correções Aplicadas
- [x] **Permissões**: Corrige ownership e permissões (www-data)
- [x] **Cache Laravel**: Limpa e reconstrói todos os caches
- [x] **Nginx Config**: Aplica configuração otimizada
- [x] **Serviços**: Reinicia PHP-FPM e Nginx

### 🧪 Testes Automatizados
- [x] Teste direto do index.php
- [x] Teste da API Health local
- [x] Teste da API Health externa

## 📊 Configuração Nginx Aplicada

### Principais Melhorias:
1. **Root Directory**: `/opt/wk-crm/wk-crm-laravel/public`
2. **Laravel Routing**: `try_files $uri $uri/ /index.php?$query_string`
3. **FastCGI Otimizado**: Parâmetros PATH_INFO corretos
4. **CORS Completo**: Headers para todas as requisições
5. **SSL Forçado**: Redirect automático HTTP → HTTPS

## 🔍 Como Verificar se Funcionou

### Testes Manuais:
```bash
# 1. Teste básico
curl -I https://api.consultoriawk.com/

# 2. Teste API Health
curl https://api.consultoriawk.com/api/health

# 3. Teste com cabeçalhos
curl -H "Accept: application/json" https://api.consultoriawk.com/api/health
```

### URLs para Testar no Navegador:
- ✅ https://api.consultoriawk.com/
- ✅ https://api.consultoriawk.com/api/health
- ✅ https://api.consultoriawk.com/api/dashboard

## 📝 Logs para Monitorar

```bash
# Logs do Nginx
tail -f /var/log/nginx/api.consultoriawk.com.error.log
tail -f /var/log/nginx/api.consultoriawk.com.access.log

# Logs do Laravel
tail -f /opt/wk-crm/wk-crm-laravel/storage/logs/laravel.log

# Status dos serviços
systemctl status nginx
systemctl status php8.2-fpm
```

## 🔄 Se Algo Der Errado

### Backup Automático:
- O script cria backup da configuração Nginx atual
- Localização: `/etc/nginx/sites-available/api.consultoriawk.com.backup.YYYYMMDD_HHMMSS`

### Restaurar Backup:
```bash
# Listar backups disponíveis
ls -la /etc/nginx/sites-available/api.consultoriawk.com.backup.*

# Restaurar último backup
cp /etc/nginx/sites-available/api.consultoriawk.com.backup.* /etc/nginx/sites-available/api.consultoriawk.com

# Testar e recarregar
nginx -t && systemctl reload nginx
```

## 📈 Próximos Passos Após Correção

1. ✅ **Verificar API funcionando**
2. ✅ **Testar CORS headers**
3. ✅ **Validar SSL e redirecionamento**
4. 🔄 **Fazer commit das alterações**
5. 🔄 **Atualizar documentação**
6. 🔄 **Implementar monitoramento**

## 💡 Prevenção de Problemas Futuros

### Deploy Checklist:
- [ ] Sempre testar configuração Nginx: `nginx -t`
- [ ] Verificar permissões após upload
- [ ] Limpar caches Laravel após mudanças
- [ ] Testar API Health após deploy
- [ ] Monitorar logs por alguns minutos

### Comando de Deploy Rápido:
```bash
# Para futuros deploys
cd /opt/wk-crm/wk-crm-laravel
php artisan config:clear && php artisan route:clear && php artisan cache:clear
php artisan config:cache && php artisan route:cache
systemctl reload nginx
```

---

**Estimated Time**: 5-10 minutos
**Risk Level**: Baixo (script faz backup automático)
**Expected Result**: API 404 → API 200 OK ✅