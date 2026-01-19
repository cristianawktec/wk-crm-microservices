#!/bin/bash
# Show all admin users
docker exec wk_postgres psql -U wk_user -d wk_main -c "SELECT id, name, email FROM public.users WHERE name LIKE '%admin%' OR email LIKE '%admin%';"
