# 🔧 Correção API Routing VPS - Comando Direto SSH

## 🚀 Execução Direta via SSH (Sem Upload)

Se preferir executar diretamente sem fazer upload do script:

```bash
ssh root@72.60.254.100 << 'EOF'
# Correção API Routing - Execução Direta
echo "🔧 Iniciando correção API Routing..."

PROJECT_DIR="/opt/wk-crm/wk-crm-laravel"

# 1. Verificar diretório
if [ ! -d "$PROJECT_DIR" ]; then
    echo "❌ Diretório não encontrado: $PROJECT_DIR"
    exit 1
fi
echo "✅ Diretório Laravel encontrado"

# 2. Corrigir permissões
echo "🔧 Corrigindo permissões..."
chown -R www-data:www-data "$PROJECT_DIR"
chmod -R 755 "$PROJECT_DIR/storage"
chmod -R 755 "$PROJECT_DIR/bootstrap/cache"

# 3. Limpar caches
echo "🧹 Limpando caches Laravel..."
cd "$PROJECT_DIR"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 4. Recriar caches
echo "⚡ Recriando caches..."
php artisan config:cache
php artisan route:cache

# 5. Backup configuração Nginx
NGINX_CONFIG="/etc/nginx/sites-available/api.consultoriawk.com"
cp "$NGINX_CONFIG" "${NGINX_CONFIG}.backup.$(date +%Y%m%d_%H%M%S)"
echo "✅ Backup Nginx criado"

# 6. Aplicar nova configuração
echo "📝 Aplicando configuração Nginx..."
cat > "$NGINX_CONFIG" << 'NGINX_EOF'
server {
    listen 80;
    listen 443 ssl http2;
    server_name api.consultoriawk.com;

    ssl_certificate /etc/letsencrypt/live/api.consultoriawk.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.consultoriawk.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    if ($scheme != "https") {
        return 301 https://$host$request_uri;
    }

    root /opt/wk-crm/wk-crm-laravel/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
        fastcgi_param HTTP_PROXY "";
        
        # NOTE: CORS headers are centralized in the repository's reverse proxy (infrastructure/nginx/nginx.conf).
        # If you are deploying this site standalone, uncomment/adapt the lines below to enable CORS:
        # add_header Access-Control-Allow-Origin * always;
        # add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
        # add_header Access-Control-Allow-Headers "Authorization, Content-Type, Accept, X-Requested-With" always;
    }

    if ($request_method = OPTIONS) {
        # NOTE: CORS headers are centralized in the repository's reverse proxy (infrastructure/nginx/nginx.conf).
        # If you are deploying this site standalone, uncomment/adapt the lines below to enable CORS:
        # add_header Access-Control-Allow-Origin * always;
        # add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always;
        # add_header Access-Control-Allow-Headers "Authorization, Content-Type, Accept, X-Requested-With" always;
        add_header Content-Length 0;
        add_header Content-Type text/plain;
        return 200;
    }

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;

    access_log /var/log/nginx/api.consultoriawk.com.access.log;
    error_log /var/log/nginx/api.consultoriawk.com.error.log;

    location ~ /\. {
        deny all;
    }

    location ~* \.(log|sql|conf)$ {
        deny all;
    }
}
NGINX_EOF

# 7. Testar configuração
echo "🧪 Testando configuração Nginx..."
if nginx -t; then
    echo "✅ Configuração válida"
    systemctl restart php8.2-fpm
    systemctl reload nginx
    echo "✅ Serviços reiniciados"
else
    echo "❌ Configuração inválida - restaurando backup"
    mv "${NGINX_CONFIG}.backup."* "$NGINX_CONFIG"
    exit 1
fi

# 8. Testes de funcionamento
echo "🧪 Testando API..."
sleep 2
curl -s "http://localhost/api/health" || echo "Teste local com problemas"
curl -s -I "https://api.consultoriawk.com/api/health" || echo "Teste externo com problemas"

echo "🎉 Correção concluída!"
echo "📝 Teste: https://api.consultoriawk.com/api/health"
EOF
```

## 🎯 Resultado Esperado

Após executar o comando, você deve ver:
```
✅ Diretório Laravel encontrado
🔧 Corrigindo permissões...
🧹 Limpando caches Laravel...
⚡ Recriando caches...
✅ Backup Nginx criado
📝 Aplicando configuração Nginx...
🧪 Testando configuração Nginx...
✅ Configuração válida
✅ Serviços reiniciados
🧪 Testando API...
🎉 Correção concluída!
📝 Teste: https://api.consultoriawk.com/api/health
```

## ⚡ Comando Ultra-Rápido (Uma Linha)

Se quiser ainda mais rápido:

```bash
ssh root@72.60.254.100 "cd /opt/wk-crm/wk-crm-laravel && php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan config:cache && php artisan route:cache && chown -R www-data:www-data . && chmod -R 755 storage bootstrap/cache && systemctl restart php8.2-fpm && systemctl reload nginx && echo '🎉 Correção básica aplicada! Teste: https://api.consultoriawk.com/api/health'"
```

---

**💡 Escolha sua opção:**
1. **Script completo** (recomendado): Upload + execução do fix-api-routing.sh
2. **SSH direto**: Copia/cola o comando direto no terminal
3. **Ultra-rápido**: Uma linha só para correção básica

**🔍 Qual você prefere tentar primeiro?**