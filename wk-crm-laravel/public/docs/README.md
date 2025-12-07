# WK CRM — API Docs (Swagger UI)

Este diretório contém a documentação OpenAPI e a Swagger UI estática para a API WK CRM.

Arquivos principais
- `openapi.yaml` — especificação OpenAPI da API (Customers, Leads, Opportunities).
- `index.html` — Swagger UI (consome `openapi.yaml`) pronta para servir via Nginx.

Objetivo
- Permitir que a equipe frontend/QA consuma e teste os endpoints usando uma interface Swagger sem necessidade de instalar dependências locais.

Como publicar no VPS
1. No host local, compacte os arquivos:

```pwsh
cd wk-crm-laravel/public
tar -czf docs.tgz docs
```

2. Copie para o VPS (exemplo usando `scp`):

```pwsh
scp docs.tgz root@<VPS_IP>:/tmp
```

3. No VPS, extraia e mova para o `public` do Laravel (exemplo assume app em `/opt/wk-crm/wk-crm-laravel`):

```bash
ssh root@<VPS_IP>
cd /opt/wk-crm/wk-crm-laravel/public
tar -xzf /tmp/docs.tgz
sudo chown -R www-data:www-data docs
sudo chmod -R 755 docs
sudo nginx -t && sudo systemctl reload nginx
```

4. Acesse a UI:

- `https://api.consultoriawk.com/docs/index.html` (ou `https://admin.consultoriawk.com/docs/index.html`) — dependendo de como o Nginx está configurado.

Verificações pós-publicação
- Acesse `/docs/index.html` e confira que o select `Servers` aponta para `https://api.consultoriawk.com/api`.
- Teste um endpoint (ex.: `GET /customers`) através da UI.

Rollback
- Se algo falhar, restaure a pasta `public/docs` a partir do backup criado antes do deploy, ou remova os arquivos e recarregue o Nginx.

Próximos passos recomendados (Fase 2)
- Implementar/validar os endpoints CRUD (caso seja necessário complementar):
  - `POST /api/customers`
  - `GET /api/customers`
  - `POST /api/leads`
  - `GET /api/opportunities`
- Configurar Laravel Sanctum para autenticação e proteger endpoints que exigem autenticação.
- Rodar os testes automatizados e adicionar um job de CI que execute `php artisan test` em PRs.

Como rodar testes localmente (dev)

```pwsh
cd wk-crm-laravel
# executar phpunit/php artisan test localmente (garanta .env e DB configurados)
php artisan test
```

Observações
- O arquivo `openapi.yaml` dentro de `public/docs` foi ajustado para apontar para `https://api.consultoriawk.com/api` por padrão. Se for necessário outro servidor (staging/local), edite o `servers` no YAML.
- Se preferir uma integração mais completa, podemos adicionar um endpoint Laravel que sirva a UI (pacote `swagger-ui` ou `scribe`) e automatizar a geração do YAML durante deploy.

Contato / decisão
- Quer que eu gere o patch/PR com esses arquivos e instruções de deploy automático? Responda `deploy-docs`.
- Quer que eu inicie a Fase 2 (CRUDs + Sanctum + testes)? Responda `2`.

---
Arquivo criado automaticamente por script de documentação do projeto.
