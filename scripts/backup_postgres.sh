#!/usr/bin/env bash
# Simple Postgres backup using the running docker container
set -euo pipefail

# Change these if you adjust compose names or database settings
CONTAINER_NAME="wk_postgres"
BACKUP_DIR="/opt/wk-crm/backups"
DB_NAME="${POSTGRES_DB:-wk_main}"
DB_USER="${POSTGRES_USER:-wk_user}"

mkdir -p "${BACKUP_DIR}"
STAMP=$(date +"%Y%m%d-%H%M%S")
OUT_FILE="${BACKUP_DIR}/db-${DB_NAME}-${STAMP}.sql.gz"

# Uses pg_dump inside the container to avoid exposing credentials on host
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
  echo "Container ${CONTAINER_NAME} not running" >&2
  exit 1
fi

echo "Backing up ${DB_NAME} from ${CONTAINER_NAME} to ${OUT_FILE}"
docker exec "${CONTAINER_NAME}" sh -c "pg_dump -U ${DB_USER} ${DB_NAME} | gzip" > "${OUT_FILE}"

echo "Done. Stored at ${OUT_FILE}"
