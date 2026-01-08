# Database Recovery Guide (Postgres)

This guide helps diagnose data loss and restore data on the VPS running the WK CRM stack.

## Quick Diagnosis

Run the bundled diagnostics script on the server:

```
ssh root@<SERVER_IP> "bash -lc '/opt/wk-crm/scripts/collect_db_diagnostics.sh'"
```

Review:
- Migrations list
- Opportunities count and total
- Laravel and Postgres logs

## Check Volume State

```
ssh root@<SERVER_IP> "docker volume ls"
ssh root@<SERVER_IP> "docker volume inspect <volume_name> | jq '.[0].Mountpoint'"
```

If the `postgres_data` volume was pruned or recreated, data may be lost.

## Restore From Dump

If you have `wk-admin-frontend/opportunities.dump` or another backup:

```
ssh root@<SERVER_IP> "bash -lc '/opt/wk-crm/scripts/restore_opportunities.sh'"
```

The script tries `pg_restore` (custom format) and falls back to `psql` for plain SQL dumps.

## Seed Minimal Data (Fallback)

If no backup is available, run Laravel seeders to repopulate demo data:

```
ssh root@<SERVER_IP> "docker compose -f /opt/wk-crm/docker-compose.yml exec -T wk_crm_laravel php artisan migrate --force"
ssh root@<SERVER_IP> "docker compose -f /opt/wk-crm/docker-compose.yml exec -T wk_crm_laravel php artisan db:seed --force"
```

## Prevent Future Data Loss

- Avoid `docker system prune -a` without checking named volumes.
- Back up Postgres regularly:

```
ssh root@<SERVER_IP> "docker compose -f /opt/wk-crm/docker-compose.yml exec -T postgres pg_dump -U wk_user -d wk_main -Fc -f /var/lib/postgresql/data/backup_$(date +%F).dump"
```

- Store dumps in a persistent path and copy them off the server periodically.
