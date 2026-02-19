#!/bin/bash

echo "═══════════════════════════════════════════"
echo "  Testing Login Audit Email on VPS"
echo "═══════════════════════════════════════════"

# Execute the test command inside the VPS Laravel container
docker exec wk_crm_laravel php artisan email:test-login-audit

echo ""
echo "═══════════════════════════════════════════"
echo "  Test completed. Check the output above."
echo "═══════════════════════════════════════════"
