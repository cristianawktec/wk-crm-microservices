#!/bin/bash
# Adicionar headers CORS no nginx da API

CONFIG_FILE="/etc/nginx/sites-enabled/api.consultoriawk.com"

# Backup do arquivo original
cp $CONFIG_FILE ${CONFIG_FILE}.backup

# Adicionar headers CORS no bloco location /
cat > $CONFIG_FILE << 'EOF'
server {
    listen 443 ssl http2;
    server_name api.consultoriawk.com;

    ssl_certificate /etc/letsencrypt/live/api.consultoriawk.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.consultoriawk.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    access_log /var/log/nginx/api.consultoriawk.com.access.log;
    error_log /var/log/nginx/api.consultoriawk.com.error.log;

    location /ai/ {
        proxy_pass http://localhost:8001/;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # CORS headers
        add_header 'Access-Control-Allow-Origin' 'https://app.consultoriawk.com' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS' always;
        add_header 'Access-Control-Allow-Headers' 'Authorization,Content-Type' always;
    }

    location /dashboard {
        alias /var/www/wk-ai-service-test;
        index dashboard.html;
        try_files $uri $uri/ /dashboard.html;
    }

    location / {
        proxy_pass http://localhost:8000;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # CORS headers  
        add_header 'Access-Control-Allow-Origin' 'https://app.consultoriawk.com' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, PATCH, OPTIONS' always;
        add_header 'Access-Control-Allow-Headers' 'Authorization,Content-Type,X-Requested-With' always;
        add_header 'Access-Control-Allow-Credentials' 'true' always;
        
        # Handle OPTIONS preflight
        if ($request_method = OPTIONS) {
            return 204;
        }
    }
}

server {
    listen 80;
    server_name api.consultoriawk.com;
    return 301 https://$server_name$request_uri;
}
EOF

# Testar configuração
nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Configuração válida, recarregando nginx..."
    systemctl reload nginx
    echo "✅ CORS configurado com sucesso!"
else
    echo "❌ Erro na configuração, restaurando backup..."
    mv ${CONFIG_FILE}.backup $CONFIG_FILE
    exit 1
fi
