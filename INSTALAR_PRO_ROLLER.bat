@echo off
chcp 65001 >nul
cls
echo ============================================
echo   INSTALADOR AUTOMATICO - pro roller
echo ============================================
echo.
echo Este instalador descargará e instalará
echo el sistema pro roller automáticamente.
echo.
echo Presiona cualquier tecla para continuar...
pause >nul
cls

REM Directorio donde se instalará el sistema
set INSTALL_DIR=%~dp0pro-roller-inventory
set PROJECT_NAME=pro-roller-inventory

echo ============================================
echo   INSTALADOR AUTOMATICO - pro roller
echo ============================================
echo.
echo [1/6] Verificando requisitos...
echo.

REM Verificar PHP
php -v >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [X] ERROR: PHP no encontrado
    echo.
    echo Por favor instala XAMPP primero:
    echo https://www.apachefriends.org/download.html
    echo.
    echo Después de instalar XAMPP, ejecuta este instalador nuevamente.
    pause
    exit /b 1
)
echo [✓] PHP detectado

REM Verificar Composer
composer --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [X] ERROR: Composer no encontrado
    echo.
    echo Por favor instala Composer primero:
    echo https://getcomposer.org/download/
    echo.
    echo Después de instalar Composer, ejecuta este instalador nuevamente.
    pause
    exit /b 1
)
echo [✓] Composer detectado

REM Verificar MySQL
mysql -u root -e "SELECT 1" >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [!] Advertencia: MySQL no está corriendo
    echo.
    echo Por favor:
    echo 1. Abre XAMPP Control Panel
    echo 2. Haz clic en "Start" en MySQL
    echo 3. Espera a que muestre "Running" en verde
    echo 4. Luego presiona cualquier tecla aquí para continuar...
    pause >nul
) else (
    echo [✓] MySQL detectado y funcionando
)

echo.
echo ============================================
echo.
echo [2/6] Descargando proyecto desde GitHub...
echo        Esto puede tardar unos minutos...
echo.

REM Si ya existe el directorio, preguntar si sobrescribir
if exist "%INSTALL_DIR%" (
    echo [!] Ya existe una instalación en: %INSTALL_DIR%
    set /p sobrescribir="¿Deseas sobrescribirla? (s/n): "
    if /i not "%sobrescribir%"=="s" (
        echo.
        echo Instalación cancelada.
        pause
        exit /b 0
    )
    echo.
    echo Eliminando instalación anterior...
    rmdir /s /q "%INSTALL_DIR%" 2>nul
)

REM Crear directorio temporal
set TEMP_DIR=%TEMP%\pro-roller-download
if exist "%TEMP_DIR%" (
    rmdir /s /q "%TEMP_DIR%"
)
mkdir "%TEMP_DIR%"

REM Descargar ZIP desde GitHub
echo Descargando código fuente...
powershell -Command "try { $ProgressPreference = 'SilentlyContinue'; Invoke-WebRequest -Uri 'https://github.com/daftsu1/pro-roller-inventory/archive/refs/heads/main.zip' -OutFile '%TEMP_DIR%\pro-roller.zip' -UseBasicParsing; Write-Host '[✓] Descarga completada' } catch { Write-Host '[X] Error al descargar:' $_.Exception.Message; exit 1 }"

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [X] ERROR: No se pudo descargar el proyecto.
    echo.
    echo Posibles causas:
    echo - No tienes conexión a internet
    echo - GitHub no está accesible
    echo - Problemas con el firewall
    echo.
    echo Intenta nuevamente más tarde.
    pause
    exit /b 1
)

echo.
echo [3/6] Extrayendo archivos...
powershell -Command "Expand-Archive -Path '%TEMP_DIR%\pro-roller.zip' -DestinationPath '%TEMP_DIR%' -Force"

if %ERRORLEVEL% NEQ 0 (
    echo [X] ERROR: No se pudieron extraer los archivos.
    pause
    exit /b 1
)

REM Mover carpeta extraída a la ubicación final
move "%TEMP_DIR%\pro-roller-inventory-main" "%INSTALL_DIR%" >nul

REM Limpiar archivos temporales
del /q "%TEMP_DIR%\pro-roller.zip" >nul 2>&1
rmdir /s /q "%TEMP_DIR%" >nul 2>&1

