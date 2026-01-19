#!/bin/bash
docker exec wk_postgres psql -U wk_user -d wk_main -c "SELECT id, email, name FROM users LIMIT 10;"
