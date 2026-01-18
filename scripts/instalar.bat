@echo off
chcp 65001 >nul 2>&1
setlocal enabledelayedexpansion
cls
echo ============================================
echo   INSTALADOR - pro roller
echo ============================================
echo.
echo Directorio actual: %CD%
echo.

REM Cambiar al directorio padre si estamos en scripts/
set "SCRIPT_DIR=%~dp0"
if exist "%SCRIPT_DIR%..\composer.json" (
    cd /d "%SCRIPT_DIR%.."
    echo Cambiando al directorio raiz del proyecto...
    echo Nuevo directorio: %CD%
    echo.
)

REM Verificar que estamos en el directorio correcto
if not exist "composer.json" (
    echo.
    echo [ERROR] Este script debe ejecutarse desde la raiz del proyecto.
    echo        Asegurate de estar en la carpeta donde esta composer.json
    echo.
    echo        Directorio actual: %CD%
    echo.
    pause
    exit /b 1
)

echo [1/8] Verificando PHP...
where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] PHP no encontrado. Por favor instala XAMPP primero.
    pause
    exit /b 1
)
php -v >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] PHP no funciona correctamente.
    pause
    exit /b 1
)
echo [OK] PHP detectado

echo.
echo [2/8] Verificando Composer...
where composer.bat >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    where composer >nul 2>nul
    if %ERRORLEVEL% NEQ 0 (
        echo [ERROR] Composer no encontrado en el PATH.
        echo        Por favor instalalo primero desde: https://getcomposer.org/download/
        pause
        exit /b 1
    )
)
echo [OK] Composer detectado

echo.
echo [3/8] Verificando MySQL...
set MYSQL_OK=0
where mysql >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    mysql -u root -e "SELECT 1" >nul 2>nul
    if %ERRORLEVEL% EQU 0 (
        echo [OK] MySQL detectado y funcionando
        set MYSQL_OK=1
    )
)
if !MYSQL_OK! EQU 0 (
    if exist "C:\xampp\mysql\bin\mysql.exe" (
        "C:\xampp\mysql\bin\mysql.exe" -u root -e "SELECT 1" >nul 2>nul
        if %ERRORLEVEL% EQU 0 (
            echo [OK] MySQL detectado y funcionando (XAMPP)
            set MYSQL_OK=1
        )
    )
)
if !MYSQL_OK! EQU 0 (
    netstat -an | findstr ":3306" | findstr "LISTENING" >nul 2>nul
    if %ERRORLEVEL% EQU 0 (
        echo [OK] MySQL detectado (puerto 3306 activo)
        set MYSQL_OK=1
    )
)
if !MYSQL_OK! EQU 0 (
    echo [!] No se pudo verificar MySQL. Asegurate de que MySQL este corriendo en XAMPP.
    echo     Abre XAMPP Control Panel y haz clic en "Start" en MySQL.
    set /p continuar="Deseas continuar de todas formas? (s/n): "
    if /i not "!continuar!"=="s" (
        exit /b 1
    )
)

echo.
echo [4/8] Instalando dependencias de Composer...
echo        Esto puede tardar varios minutos...

REM Crear directorios necesarios para Laravel antes de composer install
if not exist "bootstrap\cache" mkdir "bootstrap\cache" 2>nul
if not exist "storage\app" mkdir "storage\app" 2>nul
if not exist "storage\framework\cache" mkdir "storage\framework\cache" 2>nul
if not exist "storage\framework\sessions" mkdir "storage\framework\sessions" 2>nul
if not exist "storage\framework\views" mkdir "storage\framework\views" 2>nul
if not exist "storage\logs" mkdir "storage\logs" 2>nul

call composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] Error al instalar dependencias.
    echo        Si el error menciona Git, puedes instalarlo desde: https://git-scm.com
    echo        O continuar manualmente ejecutando: composer install
    echo.
    pause
    exit /b 1
)
echo.
echo [OK] Dependencias instaladas

echo.
echo [5/8] Configurando archivo .env...
if not exist ".env" (
    copy ".env.example" ".env" >nul 2>&1
    echo [OK] Archivo .env creado desde .env.example
) else (
    echo [!] El archivo .env ya existe. Se mantendra la configuracion actual.
)

REM Generar clave de aplicacion
php artisan key:generate --force >nul 2>&1
echo [OK] Clave de aplicacion generada

echo.
echo [6/8] Configurando base de datos...
echo        Creando base de datos 'pro_roller'...

REM Crear base de datos
if !MYSQL_OK! EQU 1 (
    if exist "C:\xampp\mysql\bin\mysql.exe" (
        "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS pro_roller CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
    ) else (
        mysql -u root -e "CREATE DATABASE IF NOT EXISTS pro_roller CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
    )
)

REM Actualizar .env con el nombre de la BD
powershell -NoProfile -Command "(Get-Content .env) -replace 'DB_DATABASE=.*', 'DB_DATABASE=pro_roller' | Set-Content .env" 2>nul
powershell -NoProfile -Command "(Get-Content .env) -replace 'DB_HOST=.*', 'DB_HOST=127.0.0.1' | Set-Content .env" 2>nul
powershell -NoProfile -Command "(Get-Content .env) -replace 'DB_USERNAME=.*', 'DB_USERNAME=root' | Set-Content .env" 2>nul
powershell -NoProfile -Command "(Get-Content .env) -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=' | Set-Content .env" 2>nul

echo [OK] Base de datos configurada

echo.
echo [7/8] Publicando migraciones de Spatie Permission...
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --quiet
echo [OK] Migraciones publicadas

echo.
echo [8/8] Ejecutando migraciones y seeders...
echo        Esto puede tardar unos segundos...

php artisan migrate --force
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Error al ejecutar migraciones.
    pause
    exit /b 1
)

php artisan db:seed --force
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Error al ejecutar seeders.
    pause
    exit /b 1
)

echo [OK] Migraciones y seeders ejecutados correctamente

echo.
echo ============================================
echo   INSTALACION COMPLETADA!
echo ============================================
echo.
echo Usuarios por defecto:
echo   Admin: admin@proroller.cl / password
echo   Vendedor: vendedor@proroller.cl / password
echo.
echo Para iniciar el sistema, ejecuta:
echo   scripts\iniciar.bat
echo.
echo O manualmente: php artisan serve
echo.
echo ============================================
pause
