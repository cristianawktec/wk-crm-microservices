# Configuração de Secrets do GitHub - Guia Passo a Passo

## ⚠️ AÇÃO NECESSÁRIA: Configurar Secrets Manualmente

O GitHub CLI (`gh`) não está instalado. Siga os passos abaixo para configurar os secrets via interface web:

### 1. Acesse as configurações do repositório
1. Vá para: https://github.com/cristianawktec/wk-crm-microservices
2. Clique em **Settings** (aba no topo)
3. No menu lateral esquerdo, clique em **Secrets and variables** → **Actions**
4. Clique no botão **New repository secret**

### 2. Adicione os 3 secrets necessários

#### Secret 1: VPS_HOST
- **Name**: `VPS_HOST`
- **Secret**: `72.60.254.100`
- Clique em **Add secret**

#### Secret 2: VPS_USER
- **Name**: `VPS_USER`
- **Secret**: `root`
- Clique em **Add secret**

#### Secret 3: VPS_PASSWORD
- **Name**: `VPS_PASSWORD`
- **Secret**: `[DIGITE A SENHA SSH DO ROOT AQUI]`
- Clique em **Add secret**

### 3. Verificar configuração
Após adicionar os 3 secrets, você deverá ver na lista:
- ✅ VPS_HOST
- ✅ VPS_USER
- ✅ VPS_PASSWORD

### 4. Testar o pipeline
Faça um commit/push para a branch `main`:
```bash
cd C:\xampp\htdocs\crm
git add .
git commit -m "feat: add CI/CD deploy pipeline"
git push origin main
```

Acompanhe o deploy em:
https://github.com/cristianawktec/wk-crm-microservices/actions

---

## ✅ Já configurado automaticamente

- ✅ Script de rollback (`/opt/wk-crm/scripts/rollback-deploy.sh`) instalado na VPS
- ✅ Permissões de execução configuradas
- ✅ `docker-compose.yml` atualizado para usar `image: wk-crm-laravel:latest`
- ✅ Backup criado em `docker-compose.yml.backup`

## Próximo passo após configurar secrets

Após adicionar os secrets, o próximo push para `main` irá:
1. ✅ Executar testes (workflow `laravel-tests.yml`)
2. ✅ Build da imagem Docker
3. ✅ Deploy automático para a VPS
4. ✅ Migrations e health check
5. ✅ Rollback automático em caso de falha

## Rollback manual (se necessário)

```bash
ssh root@72.60.254.100
cd /opt/wk-crm
bash scripts/rollback-deploy.sh
```
