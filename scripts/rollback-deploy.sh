#!/bin/bash
# Script de rollback manual para reverter deploy com falha

set -e

echo "🔄 Iniciando rollback do deploy..."

cd /opt/wk-crm

# Lista últimas 5 imagens
echo "Imagens disponíveis:"
docker images wk-crm-laravel --format "{{.Tag}}" | head -5

read -p "Digite o SHA da imagem para rollback: " ROLLBACK_SHA

if [ -z "$ROLLBACK_SHA" ]; then
  echo "❌ SHA não informado, abortando."
  exit 1
fi

# Backup do estado atual
docker-compose ps > /tmp/rollback-backup-$(date +%s).txt

# Atualiza docker-compose
sed -i "s/image: wk-crm-laravel:.*/image: wk-crm-laravel:$ROLLBACK_SHA/" docker-compose.yml

# Restart com imagem anterior
docker-compose up -d --no-deps wk-crm-laravel

# Health check
sleep 5
if curl -f http://localhost:8000/api/health > /dev/null 2>&1; then
  echo "✅ Rollback executado com sucesso!"
  docker-compose exec wk-crm-laravel php artisan config:cache
  docker-compose exec wk-crm-laravel php artisan route:cache
else
  echo "⚠️ Health check falhou após rollback. Verifique logs: docker-compose logs wk-crm-laravel"
  exit 1
fi
