#!/usr/bin/env bash
set -euo pipefail

# Restore opportunities data into Postgres running under docker-compose
# Usage: run on the VPS where /opt/wk-crm is the project root
#   bash ./scripts/restore_opportunities.sh /opt/wk-crm/wk-admin-frontend/opportunities.dump
# If no argument is given, tries default path under project root.

PROJECT_ROOT=${PROJECT_ROOT:-/opt/wk-crm}
DUMP_PATH=${1:-$PROJECT_ROOT/wk-admin-frontend/opportunities.dump}
DB_NAME=${DB_NAME:-wk_main}
DB_USER=${DB_USER:-wk_user}
SERVICE_NAME=${SERVICE_NAME:-postgres}

if [ ! -f "$DUMP_PATH" ]; then
  echo "Dump file not found: $DUMP_PATH"
  exit 1
fi

echo "==> Checking Postgres service and database"
docker compose -f "$PROJECT_ROOT/docker-compose.yml" ps "$SERVICE_NAME"

echo "==> Creating temporary directory inside Postgres container"
TMP_DIR=/tmp/restore_$(date +%s)
docker compose -f "$PROJECT_ROOT/docker-compose.yml" exec -T "$SERVICE_NAME" sh -c "mkdir -p $TMP_DIR"

echo "==> Copying dump into container"
# Use docker cp via container name; derive container from compose ps
CONTAINER=$(docker compose -f "$PROJECT_ROOT/docker-compose.yml" ps -q "$SERVICE_NAME")
docker cp "$DUMP_PATH" "$CONTAINER":"$TMP_DIR/opportunities.dump"

echo "==> Restoring using pg_restore (custom format) or psql (plain)"
RESTORE_CMD="pg_restore -U $DB_USER -d $DB_NAME $TMP_DIR/opportunities.dump || psql -U $DB_USER -d $DB_NAME -f $TMP_DIR/opportunities.dump"
docker compose -f "$PROJECT_ROOT/docker-compose.yml" exec -T "$SERVICE_NAME" sh -c "$RESTORE_CMD"

echo "==> Validating counts"
docker compose -f "$PROJECT_ROOT/docker-compose.yml" exec -T "$SERVICE_NAME" psql -U "$DB_USER" -d "$DB_NAME" -c "SELECT COUNT(*) AS opportunities_count, COALESCE(SUM(value),0) AS total_value FROM opportunities;"

echo "==> Cleaning up"
docker compose -f "$PROJECT_ROOT/docker-compose.yml" exec -T "$SERVICE_NAME" sh -c "rm -rf $TMP_DIR"

echo "Restore complete."