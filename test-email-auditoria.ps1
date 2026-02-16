#!/usr/bin/env pwsh

$apiUrl = "http://localhost:8000"
$adminEmail = "admin@consultoriawk.com"
$adminPassword = "Admin@2025"

Write-Host "🔐 Fazendo login como admin..." -ForegroundColor Cyan

# Login
$loginResponse = curl -X POST "$apiUrl/api/auth/login" `
  -H "Content-Type: application/json" `
  -d @{
    email = $adminEmail
    password = $adminPassword
  } | ConvertFrom-Json

if (-not $loginResponse.success) {
    Write-Host "❌ Erro no login: $($loginResponse.message)" -ForegroundColor Red
    exit 1
}

$token = $loginResponse.data.token
Write-Host "✅ Login realizado!" -ForegroundColor Green
Write-Host "Token: $($token.Substring(0, 20))..." -ForegroundColor Gray

Write-Host ""
Write-Host "📧 Testando envio de email de auditoria..." -ForegroundColor Cyan

# Chamar endpoint de teste de email
$emailResponse = curl -X GET "$apiUrl/api/admin/login-audits/send-test-email" `
  -H "Authorization: Bearer $token" `
  -H "Content-Type: application/json" | ConvertFrom-Json

Write-Host ""
if ($emailResponse.success) {
    Write-Host "✅ Email enviado com SUCESSO!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📋 Detalhes:" -ForegroundColor Yellow
    Write-Host "   Destinatário: $($emailResponse.recipient)"
    Write-Host "   Registros: $($emailResponse.records_sent)"
    Write-Host "   Driver: $($emailResponse.mail_driver)"
    Write-Host "   Host: $($emailResponse.mail_host)"
    Write-Host ""
    Write-Host "📬 Verifique seu email em: $($emailResponse.recipient)" -ForegroundColor Cyan
} else {
    Write-Host "❌ Erro ao enviar email:" -ForegroundColor Red
    Write-Host "$($emailResponse.message)" -ForegroundColor Red
    Write-Host ""
    if ($emailResponse.debug_info) {
        Write-Host "🔍 Debug Info:" -ForegroundColor Yellow
        Write-Host "   Driver: $($emailResponse.debug_info.mail_driver)"
        Write-Host "   Host: $($emailResponse.debug_info.mail_host)"
        Write-Host "   Port: $($emailResponse.debug_info.mail_port)"
    }
}

Write-Host ""
Write-Host "💡 Se não recebeu o email, verifique:" -ForegroundColor Gray
Write-Host "   1. Spam/Lixo eletrônico"
Write-Host "   2. Arquivo de logs: storage/logs/laravel.log (se usando driver log)"
Write-Host "   3. Configuração de SMTP em .env"
