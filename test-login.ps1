$body = @{
    email = "admin@consultoriawk.com"
    password = "Admin@123456"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/login" -Method Post -Body $body -ContentType "application/json"

Write-Host "Response:"
$response | ConvertTo-Json -Depth 10
