#!/bin/bash
# Check opportunities and notifications in database

echo "=== OPORTUNIDADES ==="
docker exec wk_postgres psql -U wk_user -d wk_main -t -A -F'|' << 'EOF'
SELECT id, title FROM opportunities ORDER BY created_at DESC LIMIT 5;
EOF

echo ""
echo "=== NOTIFICAÇÕES (últimas 5) ==="
docker exec wk_postgres psql -U wk_user -d wk_main -t -A -F'|' << 'EOF'
SELECT id, action_url, data->>'opportunity_id' as opp_id FROM notifications ORDER BY created_at DESC LIMIT 5;
EOF

echo ""
echo "=== NOTIFICAÇÕES COM OPORTUNIDADES INVÁLIDAS ==="
docker exec wk_postgres psql -U wk_user -d wk_main -t -A -F'|' << 'EOF'
SELECT n.id, n.action_url, n.data->>'opportunity_id' as opp_id
FROM notifications n
WHERE NOT EXISTS (SELECT 1 FROM opportunities o WHERE o.id = n.data->>'opportunity_id')
LIMIT 10;
EOF
