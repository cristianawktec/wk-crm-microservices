#!/usr/bin/env bash
# Simple deploy script (linux/mac) to copy public/docs to the VPS

set -euo pipefail

VPS_USER=${1:-root}
VPS_HOST=${2:-}
REMOTE_PATH=${3:-/opt/wk-crm/wk-crm-laravel/public}

if [ -z "$VPS_HOST" ]; then
  echo "Usage: $0 <vps_user> <vps_host> [remote_path]"
  exit 1
fi

ROOT_DIR=$(dirname "$(dirname "${BASH_SOURCE[0]}")")
DOCS_DIR="$ROOT_DIR/wk-crm-laravel/public/docs"

if [ ! -d "$DOCS_DIR" ]; then
  echo "Docs dir not found: $DOCS_DIR"
  exit 1
fi

TMP_ARCHIVE="/tmp/wkcrmdocs_$(date +%s).tgz"
tar -czf "$TMP_ARCHIVE" -C "$(dirname "$DOCS_DIR")" "$(basename "$DOCS_DIR")"

echo "Uploading $TMP_ARCHIVE to $VPS_USER@$VPS_HOST:/tmp/"
scp "$TMP_ARCHIVE" "$VPS_USER@$VPS_HOST:/tmp/"

echo "Extracting on VPS into $REMOTE_PATH"
ssh "$VPS_USER@$VPS_HOST" "mkdir -p $REMOTE_PATH && tar -xzf /tmp/$(basename "$TMP_ARCHIVE") -C $REMOTE_PATH && sudo chown -R www-data:www-data $REMOTE_PATH/docs && sudo chmod -R 755 $REMOTE_PATH/docs && sudo nginx -t && sudo systemctl reload nginx"

echo "Done. Access: https://api.consultoriawk.com/docs/index.html"
