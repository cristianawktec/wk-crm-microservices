@echo off
REM WK CRM Brasil - Habilitador Docker Desktop
REM Script para resolver problemas de Virtual Machine Platform

echo ========================================
echo   WK CRM Brasil - Docker Desktop Fix
echo ========================================
echo.

echo 🔧 Verificando privilégios de administrador...
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Este script precisa ser executado como ADMINISTRADOR!
    echo.
    echo Clique com botao direito no arquivo e selecione:
    echo "Executar como administrador"
    echo.
    pause
    exit /b 1
)

echo ✅ Privilégios OK!
echo.

echo 🔧 Habilitando Virtual Machine Platform...
dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart

echo.
echo 🔧 Habilitando Windows Subsystem for Linux...
dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart

echo.
echo 🔧 Habilitando Hyper-V (se disponível)...
dism.exe /online /enable-feature /featurename:Microsoft-Hyper-V-All /all /norestart

echo.
echo ✅ Recursos habilitados!
echo.
echo ⚠️ IMPORTANTE:
echo 1. REINICIE o computador agora
echo 2. Após reiniciar, abra o Docker Desktop novamente
echo 3. Execute o script start-quick.bat
echo.

echo Deseja reiniciar agora? (S/N)
set /p choice=
if /i "%choice%"=="S" (
    echo 🔄 Reiniciando em 10 segundos...
    shutdown /r /t 10
) else (
    echo ⚠️ Lembre-se de reiniciar manualmente!
    pause
)