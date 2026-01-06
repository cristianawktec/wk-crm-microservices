#!/bin/bash

# Script to upload api.php to VPS
scp -o "StrictHostKeyChecking=no" \
    "c:/xampp/htdocs/crm/wk-crm-laravel/routes/api.php" \
    root@srv1057865:/var/www/html/wk-crm-laravel/routes/api.php

echo "Upload complete. Clearing caches..."

ssh -o "StrictHostKeyChecking=no" root@srv1057865 \
    "cd /var/www/html/wk-crm-laravel && \
     php artisan route:clear && \
     php artisan config:clear && \
     php artisan route:cache && \
     php artisan route:list | grep trends"
