# 🚀 WK CRM Brasil - Soluções para Docker Desktop

## 🔴 Problema Atual: Virtual Machine Platform não habilitado

### 🛠️ Solução 1: Script Automático (RECOMENDADO)

1. **Abra PowerShell como ADMINISTRADOR**
   ```powershell
   # Clique com botão direito no PowerShell
   # Selecione "Executar como administrador"
   ```

2. **Execute o script de correção:**
   ```powershell
   cd C:\xampp\htdocs\crm
   .\fix-docker.ps1
   ```

3. **Reinicie o computador** quando solicitado

4. **Após reiniciar**, abra Docker Desktop e teste:
   ```powershell
   docker --version
   .\start-quick.bat
   ```

---

### 🛠️ Solução 2: Manual (Windows Settings)

1. **Abrir Recursos do Windows:**
   - Pressione `Win + R`
   - Digite: `optionalfeatures`
   - Pressione Enter

2. **Habilitar recursos:**
   - ☑️ **Virtual Machine Platform**
   - ☑️ **Windows Subsystem for Linux**
   - ☑️ **Hyper-V** (se disponível)

3. **Reiniciar** o computador

4. **Instalar WSL2:**
   ```powershell
   wsl --install
   wsl --set-default-version 2
   ```

---

### 🛠️ Solução 3: Via Linha de Comando

```powershell
# Como ADMINISTRADOR
dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart
dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart
dism.exe /online /enable-feature /featurename:Microsoft-Hyper-V-All /all /norestart

# Reiniciar
shutdown /r /t 0
```

---

## 🟡 Alternativa: Usar XAMPP (Sem Docker)

**Se o Docker continuar com problemas, use o XAMPP:**

1. **Inicie XAMPP Control Panel**
   - Ligue Apache ✅
   - Ligue MySQL ✅

2. **Execute o modo XAMPP:**
   ```cmd
   cd C:\xampp\htdocs\crm
   .\start-xampp.bat
   ```

3. **Acesse:**
   - API: http://localhost:8001
   - Health: http://localhost:8001/api/health
   - Dashboard: http://localhost:8001/api/dashboard

---

## 🔍 Diagnóstico de Problemas

### Verificar Virtualização na BIOS
```
1. Reinicie o PC
2. Entre na BIOS (F2, F12, Del)
3. Procure por:
   - Intel VT-x / AMD-V
   - Virtualization Technology
   - SVM Mode
4. Habilite e salve
```

### Verificar Windows Version
```powershell
# Precisa ser Windows 10 Build 18362+ ou Windows 11
winver
```

### Verificar Hyper-V Conflicts
```powershell
# Desabilitar Hyper-V se necessário
bcdedit /set hypervisorlaunchtype off
# Reiniciar e tentar Docker novamente
```

---

## 🎯 Status Atual do Projeto

### ✅ **Funcionando em Produção:**
- 🌐 **API:** https://api.consultoriawk.com/api/health
- 🎨 **Admin:** https://admin.consultoriawk.com
- 🏗️ **Arquitetura:** DDD + SOLID + TDD
- 🇧🇷 **Localização:** 100% Português Brasileiro

### 🐳 **Funcionando Local (após Docker fix):**
- 📦 **Containers:** PostgreSQL, Redis, Laravel, Gateway
- 🌐 **URLs:** localhost:8000, localhost:3000, localhost:4200
- 🔄 **Hot Reload:** Desenvolvimento completo

### 🔶 **Alternativa Funcionando:**
- 📊 **XAMPP:** MySQL + PHP + Apache
- 🌐 **URL:** http://localhost:8001
- ⚡ **Rápido:** Sem overhead de containers

---

## 🚀 Próximos Passos

1. **Escolha uma opção:**
   - 🐳 **Docker:** Solução completa microservices
   - 📊 **XAMPP:** Solução rápida para desenvolvimento

2. **Para Docker:**
   - Execute `fix-docker.ps1` como admin
   - Reinicie
   - Execute `start-quick.bat`

3. **Para XAMPP:**
   - Inicie Apache + MySQL no XAMPP
   - Execute `start-xampp.bat`

**O sistema WK CRM está pronto e funcionando! 🎉**