echo [✓] Proyecto descargado y extraído

echo.
echo ============================================
echo.
echo [4/6] Instalando dependencias...
echo        Esto puede tardar 5-10 minutos...
echo        (Descargando Laravel y componentes)
echo.

cd /d "%INSTALL_DIR%"

composer install --no-interaction --prefer-dist --optimize-autoloader --quiet

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [X] ERROR: No se pudieron instalar las dependencias.
    pause
    exit /b 1
)

echo [✓] Dependencias instaladas

echo.
echo ============================================
echo.
echo [5/6] Configurando sistema...

REM Configurar .env
if not exist ".env" (
    copy ".env.example" ".env" >nul
)

REM Generar clave de aplicación
php artisan key:generate --force >nul 2>&1

REM Configurar base de datos en .env
powershell -Command "(Get-Content .env) -replace 'DB_DATABASE=.*', 'DB_DATABASE=pro_roller' | Set-Content .env" 2>nul
powershell -Command "(Get-Content .env) -replace 'DB_HOST=.*', 'DB_HOST=127.0.0.1' | Set-Content .env" 2>nul
powershell -Command "(Get-Content .env) -replace 'DB_USERNAME=.*', 'DB_USERNAME=root' | Set-Content .env" 2>nul
powershell -Command "(Get-Content .env) -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=' | Set-Content .env" 2>nul

REM Crear base de datos
echo        Creando base de datos...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS pro_roller CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul

REM Publicar migraciones de Spatie
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --quiet >nul 2>&1

echo [✓] Sistema configurado

echo.
echo ============================================
echo.
echo [6/6] Ejecutando migraciones y creando datos iniciales...
echo        Esto puede tardar unos segundos...
echo.

REM Ejecutar migraciones
php artisan migrate --force
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [!] Advertencia: Hubo problemas con las migraciones.
    echo    Intenta ejecutar manualmente: php artisan migrate
)

REM Ejecutar seeders
php artisan db:seed --force
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [!] Advertencia: Hubo problemas con los seeders.
    echo    Intenta ejecutar manualmente: php artisan db:seed
)

echo [✓] Base de datos configurada

echo.
echo ============================================
echo   ¡INSTALACIÓN COMPLETADA!
echo ============================================
echo.
echo El sistema pro roller ha sido instalado en:
echo %INSTALL_DIR%
echo.
echo ============================================
echo   CREDENCIALES POR DEFECTO
echo ============================================
echo.
echo Administrador:
echo   Email: admin@proroller.cl
echo   Contraseña: password
echo.
echo Vendedor:
echo   Email: vendedor@proroller.cl
echo   Contraseña: password
echo.
echo ⚠️  IMPORTANTE: Cambia las contraseñas después
echo    del primer inicio de sesión.
echo.
echo ============================================
echo   PRÓXIMOS PASOS
echo ============================================
echo.
echo Para iniciar el sistema:
echo.
echo 1. Abre una terminal en: %INSTALL_DIR%
echo 2. Ejecuta: iniciar.bat
echo    O doble clic en: iniciar.bat
echo.
echo 3. Abre tu navegador en: http://localhost:8000
echo.
echo ============================================
echo.
echo ¿Deseas crear un acceso directo para iniciar el sistema? (s/n)
set /p crear_acceso="> "

if /i "%crear_acceso%"=="s" (
    echo.
    echo Creando acceso directo en el escritorio...
    
    REM Crear acceso directo usando PowerShell
    powershell -Command "$WshShell = New-Object -ComObject WScript.Shell; $Shortcut = $WshShell.CreateShortcut('%USERPROFILE%\Desktop\Iniciar pro roller.lnk'); $Shortcut.TargetPath = '%INSTALL_DIR%\iniciar.bat'; $Shortcut.WorkingDirectory = '%INSTALL_DIR%'; $Shortcut.Description = 'Inicia el servidor de pro roller'; $Shortcut.Save()"
    
    if %ERRORLEVEL% EQU 0 (
        echo [✓] Acceso directo creado en el escritorio
    ) else (
        echo [!] No se pudo crear el acceso directo
    )
)

echo.
echo ============================================
echo   ¡LISTO PARA USAR!
echo ============================================
echo.
pause
