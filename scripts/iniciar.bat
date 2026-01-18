@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion
cls
echo ============================================
echo   Iniciando pro roller
echo ============================================
echo.

REM Verificar que existe .env
if not exist ".env" (
    echo [ERROR] El sistema no está instalado. Ejecuta instalar.bat primero.
    pause
    exit /b 1
)

REM Verificar si Git está instalado y tenemos .git
where git >nul 2>&1
set HAS_GIT=%ERRORLEVEL%

if %HAS_GIT% EQU 0 if exist ".git" (
    REM MÉTODO 1: Con Git instalado, detectar releases por tags
    echo [1/2] Verificando actualizaciones (Git)...
    echo.
    
    REM Fetch de tags para obtener releases
    git fetch --tags origin >nul 2>&1
    
    REM Obtener el último tag local
    for /f "tokens=*" %%i in ('git describe --tags --abbrev=0 2^>nul') do set LOCAL_TAG=%%i
    
    REM Obtener el último tag remoto
    for /f "tokens=*" %%i in ('git ls-remote --tags origin 2^>nul ^| findstr /R "refs/tags/v[0-9]" ^| sort /R') do (
        set REMOTE_TAG_LINE=%%i
        goto :FOUND_REMOTE_TAG
    )
    :FOUND_REMOTE_TAG
    
    if defined REMOTE_TAG_LINE (
        REM Extraer el nombre del tag (formato: hash refs/tags/v1.0.0)
        for /f "tokens=2" %%i in ("!REMOTE_TAG_LINE!") do (
            for /f "tokens=3 delims=/" %%j in ("%%i") do set REMOTE_TAG=%%j
        )
    )
    
    REM Si no tenemos tag local, asumir v0.0.0
    if not defined LOCAL_TAG set LOCAL_TAG=v0.0.0
    
    REM Si hay un tag remoto y es diferente al local, hay actualización
    if defined REMOTE_TAG (
        if "!REMOTE_TAG!" NEQ "!LOCAL_TAG!" (
            echo [!] Nuevo release disponible: !REMOTE_TAG!
            echo     Versión actual: !LOCAL_TAG!
            echo.
            echo ¿Deseas actualizar ahora? (S/N)
            set /p UPDATE_CHOICE=
            
            if /i "!UPDATE_CHOICE!"=="S" (
                echo.
                echo Actualizando desde GitHub...
                git fetch origin >nul 2>&1
                git checkout !REMOTE_TAG! >nul 2>&1
                
                if %ERRORLEVEL% EQU 0 (
                    call :UPDATE_SYSTEM
                ) else (
                    REM Si checkout falla, intentar pull normal
                    for /f "tokens=*" %%i in ('git rev-parse --abbrev-ref HEAD 2^>nul') do set CURRENT_BRANCH=%%i
                    git pull origin !CURRENT_BRANCH! >nul 2>&1
                    if %ERRORLEVEL% EQU 0 (
                        call :UPDATE_SYSTEM
                    ) else (
                        echo [!] Error al actualizar. Continuando con la versión actual...
                        echo.
                    )
                )
            ) else (
                echo.
                echo Saltando actualización. Se iniciará la versión actual.
                echo.
            )
        ) else (
            echo [✓] El sistema está actualizado (versión: !LOCAL_TAG!)
            echo.
        )
    ) else (
        echo [i] No se pudieron detectar releases. Continuando...
        echo.
    )
) else (
    REM MÉTODO 2: Sin Git, usar API de GitHub para detectar releases
    echo [1/2] Verificando actualizaciones (API de GitHub)...
    echo.
    
    REM Leer versión actual desde archivo VERSION (si existe)
    set LOCAL_VERSION=v0.0.0
    if exist "VERSION" (
        for /f %%i in (VERSION) do set LOCAL_VERSION=%%i
    )
    
    REM Consultar API de GitHub para obtener último release
    echo     Consultando GitHub...
    powershell -NoProfile -ExecutionPolicy Bypass -Command "& {$ErrorActionPreference = 'SilentlyContinue'; [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; try { $response = Invoke-RestMethod -Uri 'https://api.github.com/repos/daftsu1/pro-roller-inventory/releases/latest' -UseBasicParsing -TimeoutSec 10; $tagName = $response.tag_name; Write-Host $tagName } catch { Write-Host 'ERROR' }}" > "%TEMP%\latest_release.txt" 2>nul
    
    if exist "%TEMP%\latest_release.txt" (
        for /f %%i in ("%TEMP%\latest_release.txt") do set REMOTE_VERSION=%%i
        del "%TEMP%\latest_release.txt"
        
        if "!REMOTE_VERSION!" NEQ "ERROR" (
            if "!REMOTE_VERSION!" NEQ "!LOCAL_VERSION!" (
                echo [!] Nuevo release disponible: !REMOTE_VERSION!
                echo     Versión actual: !LOCAL_VERSION!
                echo.
                echo ¿Deseas descargar y actualizar ahora? (S/N)
                echo     NOTA: Esto descargará el código desde GitHub.
                echo.
                set /p UPDATE_CHOICE=
                
                if /i "!UPDATE_CHOICE!"=="S" (
                    echo.
                    echo Descargando release !REMOTE_VERSION! desde GitHub...
                    echo     Esto puede tardar unos minutos...
                    echo.
                    
                    REM Descargar el ZIP del release
                    set RELEASE_URL=https://github.com/daftsu1/pro-roller-inventory/archive/refs/tags/!REMOTE_VERSION!.zip
                    set TEMP_ZIP=%TEMP%\pro-roller-update.zip
                    
                    powershell -NoProfile -ExecutionPolicy Bypass -Command "& {$ErrorActionPreference = 'Stop'; [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri '!RELEASE_URL!' -OutFile '!TEMP_ZIP!' -UseBasicParsing -TimeoutSec 120}"
                    
                    if exist "!TEMP_ZIP!" (
                        echo     Extrayendo archivos...
                        
                        REM Crear directorio temporal para extracción
                        set TEMP_EXTRACT=%TEMP%\pro-roller-update
                        if exist "!TEMP_EXTRACT!" rmdir /s /q "!TEMP_EXTRACT!"
                        mkdir "!TEMP_EXTRACT!" >nul 2>&1
                        
                        REM Extraer ZIP
                        powershell -NoProfile -ExecutionPolicy Bypass -Command "& {Expand-Archive -Path '!TEMP_ZIP!' -DestinationPath '!TEMP_EXTRACT!' -Force}"
                        
                        if %ERRORLEVEL% EQU 0 (
                            echo     Actualizando archivos...
                            
                            REM Encontrar el directorio extraído
                            set EXTRACTED_DIR=
                            for /d %%d in ("!TEMP_EXTRACT!\pro-roller-inventory-*") do set EXTRACTED_DIR=%%d
                            
                            if defined EXTRACTED_DIR (
                                REM Backup del .env
                                if exist ".env" copy ".env" ".env.backup" >nul 2>&1
                                
                                REM Copiar archivos (excluir storage y vendor si existen)
                                echo         Copiando nuevos archivos...
                                xcopy "!EXTRACTED_DIR!\*" "." /E /Y /I /H /Q >nul 2>&1
                                
                                REM Restaurar .env si existe backup
                                if exist ".env.backup" (
                                    copy ".env.backup" ".env" >nul 2>&1
                                    del ".env.backup" >nul 2>&1
                                )
                                
                                REM Limpiar archivos temporales
                                del "!TEMP_ZIP!" >nul 2>&1
                                rmdir /s /q "!TEMP_EXTRACT!" >nul 2>&1
                                
                                REM Guardar la nueva versión
                                echo !REMOTE_VERSION! > VERSION
                                
                                call :UPDATE_SYSTEM
                            ) else (
                                echo [!] Error: No se encontró el directorio extraído.
                                echo.
                            )
                        ) else (
                            echo [!] Error al extraer el archivo ZIP.
                            echo.
                        )
                    ) else (
                        echo [!] Error al descargar el release.
                        echo.
                    )
                ) else (
                    echo.
                    echo Saltando actualización. Se iniciará la versión actual.
                    echo.
                )
            ) else (
                echo [✓] El sistema está actualizado (versión: !LOCAL_VERSION!)
                echo.
            )
        ) else (
            echo [i] No se pudo verificar actualizaciones (problema de conexión)
            echo.
        )
    ) else (
        echo [i] No se pudo verificar actualizaciones
        echo.
    )
)

goto START_SERVER

:UPDATE_SYSTEM
echo     Ejecutando migraciones y actualizando dependencias...
echo.

REM Actualizar dependencias de Composer
if exist "composer.json" (
    echo         Instalando dependencias de Composer...
    composer install --no-interaction --prefer-dist --optimize-autoloader >nul 2>&1
)

REM Ejecutar migraciones
echo         Ejecutando migraciones de base de datos...
php artisan migrate --force >nul 2>&1

REM Limpiar caché
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1
php artisan view:clear >nul 2>&1

echo [✓] Sistema actualizado correctamente
echo.
exit /b

:START_SERVER
echo [2/2] Iniciando servidor de desarrollo...
echo.
echo ============================================
echo   El sistema estará disponible en:
echo   http://localhost:8000
echo ============================================
echo.
echo Para detener el servidor, presiona Ctrl+C
echo.
echo ============================================
echo.

php artisan serve
