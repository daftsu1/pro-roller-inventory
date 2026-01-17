@echo off
chcp 65001 >nul
echo ============================================
echo   INSTALADOR - pro roller
echo ============================================
echo.

REM Verificar que estamos en el directorio correcto
if not exist "composer.json" (
    echo [ERROR] Este script debe ejecutarse desde la raíz del proyecto.
    echo        Asegúrate de estar en la carpeta donde está composer.json
    pause
    exit /b 1
)

echo [1/8] Verificando PHP...
php -v >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] PHP no encontrado. Por favor instala XAMPP primero.
    pause
    exit /b 1
)
echo [✓] PHP detectado

echo.
echo [2/8] Verificando Composer...
composer --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Composer no encontrado. Por favor instálalo primero.
    echo        Descarga desde: https://getcomposer.org/download/
    pause
    exit /b 1
)
echo [✓] Composer detectado

echo.
echo [3/8] Verificando MySQL...
REM Intentar conectar a MySQL
mysql -u root -e "SELECT 1" >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [!] MySQL no responde. Asegúrate de que MySQL esté corriendo en XAMPP.
    echo     Abre XAMPP Control Panel y haz clic en "Start" en MySQL.
    set /p continuar="¿Deseas continuar de todas formas? (s/n): "
    if /i not "%continuar%"=="s" (
        exit /b 1
    )
) else (
    echo [✓] MySQL detectado y funcionando
)

echo.
echo [4/8] Instalando dependencias de Composer...
echo        Esto puede tardar varios minutos...
composer install --no-interaction --prefer-dist --optimize-autoloader
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Error al instalar dependencias.
    pause
    exit /b 1
)
echo [✓] Dependencias instaladas

echo.
echo [5/8] Configurando archivo .env...
if not exist ".env" (
    copy ".env.example" ".env" >nul
    echo [✓] Archivo .env creado desde .env.example
) else (
    echo [!] El archivo .env ya existe. Se mantendrá la configuración actual.
)

REM Generar clave de aplicación
php artisan key:generate --force >nul 2>&1
echo [✓] Clave de aplicación generada

echo.
echo [6/8] Configurando base de datos...
echo        Creando base de datos 'pro_roller'...

REM Crear base de datos
mysql -u root -e "CREATE DATABASE IF NOT EXISTS pro_roller CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul

REM Actualizar .env con el nombre de la BD
powershell -Command "(Get-Content .env) -replace 'DB_DATABASE=.*', 'DB_DATABASE=pro_roller' | Set-Content .env" 2>nul
powershell -Command "(Get-Content .env) -replace 'DB_HOST=.*', 'DB_HOST=127.0.0.1' | Set-Content .env" 2>nul
powershell -Command "(Get-Content .env) -replace 'DB_USERNAME=.*', 'DB_USERNAME=root' | Set-Content .env" 2>nul
powershell -Command "(Get-Content .env) -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=' | Set-Content .env" 2>nul

echo [✓] Base de datos configurada

echo.
echo [7/8] Publicando migraciones de Spatie Permission...
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --quiet
echo [✓] Migraciones publicadas

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

echo [✓] Migraciones y seeders ejecutados correctamente

echo.
echo ============================================
echo   ¡INSTALACIÓN COMPLETADA!
echo ============================================
echo.
echo Datos por defecto:
echo   Admin: admin@proroller.cl / password
echo   Vendedor: vendedor@proroller.cl / password
echo.
echo Para iniciar el servidor, ejecuta: iniciar.bat
echo O manualmente: php artisan serve
echo.
echo ============================================
pause
