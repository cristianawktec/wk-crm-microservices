# CI/CD Deploy - Configuração

## Secrets necessários no GitHub

Configure em: `Settings > Secrets and variables > Actions > New repository secret`

- `VPS_HOST`: IP da VPS (ex: `72.60.254.100`)
- `VPS_USER`: Usuário SSH (ex: `root`)
- `VPS_PASSWORD`: Senha SSH do usuário

## Workflow de Deploy

**Arquivo**: `.github/workflows/deploy-to-vps.yml`

**Trigger**: Push para branch `main`

**Etapas**:
1. Build da imagem Docker do Laravel (`wk-crm-laravel:SHA` + `latest`)
2. Compressão e envio via SCP para `/tmp/` na VPS
3. Load da imagem no Docker da VPS
4. Atualização do `docker-compose.yml` com novo SHA
5. Restart do container `wk-crm-laravel`
6. Migrations (`php artisan migrate --force`)
7. Cache de config/routes
8. Health check em `http://localhost:8000/api/health`
9. Rollback automático se health check falhar
10. Limpeza de imagens antigas

## Rollback Manual

Se o deploy falhar e precisar reverter:

```bash
ssh root@72.60.254.100
cd /opt/wk-crm
bash /opt/wk-crm/scripts/rollback-deploy.sh
```

O script:
- Lista últimas 5 imagens Docker
- Solicita SHA da versão para rollback
- Atualiza `docker-compose.yml`
- Reinicia container
- Valida com health check

## Verificação pós-deploy

```bash
# Health check
curl -i https://api.consultoriawk.com/api/health

# Logs do container
ssh root@72.60.254.100 "cd /opt/wk-crm && docker-compose logs -f wk-crm-laravel"

# Status dos containers
ssh root@72.60.254.100 "cd /opt/wk-crm && docker-compose ps"
```

## Melhorias futuras

- [ ] Usar SSH keys em vez de senha (mais seguro)
- [ ] Registry Docker privado (evitar transferir imagem via SCP)
- [ ] Blue-green deployment
- [ ] Notificações Slack/Discord
- [ ] Backup automático antes do deploy
- [ ] Smoke tests pós-deploy
