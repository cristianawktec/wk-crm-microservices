#!/bin/bash
echo "=== Simulating browser request ==="
TOKEN="1A|ZbWeKczaCrGuzb1eHue"  # Token from screenshot

echo "Test with Authorization header:"
curl -v \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Origin: https://app.consultoriawk.com" \
  "https://api.consultoriawk.com/api/trends/analyze?period=year" 2>&1 | grep -E "< HTTP|< 404|< 200|< Access-Control|authorization"

echo ""
echo "=== Checking route cache ==="
cd /var/www/html/wk-crm-laravel
php artisan route:list --path=trends --columns=Method,URI,Name,Action
