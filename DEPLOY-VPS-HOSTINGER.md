# 🚀 Deploy VPS Hostinger - WK CRM Microservices

## 📋 Pré-requisitos

### 🔑 Credenciais VPS
- **IP:** 72.60.254.100
- **Usuário:** root
- **Banco:** PostgreSQL wk_crm_production
- **Repositório:** https://github.com/cristianawktec/wk-crm-microservices.git

### 🛠️ Ferramentas Necessárias
- SSH Client (PuTTY, PowerShell SSH, ou terminal)
- Acesso root à VPS
- Git configurado na VPS

---

## 🎯 Etapa 1: Conectar à VPS

### Via PowerShell (Windows)
```powershell
# Conectar via SSH
ssh root@72.60.254.100

# Se precisar de chave SSH específica:
# ssh -i path/to/private-key root@72.60.254.100
```

### Via PuTTY
```
Host: 72.60.254.100
Port: 22
Connection Type: SSH
Username: root
```

---

## 🔄 Etapa 2: Atualizar Código do Git

### Navegar para o diretório do projeto
```bash
cd /opt/wk-crm

# Verificar status atual
git status
git log --oneline -5

# Fazer backup antes de atualizar
cp -r /opt/wk-crm /opt/wk-crm-backup-$(date +%Y%m%d_%H%M%S)
```

### Puxar últimas atualizações
```bash
# Fazer fetch das mudanças
git fetch origin

# Reset para garantir estado limpo
git reset --hard origin/main

# Pull das últimas mudanças
git pull origin main --no-edit

# Verificar se atualizou
git log --oneline -3
```

---

## ⚙️ Etapa 3: Atualizar Laravel API

### Navegar para Laravel
```bash
cd /opt/wk-crm/wk-crm-laravel
```

### Atualizar dependências
```bash
# Composer install/update
composer install --optimize-autoloader --no-dev

# Verificar se .env está correto
cat .env | grep -E "(DB_HOST|DB_DATABASE|APP_URL)"
```

### Atualizar cache e migrações
```bash
# Limpar caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Executar migrações se houver
php artisan migrate --force

# Recriar caches otimizados
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Verificar permissões
```bash
# Ajustar permissões
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## 🎨 Etapa 4: Atualizar AdminLTE Frontend

### Copiar arquivos atualizados
```bash
# Backup do admin atual
cp -r /var/www/html/admin /var/www/html/admin-backup-$(date +%Y%m%d_%H%M%S)

# Copiar nova versão
cp -r /opt/wk-crm/wk-admin-simple/* /var/www/html/admin/

# Ajustar permissões
chown -R www-data:www-data /var/www/html/admin
chmod -R 755 /var/www/html/admin
```

---

## 🌐 Etapa 5: Atualizar Nginx

### Verificar e atualizar configuração
```bash
# Copiar nova configuração se houver mudanças
cp /opt/wk-crm/laravel_nginx.conf /etc/nginx/sites-available/api.consultoriawk.com

# Testar configuração
nginx -t

# Se OK, recarregar
systemctl reload nginx
```

---

## 🔄 Etapa 6: Reiniciar Serviços

### PHP-FPM
```bash
# Verificar status
systemctl status php8.2-fpm

# Reiniciar
systemctl restart php8.2-fpm
```

### Nginx
```bash
# Verificar status
systemctl status nginx

# Reiniciar se necessário
systemctl restart nginx
```

### PostgreSQL (se necessário)
```bash
# Verificar status
systemctl status postgresql

# Reiniciar se necessário
systemctl restart postgresql
```

---

## 🧪 Etapa 7: Testes de Funcionamento

### Teste 1: AdminLTE Frontend
```bash
# Teste local
curl -I http://localhost/admin/

# Verificar resposta HTTP 200
```

### Teste 2: Laravel API
```bash
# Health check
curl -X GET http://localhost/api/health

# Dashboard dados
curl -X GET http://localhost/api/dashboard
```

### Teste 3: Banco de Dados
```bash
# Conectar ao PostgreSQL
psql -h localhost -U wk_user -d wk_crm_production

# Dentro do psql:
\l                          # Listar databases
\c wk_crm_production       # Conectar
\dt                        # Listar tabelas
SELECT COUNT(*) FROM customers;
SELECT COUNT(*) FROM leads;
\q                         # Sair
```

### Teste 4: URLs Externas
```bash
# Teste via curl externo (de outra máquina)
curl -I https://consultoriawk.com/admin/
curl -I https://api.consultoriawk.com/api/health
```

---

## 🔍 Etapa 8: Verificação e Monitoramento

### Logs para verificar
```bash
# Logs do Nginx
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# Logs do Laravel
tail -f /opt/wk-crm/wk-crm-laravel/storage/logs/laravel.log

# Logs do PHP-FPM
tail -f /var/log/php8.2-fpm.log

# Logs do sistema
journalctl -f -u nginx
journalctl -f -u php8.2-fpm
```

### Verificar processos
```bash
# Verificar processos ativos
ps aux | grep -E "(nginx|php-fpm|postgres)"

# Verificar portas em uso
netstat -tlnp | grep -E "(80|443|5432)"
```

---

## 📊 Etapa 9: Validação Final

### ✅ Checklist de Validação

- [ ] **Git atualizado:** `git log --oneline -3` mostra commits recentes
- [ ] **Laravel funcionando:** `curl http://localhost/api/health` retorna JSON
- [ ] **AdminLTE acessível:** `curl -I http://localhost/admin/` retorna 200
- [ ] **Banco conectado:** `php artisan migrate:status` sem erros
- [ ] **Nginx OK:** `nginx -t` sem erros
- [ ] **PHP-FPM ativo:** `systemctl is-active php8.2-fpm` retorna "active"
- [ ] **URLs externas:** Sites acessíveis de fora da VPS

### 🌐 URLs para Testar
1. **AdminLTE:** https://consultoriawk.com/admin/
2. **API Health:** https://api.consultoriawk.com/api/health
3. **API Dashboard:** https://api.consultoriawk.com/api/dashboard

---

## 🚨 Troubleshooting

### Problema: Git não atualiza
```bash
# Força atualização
git fetch --all
git reset --hard origin/main
git clean -fd
```

### Problema: Laravel com erro
```bash
# Verificar logs
tail -20 storage/logs/laravel.log

# Limpar tudo e reconfigurar
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

### Problema: Nginx não carrega
```bash
# Verificar configuração
nginx -t

# Verificar logs
tail -20 /var/log/nginx/error.log

# Reiniciar completamente
systemctl restart nginx
```

### Problema: Banco não conecta
```bash
# Testar conexão
psql -h localhost -U wk_user -d wk_crm_production -c "SELECT 1;"

# Verificar serviço PostgreSQL
systemctl status postgresql
```

---

## 🎉 Deploy Automático (Opcional)

### Script de Deploy Completo
```bash
# Executar o script automático
cd /opt/wk-crm
chmod +x deploy.sh
./deploy.sh
```

**Este script executa todas as etapas automaticamente!**

---

## 📞 Suporte

**Em caso de problemas:**
1. Verificar logs específicos da seção troubleshooting
2. Executar testes de validação um por um
3. Fazer rollback se necessário: `cp -r /opt/wk-crm-backup-* /opt/wk-crm`

**🚀 Deploy concluído com sucesso quando todos os testes passarem!**