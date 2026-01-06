#!/bin/bash
cd /var/www/html/wk-crm-laravel

# Get token
TOKEN=$(curl -s http://localhost:8000/api/auth/test-customer 2>/dev/null | grep -o '"token":"[^"]*' | cut -d'"' -f4)

echo "✅ Token obtained: ${TOKEN:0:20}..."

# Test trends endpoint
echo "Testing /api/trends/analyze with token..."
curl -s -i -H "Authorization: Bearer $TOKEN" "http://localhost:8000/api/trends/analyze?period=year" 2>&1 | head -30
