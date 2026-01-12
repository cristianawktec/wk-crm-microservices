# Check existing users in database
$password = "6y6-@Qw88-b)"
$securePassword = ConvertTo-SecureString $password -AsPlainText -Force

Write-Host "Checking existing users..." -ForegroundColor Cyan

echo $password | ssh root@72.60.254.100 @'
echo "=== Existing Users ==="
PGPASSWORD=secure_password_123 psql -h localhost -p 5433 -U wk_user -d wk_main -c "SELECT id, email, name, created_at FROM users ORDER BY id;"
'@
