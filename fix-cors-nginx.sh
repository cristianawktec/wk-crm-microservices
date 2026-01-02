#!/bin/bash

# Fix CORS - Remove duplicate headers from Nginx
# Only Laravel CorsMiddleware should handle CORS

echo "🔧 Fixing CORS configuration..."

# Backup current nginx config
cp /etc/nginx/sites-available/api.consultoriawk.com /etc/nginx/sites-available/api.consultoriawk.com.bak

# Remove CORS headers from nginx (Laravel will handle it)
sed -i '/add_header.*Access-Control/d' /etc/nginx/sites-available/api.consultoriawk.com

# Test nginx configuration
nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Nginx config valid, reloading..."
    systemctl reload nginx
    echo "✅ CORS fix applied! Laravel CorsMiddleware now handles all CORS."
else
    echo "❌ Nginx config invalid, restoring backup..."
    cp /etc/nginx/sites-available/api.consultoriawk.com.bak /etc/nginx/sites-available/api.consultoriawk.com
    exit 1
fi
