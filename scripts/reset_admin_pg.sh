#!/usr/bin/env bash
set -euo pipefail

EMAIL="admin@consultoriawk.com"
NEW_PASS="Admin@2025"
DB_NAME="wk_main"
DB_USER="wk_user"

# Generate bcrypt hash using PHP inside the Laravel container
HASH=$(docker exec wk_crm_laravel php -r 'echo password_hash("'"${NEW_PASS}"'", PASSWORD_BCRYPT);')

if [[ -z "$HASH" ]]; then
  echo "Failed to generate bcrypt hash" >&2
  exit 1
fi

echo "Updating password for ${EMAIL}..."
docker exec -i wk_postgres psql -U "$DB_USER" -d "$DB_NAME" -c "UPDATE users SET password='${HASH}' WHERE email='${EMAIL}';"

echo "Password updated for ${EMAIL}."