#!/usr/bin/env bash
# Quick verification script for the CORS/route fixes applied on 2025-12-05
# Usage: ssh into the server or run from a shell that can reach the api host

API_HOST="https://api.consultoriawk.com"

echo "==> OPTIONS /api/sellers (preflight)"
curl -sS -D - -o /dev/null -X OPTIONS "$API_HOST/api/sellers" -H "Origin: https://admin.consultoriawk.com" -H "Access-Control-Request-Method: GET"

echo "\n==> GET /api/sellers"
curl -sS -D - -o /dev/null "$API_HOST/api/sellers"

echo "\n==> GET /api/leads/sources"
curl -sS -D - -o /dev/null "$API_HOST/api/leads/sources"

# Try Laravel's test runner if available (artisan test or phpunit)
APP_DIR="wk-crm-laravel"
if [ -d "$APP_DIR" ]; then
  echo "\n==> Running Laravel feature tests (TestCase: LeadsRoutesTest)"
  if command -v docker >/dev/null 2>&1 && [ -f docker-compose.yml ]; then
    echo "Running inside docker-compose (attempting to exec app container)..."
    # Note: service name might differ on your compose file; change `app` to the Laravel service name if needed
    docker compose exec -T app php artisan test --filter=LeadsRoutesTest || docker compose exec -T app ./vendor/bin/phpunit --testsuite=Feature --filter=LeadsRoutesTest
  else
    (cd "$APP_DIR" && php artisan test --filter=LeadsRoutesTest) || (cd "$APP_DIR" && ./vendor/bin/phpunit tests/Feature/LeadsRoutesTest.php)
  fi
else
  echo "App directory '$APP_DIR' not found; skipping phpunit/artisan test run."
fi

exit 0
