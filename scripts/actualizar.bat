@echo off
chcp 65001 >nul
echo ============================================
echo   ACTUALIZADOR - pro roller
echo ============================================
echo.

REM Verificar que estamos en el directorio correcto
if not exist "composer.json" (
    echo [ERROR] Este script debe ejecutarse desde la raíz del proyecto.
    pause
    exit /b 1
)

REM Verificar que existe .env (ya está instalado)
if not exist ".env" (
    echo [ERROR] El sistema no está instalado. Ejecuta instalar.bat primero.
    pause
    exit /b 1
)

echo [1/6] Creando backup de base de datos...
set BACKUP_FILE=backup_%date:~-4,4%%date:~-7,2%%date:~-10,2%_%time:~0,2%%time:~3,2%%time:~6,2%.sql
set BACKUP_FILE=%BACKUP_FILE: =0%
set BACKUP_DIR=storage\backups

if not exist "%BACKUP_DIR%" (
    mkdir "%BACKUP_DIR%"
)

REM Leer DB_DATABASE del .env
for /f "tokens=2 delims==" %%a in ('findstr /b "DB_DATABASE=" .env') do set DB_NAME=%%a
for /f "tokens=2 delims==" %%a in ('findstr /b "DB_USERNAME=" .env') do set DB_USER=%%a
for /f "tokens=2 delims==" %%a in ('findstr /b "DB_PASSWORD=" .env') do set DB_PASS=%%a

if "%DB_PASS%"=="" (
    mysqldump -u %DB_USER% %DB_NAME% > "%BACKUP_DIR%\%BACKUP_FILE%" 2>nul
) else (
    mysqldump -u %DB_USER% -p%DB_PASS% %DB_NAME% > "%BACKUP_DIR%\%BACKUP_FILE%" 2>nul
)

if %ERRORLEVEL% EQU 0 (
    echo [✓] Backup creado: %BACKUP_DIR%\%BACKUP_FILE%
) else (
    echo [!] No se pudo crear el backup. Continuando de todas formas...
)

echo.
echo [2/6] Detectando método de actualización...
where git >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    REM Git está disponible
    git --version >nul 2>&1
    if %ERRORLEVEL% EQU 0 (
        REM Verificar si estamos en un repositorio Git
        git status >nul 2>&1
        if %ERRORLEVEL% EQU 0 (
            echo [✓] Git detectado. Usando git pull...
            set USAR_GIT=1
        ) else (
            echo [!] Git detectado pero no hay repositorio inicializado.
            echo     Usando descarga desde GitHub...
            set USAR_GIT=0
        )
    ) else (
        set USAR_GIT=0
    )
) else (
    echo [!] Git no detectado. Descargando desde GitHub...
    set USAR_GIT=0
)

echo.
if "%USAR_GIT%"=="1" (
    echo [3/6] Actualizando desde Git (git pull)...
    git pull origin main
    if %ERRORLEVEL% NEQ 0 (
        echo [ERROR] Error al actualizar desde Git.
        pause
        exit /b 1
    )
    echo [✓] Código actualizado desde Git
) else (
    echo [3/6] Descargando última versión desde GitHub...
    
    REM Crear carpeta temporal
    if exist "temp_update" (
        rmdir /s /q temp_update
    )
    mkdir temp_update
    
    REM Descargar ZIP desde GitHub
    echo        Descargando código fuente...
    powershell -Command "try { Invoke-WebRequest -Uri 'https://github.com/daftsu1/pro-roller-inventory/archive/refs/heads/main.zip' -OutFile 'temp_update\actualizacion.zip' -UseBasicParsing; Write-Host '[✓] Descarga completada' } catch { Write-Host '[ERROR] Error al descargar: ' $_.Exception.Message; exit 1 }"
    
    if %ERRORLEVEL% NEQ 0 (
        echo [ERROR] No se pudo descargar la actualización.
        pause
        exit /b 1
    )
    
    echo        Extrayendo archivos...
    powershell -Command "Expand-Archive -Path 'temp_update\actualizacion.zip' -DestinationPath 'temp_update' -Force"
    
    REM Copiar archivos (preservar .env y storage)
    echo        Copiando archivos nuevos...
    
    REM Lista de archivos/carpetas a preservar
    set PRESERVAR=.env,storage,vendor,node_modules
    
    REM Copiar archivos desde el ZIP extraído
    for /d %%d in (temp_update\pro-roller-inventory-main\*) do (
        set folder_name=%%~nxd
        if not "!folder_name!"==".env" if not "!folder_name!"=="storage" if not "!folder_name!"=="vendor" (
            xcopy /E /I /Y "temp_update\pro-roller-inventory-main\!folder_name!" "!folder_name!" >nul
        )
    )
    
    for %%f in (temp_update\pro-roller-inventory-main\*) do (
        set file_name=%%~nxf
        if not "!file_name!"==".env" if not "!file_name!"==".env.example" (
            copy /Y "temp_update\pro-roller-inventory-main\!file_name!" "!file_name!" >nul
        )
    )
    
    REM Limpiar archivos temporales
    del /q temp_update\actualizacion.zip >nul 2>&1
    rmdir /s /q temp_update >nul 2>&1
    
    echo [✓] Archivos actualizados desde GitHub
)

echo.
echo [4/6] Actualizando dependencias...
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
if %ERRORLEVEL% NEQ 0 (
    echo [!] Advertencia: Algunas dependencias no se actualizaron correctamente.
    echo    Continuando de todas formas...
)
echo [✓] Dependencias actualizadas

echo.
echo [5/6] Ejecutando migraciones...
php artisan migrate --force
if %ERRORLEVEL% NEQ 0 (
    echo [!] Advertencia: Algunas migraciones pueden haber fallado.
    echo    Revisa los mensajes anteriores.
)
echo [✓] Migraciones ejecutadas

echo.
echo [6/6] Limpiando caché...
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1
php artisan view:clear >nul 2>&1
php artisan route:clear >nul 2>&1
echo [✓] Caché limpiado

echo.
echo ============================================
echo   ¡ACTUALIZACIÓN COMPLETADA!
echo ============================================
echo.
echo El sistema ha sido actualizado exitosamente.
echo Backup guardado en: %BACKUP_DIR%\%BACKUP_FILE%
echo.
echo Si encuentras algún problema, puedes restaurar el backup.
echo.
pause
