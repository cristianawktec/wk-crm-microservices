#!/bin/bash
# Update admin password in VPS database
docker exec wk_postgres psql -U laravel -d laravel <<SQL
UPDATE users SET password = '\$2y\$10\$xiAQ7ZESKJ6QHSaxJxuGsOiIeyR/Gl6f2ewcrgC17iLmmoD0un04y' WHERE email = 'admin@consultoriawk.com';
SELECT id, name, email FROM users WHERE email = 'admin@consultoriawk.com';
SQL
