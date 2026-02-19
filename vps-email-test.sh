#!/usr/bin/env bash

# VPS Email Test Script
# Usage: bash vps-email-test.sh <PASSWORD>

PASSWORD="${1:-cris1tian#}"
VPS_USER="root"
VPS_HOST="72.60.254.100"
VPS_PATH="/opt/wk-crm"

echo "╔════════════════════════════════════════════════════════╗"
echo "║         Testing Login Audit Email on VPS               ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""

# Build the command to execute on VPS
VPS_COMMAND="cd $VPS_PATH/wk-crm-laravel && \
  docker exec wk_crm_laravel php artisan config:cache && \
  docker exec wk_crm_laravel php artisan email:test-login-audit"

# Try to execute via SSH (requires sshpass or SSH key)
echo "📡 Connecting to VPS..."

# Method 1: Try direct SSH (if key-based auth is set up)
ssh "$VPS_USER@$VPS_HOST" "$VPS_COMMAND" 2>/dev/null

if [ $? -ne 0 ]; then
    echo "❌ Direct SSH failed. Trying with password..."
    # Method 2: Use sshpass if available
    if command -v sshpass &> /dev/null; then
        sshpass -p "$PASSWORD" ssh "$VPS_USER@$VPS_HOST" "$VPS_COMMAND"
    else
        echo "❌ sshpass not available and SSH key not configured."
        echo "💡 Set up SSH key or install sshpass for non-interactive authentication."
        exit 1
    fi
fi

echo ""
echo "✅ Test completed!"
