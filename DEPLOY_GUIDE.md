# 🚀 Deploy Guide - WK CRM para VPS Hostinger

## 📋 Checklist de Deploy

### 1. 🔄 Sincronizar código no VPS

```bash
# Conectar via SSH ao VPS
ssh root@your-vps-ip

# Navegar para o diretório do projeto
cd /opt/wk-crm

# Fazer pull das últimas mudanças
git pull origin main

# Ou se for primeira instalação:
# git clone https://github.com/cristianawktec/wk-crm-microservices.git /opt/wk-crm
```

### 2. 🎨 Configurar AdminLTE Frontend

```bash
# Copiar arquivos do AdminLTE para nginx
cp -r /opt/wk-crm/wk-admin-simple/* /var/www/html/admin/

# Verificar permissões
chown -R www-data:www-data /var/www/html/admin/
chmod -R 755 /var/www/html/admin/
```

### 3. ⚙️ Configurar Laravel API

```bash
# Navegar para Laravel
cd /opt/wk-crm/wk-crm-laravel

# Instalar dependências
composer install --optimize-autoloader --no-dev

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Executar migrações
php artisan migrate --force

# Cache de configuração
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. 🌐 Configurar Nginx

```bash
# Copiar configuração do nginx
cp /opt/wk-crm/laravel_nginx.conf /etc/nginx/sites-available/api.consultoriawk.com

# Habilitar site
ln -sf /etc/nginx/sites-available/api.consultoriawk.com /etc/nginx/sites-enabled/

# Testar configuração
nginx -t

# Recarregar nginx
systemctl reload nginx
```

### 5. 🔧 Configurar PHP-FPM

```bash
# Verificar se PHP-FPM está rodando
systemctl status php8.2-fpm

# Se necessário, reiniciar
systemctl restart php8.2-fpm
```

### 6. 🔒 Configurar SSL (se necessário)

```bash
# Instalar certbot se não estiver instalado
apt update
apt install certbot python3-certbot-nginx

# Obter certificado SSL
certbot --nginx -d api.consultoriawk.com
```

### 7. 📱 URLs de Acesso

Após o deploy, os serviços estarão disponíveis em:

- **🎨 AdminLTE Interface**: https://consultoriawk.com/admin/
- **📡 Laravel API**: https://api.consultoriawk.com/api/
- **📊 Dashboard**: https://consultoriawk.com/admin/index.html
- **👥 Gestão de Clientes**: https://consultoriawk.com/admin/customers.html

### 8. ✅ Testes Pós-Deploy

```bash
# Testar API Laravel
curl -X GET https://api.consultoriawk.com/api/customers \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"

# Verificar logs do Laravel
tail -f /opt/wk-crm/wk-crm-laravel/storage/logs/laravel.log

# Verificar logs do Nginx
tail -f /var/log/nginx/api.consultoriawk.com.error.log
```

## 🔧 Comandos Úteis

### Verificar Status dos Serviços
```bash
systemctl status nginx
systemctl status php8.2-fpm
systemctl status mysql
```

### Logs em Tempo Real
```bash
# Nginx
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# Laravel
tail -f /opt/wk-crm/wk-crm-laravel/storage/logs/laravel.log
```

### Reiniciar Serviços
```bash
systemctl restart nginx
systemctl restart php8.2-fpm
```

## 🚨 Troubleshooting

### Problema: 502 Bad Gateway
```bash
# Verificar se PHP-FPM está rodando
systemctl status php8.2-fpm

# Verificar logs do PHP-FPM
tail -f /var/log/php8.2-fpm.log

# Reiniciar se necessário
systemctl restart php8.2-fpm
```

### Problema: Permissões Laravel
```bash
# Corrigir permissões
cd /opt/wk-crm/wk-crm-laravel
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Problema: AdminLTE não carrega API
- Verificar se o JavaScript está carregando: `js/api-connection.js`
- Verificar CORS no Laravel API
- Verificar URLs da API no arquivo JavaScript

## 📊 Monitoramento

### Verificar Performance
```bash
# Uso de CPU e memória
htop

# Espaço em disco
df -h

# Conexões ativas
netstat -an | grep :80
netstat -an | grep :443
```

### Backup Automático
```bash
# Adicionar ao crontab
crontab -e

# Backup diário às 2h da manhã
0 2 * * * /opt/wk-crm/scripts/backup.sh
```

## 🎯 Próximos Passos

1. ✅ **Deploy Completo** - Todos os arquivos sincronizados
2. ✅ **AdminLTE Funcionando** - Interface responsiva ativa
3. ✅ **API Laravel Ativa** - Endpoints DDD funcionais
4. 🔄 **SSL Configurado** - HTTPS ativo
5. 📊 **Monitoramento** - Logs e métricas

---

**🎉 Deploy realizado com sucesso!**
**🌐 AdminLTE + Laravel API + DDD + Microserviços ativos**