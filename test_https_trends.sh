#!/bin/bash
# Test trends endpoint via HTTPS
TOKEN=$(curl -s http://localhost:8000/api/auth/test-customer | grep -o '"token":"[^"]*' | cut -d'"' -f4)

echo "Testing https://api.consultoriawk.com/api/trends/analyze"
curl -s -i -H "Authorization: Bearer $TOKEN" "https://api.consultoriawk.com/api/trends/analyze?period=year" 2>&1 | head -30
