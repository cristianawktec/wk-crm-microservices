#!/bin/bash
echo "=== Getting fresh token ==="
TOKEN=$(curl -s http://localhost:8000/api/auth/test-customer | grep -o '"token":"[^"]*' | cut -d'"' -f4)
echo "Token: ${TOKEN:0:30}..."

echo ""
echo "=== Test 1: HTTPS external (api.consultoriawk.com) ==="
curl -s -w "\nHTTP Code: %{http_code}\n" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  "https://api.consultoriawk.com/api/trends/analyze?period=year" 2>&1 | head -40

echo ""
echo "=== Test 2: HTTP localhost ==="
curl -s -w "\nHTTP Code: %{http_code}\n" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  "http://localhost:8000/api/trends/analyze?period=year" 2>&1 | head -15
