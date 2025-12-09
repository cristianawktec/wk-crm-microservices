# 🚀 Instruções de Deploy - VPS Hostinger

## ✅ O que foi feito (Localhost)

1. ✅ Autenticação obrigatória na tela de login
2. ✅ Validação de token com backend antes de acessar dashboard
3. ✅ CORS middleware adicionado
4. ✅ Frontend buildado com URL da API corrigida (`/api` - relative URL)

## 📋 Próximas Ações na VPS

### 1️⃣ **SSH na VPS**

```bash
ssh root@seu-servidor-vps
# ou
ssh seu-usuario@seu-servidor-vps
```

### 2️⃣ **Clonar/Atualizar o Repositório**

Se já tem o repositório:
```bash
cd /var/www/crm  # ou o diretório onde está o projeto
git pull origin main
```

Se não tem ainda:
```bash
cd /var/www
git clone https://github.com/cristianawktec/wk-crm-microservices.git crm
cd crm
```

### 3️⃣ **Instalar Dependências do Laravel**

```bash
cd wk-crm-laravel
composer install --no-dev --optimize-autoloader
```

### 4️⃣ **Rodar Migrações**

```bash
php artisan migrate --force
```

### 5️⃣ **Limpar Cache**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6️⃣ **Deploy do Frontend**

```bash
# Copiar build para o diretório web root
rm -rf /var/www/html/admin/*
cp -r /var/www/crm/wk-admin-frontend/dist/admin-frontend/* /var/www/html/admin/

# Ajustar permissões
chown -R www-data:www-data /var/www/html/admin
chmod -R 755 /var/www/html/admin
```

### 7️⃣ **Configurar Nginx**

Certifique-se que o arquivo `/etc/nginx/sites-available/default` (ou seu vhost) tem:

```nginx
# Para admin.consultoriawk.com
server {
    listen 443 ssl http2;
    server_name admin.consultoriawk.com;
    
    root /var/www/html/admin;
    index index.html;
    
    # SPA routing - tudo que não é arquivo estático vai para index.html
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    ssl_certificate /path/to/cert;
    ssl_certificate_key /path/to/key;
}

# Para api.consultoriawk.com
server {
    listen 443 ssl http2;
    server_name api.consultoriawk.com;
    
    root /var/www/crm/wk-crm-laravel/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    ssl_certificate /path/to/cert;
    ssl_certificate_key /path/to/key;
}
```

### 8️⃣ **Reiniciar Nginx**

```bash
nginx -t  # Testar configuração
systemctl restart nginx
```

### 9️⃣ **Testar URLs**

```bash
# Frontend
curl -I https://admin.consultoriawk.com

# API Health
curl -I https://api.consultoriawk.com/api/health

# Teste de autenticação
curl -X POST https://api.consultoriawk.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@consultoriawk.com","password":"Admin@123456"}'
```

## 🔧 Verificação de Arquivos Importantes

### Verificar se os arquivos estão lá:

```bash
# Frontend build
ls -la /var/www/html/admin/index.html

# Laravel API
ls -la /var/www/crm/wk-crm-laravel/public/index.php

# Arquivo de configuração CORS
cat /var/www/crm/wk-crm-laravel/config/cors.php
```

## ⚠️ Possíveis Problemas

### Problema: CORS error na console do navegador
**Solução**: Verificar se o CORS middleware está ativo no `bootstrap/app.php`

### Problema: 404 na API
**Solução**: Verificar Nginx routing para `/api/*`

### Problema: Permissões negadas
**Solução**: 
```bash
chown -R www-data:www-data /var/www/crm
chown -R www-data:www-data /var/www/html/admin
chmod -R 755 /var/www/crm/storage
chmod -R 755 /var/www/crm/bootstrap/cache
```

### Problema: PHP version incompatível
**Solução**: 
```bash
php -v  # Verificar versão (precisa 8.2+)
composer install --no-dev --ignore-platform-req=php
```

## 📊 Teste Final

1. Abra `https://admin.consultoriawk.com`
2. Você deve ser **redirecionado para login** (não mais direto para dashboard)
3. Faça login com: `admin@consultoriawk.com` / `Admin@123456`
4. Você deve ver o dashboard com dados ✅

## 🎯 URLs Finais

- **Frontend**: `https://admin.consultoriawk.com`
- **API**: `https://api.consultoriawk.com/api`
- **Health Check**: `https://api.consultoriawk.com/api/health`

## 📝 Commits Relevantes

```
43bba43 - fix: use relative API URL for production environment
6c7305c - feat: add CORS middleware and configuration for API endpoints
4e33142 - fix: use /auth/me endpoint for token verification
de2b747 - fix: enforce token validation on frontend and disable stale token redirect
```

---

**Qualquer dúvida durante o deploy, me avise!**
