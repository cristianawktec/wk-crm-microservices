# Restart Docker Containers
Write-Host "=== Iniciando Containers ===" -ForegroundColor Green

# Mudar para o diretório
Set-Location c:\xampp\htdocs\wk-crm-microservices

# Down dos containers antigos
Write-Host "Removendo containers antigos..."
docker-compose down --remove-orphans

# Aguardar um pouco
Start-Sleep -Seconds 3

# Up dos containers
Write-Host "Iniciando containers..."
docker-compose up -d

# Aguardar um pouco para inicialização
Start-Sleep -Seconds 5

# Verificar status
Write-Host "`n=== Status dos Containers ===" -ForegroundColor Green
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

Write-Host "`n=== Logs do AI Service ===" -ForegroundColor Green
docker logs wk_ai_service --tail 10

Write-Host "`nContainers iniciados com sucesso!" -ForegroundColor Green
