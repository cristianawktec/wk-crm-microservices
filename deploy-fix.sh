#!/bin/bash
cd /root/wk-crm-microservices
git pull origin main
docker compose build --no-cache --progress=plain 2>&1 | grep -E "Step|ERROR|Successfully"
docker compose up -d
echo "Deployment complete. Testing endpoint..."
sleep 5
curl -s https://api.consultoriawk.com/api/auth/test-customer?role=admin | python3 -m json.tool 2>/dev/null || echo "Endpoint not responding yet"
