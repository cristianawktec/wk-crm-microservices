# Script de Diagnóstico do Banco de Dados
# Verifica o estado real sem fazer alterações
# Executa: .\diagnostico-banco.ps1

$VPS_HOST = "root@72.60.254.100"
$LARAVEL_PATH = "/var/www/html/wk-crm-laravel"

Write-Host ""
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "  DIAGNÓSTICO DO BANCO DE DADOS" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "📋 Coletando informações..." -ForegroundColor Yellow
Write-Host ""

# Script para executar na VPS
$diagnosticScript = @"
cd $LARAVEL_PATH

echo '=== 1. CONFIGURAÇÃO DO .env ==='
echo ''
echo 'DB_CONNECTION:'
grep '^DB_CONNECTION' .env || echo 'Não encontrado'
echo ''
echo 'DB_HOST:'
grep '^DB_HOST' .env || echo 'Não encontrado'
echo ''
echo 'DB_PORT:'
grep '^DB_PORT' .env || echo 'Não encontrado'
echo ''
echo 'DB_DATABASE:'
grep '^DB_DATABASE' .env || echo 'Não encontrado'
echo ''
echo 'DB_USERNAME:'
grep '^DB_USERNAME' .env || echo 'Não encontrado'
echo ''

echo '=== 2. TESTE DE CONEXÃO COM O BANCO ==='
echo ''
php artisan tinker --execute="try { \DB::connection()->getPdo(); echo 'CONEXÃO: OK\n'; } catch(\Exception \$e) { echo 'CONEXÃO: FALHOU - ' . \$e->getMessage() . '\n'; }"
echo ''

echo '=== 3. CONTAGEM DE REGISTROS ==='
echo ''
php artisan tinker --execute="try { echo 'Usuários: ' . \App\Models\User::count() . '\n'; } catch(\Exception \$e) { echo 'Erro ao contar usuários: ' . \$e->getMessage() . '\n'; }"
php artisan tinker --execute="try { echo 'Oportunidades: ' . \App\Models\Opportunity::count() . '\n'; } catch(\Exception \$e) { echo 'Erro ao contar oportunidades: ' . \$e->getMessage() . '\n'; }"
php artisan tinker --execute="try { echo 'Notificações: ' . \App\Models\Notification::count() . '\n'; } catch(\Exception \$e) { echo 'Erro ao contar notificações: ' . \$e->getMessage() . '\n'; }"
echo ''

echo '=== 4. STATUS DAS MIGRATIONS ==='
echo ''
php artisan migrate:status 2>&1 | head -20
echo ''

echo '=== 5. VERIFICAR SE POSTGRESQL ESTÁ RODANDO ==='
echo ''
ps aux | grep postgres | grep -v grep || echo 'PostgreSQL não está rodando'
echo ''

echo '=== 6. PORTAS EM USO (PostgreSQL geralmente usa 5432) ==='
echo ''
netstat -tuln | grep 5432 || echo 'Porta 5432 não está em uso'
echo ''
"@

Write-Host "Executando diagnóstico na VPS..." -ForegroundColor Cyan
Write-Host ""

# Executar diagnóstico
$result = ssh $VPS_HOST "$diagnosticScript"

Write-Host $result

Write-Host ""
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "  ANÁLISE DOS RESULTADOS" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

# Análise baseada no resultado
if ($result -like "*CONEXÃO: OK*") {
    Write-Host "✅ BANCO DE DADOS ESTÁ CONECTADO!" -ForegroundColor Green
    Write-Host ""
    
    if ($result -like "*Usuários: 0*") {
        Write-Host "⚠️  BANCO ESTÁ VAZIO - Precisa popular" -ForegroundColor Yellow
    } else {
        Write-Host "✅ BANCO TEM DADOS!" -ForegroundColor Green
        Write-Host ""
        Write-Host "📊 Resumo:" -ForegroundColor Cyan
        $result | Select-String "Usuários:|Oportunidades:|Notificações:" | ForEach-Object { 
            Write-Host "   $_" -ForegroundColor White
        }
    }
} else {
    Write-Host "❌ BANCO NÃO ESTÁ CONECTADO" -ForegroundColor Red
    Write-Host ""
    Write-Host "Possíveis causas:" -ForegroundColor Yellow
    Write-Host "  1. DB_HOST incorreto (provavelmente está 'postgres' e deveria ser 'localhost')" -ForegroundColor Gray
    Write-Host "  2. PostgreSQL não está rodando" -ForegroundColor Gray
    Write-Host "  3. Credenciais incorretas no .env" -ForegroundColor Gray
    Write-Host "  4. Porta 5432 não está acessível" -ForegroundColor Gray
}

Write-Host ""
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "  PRÓXIMOS PASSOS" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

if ($result -like "*could not translate host name*") {
    Write-Host "🔧 CORREÇÃO NECESSÁRIA:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Execute estes comandos na VPS:" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "  cd /var/www/html/wk-crm-laravel" -ForegroundColor Gray
    Write-Host "  nano .env" -ForegroundColor Gray
    Write-Host ""
    Write-Host "Altere a linha:" -ForegroundColor Yellow
    Write-Host "  DB_HOST=postgres" -ForegroundColor Red
    Write-Host ""
    Write-Host "Para:" -ForegroundColor Yellow
    Write-Host "  DB_HOST=localhost" -ForegroundColor Green
    Write-Host ""
    Write-Host "Depois execute:" -ForegroundColor Cyan
    Write-Host "  php artisan config:clear" -ForegroundColor Gray
    Write-Host "  php artisan config:cache" -ForegroundColor Gray
    Write-Host ""
}

Write-Host "💾 Relatório salvo em: diagnostico-resultado.txt" -ForegroundColor Green
$result | Out-File -FilePath "diagnostico-resultado.txt"
