#!/bin/bash

# Get login token
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@consultoriawk.com","password":"admin123"}' \
  | jq -r '.token // empty')

if [ -z "$TOKEN" ]; then
  echo "❌ Failed to get token"
  exit 1
fi

echo "✓ Token: ${TOKEN:0:20}..."

# Get notifications
echo ""
echo "=== GET /notifications ==="
NOTIF=$(curl -s -X GET http://localhost:8000/api/notifications \
  -H "Authorization: Bearer $TOKEN" \
  | jq '.data[0] // empty')

if [ -z "$NOTIF" ]; then
  echo "❌ No notifications found"
  exit 1
fi

NOTIF_ID=$(echo "$NOTIF" | jq -r '.id')
echo "✓ Found notification: $NOTIF_ID"

# Try to delete
echo ""
echo "=== DELETE /notifications/$NOTIF_ID ==="
DELETE_RESPONSE=$(curl -s -X DELETE http://localhost:8000/api/notifications/$NOTIF_ID \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -w "\n%{http_code}")

HTTP_CODE=$(echo "$DELETE_RESPONSE" | tail -n 1)
BODY=$(echo "$DELETE_RESPONSE" | head -n -1)

echo "HTTP Status: $HTTP_CODE"
echo "Response:"
echo "$BODY" | jq .
