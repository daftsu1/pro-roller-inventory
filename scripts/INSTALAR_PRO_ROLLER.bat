@echo off
chcp 65001 >nul 2>&1
setlocal enabledelayedexpansion
cls
echo ============================================
echo   INSTALADOR SIMPLIFICADO - pro roller
echo ============================================
echo.
echo Este instalador configura el sistema pro roller
echo asumiendo que ya tienes XAMPP y Composer instalados.
echo.
echo Presiona cualquier tecla para continuar...
pause >nul
cls

REM Verificar que estamos en el directorio correcto
if not exist "composer.json" (
    echo [ERROR] Este script debe ejecutarse desde la raíz del proyecto.
    echo        Asegúrate de estar en la carpeta donde está composer.json
    pause
    exit /b 1
)

echo ============================================
echo   INSTALADOR - pro roller
echo ============================================
echo.
echo [1/6] Verificando requisitos...
echo.

REM Verificar PHP
php -v >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [X] PHP no encontrado.
    echo.
    echo Por favor instala XAMPP primero desde:
    echo https://www.apachefriends.org/download.html
    echo.
    echo Después de instalarlo, ejecuta este instalador nuevamente.
    pause
    exit /b 1
)
echo [✓] PHP detectado

REM Verificar Composer
composer --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [X] Composer no encontrado.
    echo.
    echo Por favor instala Composer primero desde:
    echo https://getcomposer.org/download/
    echo.
    echo Después de instalarlo, ejecuta este instalador nuevamente.
    pause
    exit /b 1
)
echo [✓] Composer detectado

REM Verificar MySQL
mysql -u root -e "SELECT 1" >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [!] MySQL no responde.
    echo     Asegúrate de que MySQL esté corriendo en XAMPP Control Panel.
    echo.
    set /p continuar="¿Deseas continuar de todas formas? (s/n): "
    if /i not "!continuar!"=="s" (
        exit /b 1
    )
) else (
    echo [✓] MySQL detectado
)

echo.
echo [2/6] Instalando dependencias de Composer...
echo        Esto puede tardar varios minutos...
composer install --no-interaction --prefer-dist --optimize-autoloader
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Error al instalar dependencias.
    pause
    exit /b 1
)
echo [✓] Dependencias instaladas

echo.
echo [3/6] Configurando archivo .env...
if not exist ".env" (
    copy ".env.example" ".env" >nul
    echo [✓] Archivo .env creado
) else (
    echo [!] El archivo .env ya existe. Se mantendrá la configuración actual.
)
php artisan key:generate --force >nul 2>&1
echo [✓] Clave de aplicación generada

echo.
echo [4/6] Configurando base de datos...
echo        Creando base de datos 'pro_roller'...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS pro_roller CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
powershell -Command "(Get-Content .env) -replace 'DB_DATABASE=.*', 'DB_DATABASE=pro_roller' | Set-Content .env" 2>nul
powershell -Command "(Get-Content .env) -replace 'DB_HOST=.*', 'DB_HOST=127.0.0.1' | Set-Content .env" 2>nul
powershell -Command "(Get-Content .env) -replace 'DB_USERNAME=.*', 'DB_USERNAME=root' | Set-Content .env" 2>nul
powershell -Command "(Get-Content .env) -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=' | Set-Content .env" 2>nul
echo [✓] Base de datos configurada

echo.
echo [5/6] Publicando migraciones de Spatie Permission...
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --quiet
echo [✓] Migraciones publicadas

echo.
echo [6/6] Ejecutando migraciones y seeders...
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
echo [✓] Migraciones y seeders ejecutados

echo.
echo ============================================
echo   ¡INSTALACIÓN COMPLETADA!
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
