@echo off
chcp 65001 >nul 2>&1
setlocal enabledelayedexpansion
cls
echo ============================================
echo   Iniciando pro roller
echo ============================================
echo.

REM Verificar que existe .env
if not exist ".env" (
    echo [ERROR] El sistema no esta instalado. Ejecuta instalar.bat primero.
    pause
    exit /b 1
)

REM Verificar actualizaciones
echo [1/2] Verificando actualizaciones...
echo.

REM Intentar primero con Git si esta disponible
where git >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    if exist ".git" (
        git fetch --tags origin >nul 2>&1
        
        for /f "tokens=*" %%i in ('git describe --tags --abbrev=0 2^>nul') do set LOCAL_TAG=%%i
        
        if not defined LOCAL_TAG set LOCAL_TAG=v0.0.0
        
        for /f "tokens=*" %%i in ('git ls-remote --tags origin 2^>nul ^| findstr /R "refs/tags/v[0-9]" ^| sort /R') do (
            if not defined REMOTE_TAG (
                for /f "tokens=2" %%j in ("%%i") do (
                    for /f "tokens=3 delims=/" %%k in ("%%j") do set REMOTE_TAG=%%k
                )
            )
        )
        
        if defined REMOTE_TAG (
            if "!REMOTE_TAG!" NEQ "!LOCAL_TAG!" (
                echo [!] Nuevo release disponible: !REMOTE_TAG!
                echo     Version actual: !LOCAL_TAG!
                echo.
                echo Deseas actualizar ahora? (S/N)
                set /p UPDATE_CHOICE=
                
                if /i "!UPDATE_CHOICE!"=="S" (
                    echo.
                    echo Actualizando desde GitHub...
                    git fetch origin >nul 2>&1
                    git checkout !REMOTE_TAG! >nul 2>&1
                    
                    if %ERRORLEVEL% EQU 0 (
                        call :UPDATE_SYSTEM
                    ) else (
                        for /f "tokens=*" %%i in ('git rev-parse --abbrev-ref HEAD 2^>nul') do set CURRENT_BRANCH=%%i
                        git pull origin !CURRENT_BRANCH! >nul 2>&1
                        if %ERRORLEVEL% EQU 0 (
                            call :UPDATE_SYSTEM
                        )
                    )
                ) else (
                    echo.
                    echo Saltando actualizacion...
                    echo.
                )
            ) else (
                echo [OK] Sistema actualizado (version: !LOCAL_TAG!)
                echo.
            )
            goto START_SERVER
        )
    )
)

REM Si no se pudo con Git, usar API de GitHub
set LOCAL_VERSION=v0.0.0
if exist "VERSION" (
    for /f %%i in (VERSION) do set LOCAL_VERSION=%%i
)

echo     Consultando GitHub...
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference = 'SilentlyContinue'; [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; try { $response = Invoke-RestMethod -Uri 'https://api.github.com/repos/daftsu1/pro-roller-inventory/releases/latest' -UseBasicParsing -TimeoutSec 10; $tagName = $response.tag_name; Write-Host $tagName } catch { Write-Host 'ERROR' }" > "%TEMP%\latest_release.txt" 2>nul

if exist "%TEMP%\latest_release.txt" (
    for /f %%i in ("%TEMP%\latest_release.txt") do set REMOTE_VERSION=%%i
    del "%TEMP%\latest_release.txt"
    
    if "!REMOTE_VERSION!" NEQ "ERROR" (
        if "!REMOTE_VERSION!" NEQ "!LOCAL_VERSION!" (
            echo [!] Nuevo release disponible: !REMOTE_VERSION!
            echo     Version actual: !LOCAL_VERSION!
            echo.
            echo Deseas descargar y actualizar ahora? (S/N)
            echo     NOTA: Esto descargara el codigo desde GitHub.
            echo.
            set /p UPDATE_CHOICE=
            
            if /i "!UPDATE_CHOICE!"=="S" (
                echo.
                echo Descargando release !REMOTE_VERSION! desde GitHub...
                echo     Esto puede tardar unos minutos...
                echo.
                
                set RELEASE_URL=https://github.com/daftsu1/pro-roller-inventory/archive/refs/tags/!REMOTE_VERSION!.zip
                set TEMP_ZIP=%TEMP%\pro-roller-update.zip
                
                powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference = 'Stop'; [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri '%RELEASE_URL%' -OutFile '%TEMP_ZIP%' -UseBasicParsing -TimeoutSec 120"
                
                if exist "!TEMP_ZIP!" (
                    echo     Extrayendo archivos...
                    
                    set TEMP_EXTRACT=%TEMP%\pro-roller-update
                    if exist "!TEMP_EXTRACT!" rmdir /s /q "!TEMP_EXTRACT!"
                    mkdir "!TEMP_EXTRACT!" >nul 2>&1
                    
                    powershell -NoProfile -ExecutionPolicy Bypass -Command "Expand-Archive -Path '%TEMP_ZIP%' -DestinationPath '%TEMP_EXTRACT%' -Force"
                    
                    if %ERRORLEVEL% EQU 0 (
                        echo     Actualizando archivos...
                        
                        set EXTRACTED_DIR=
                        for /d %%d in ("!TEMP_EXTRACT!\pro-roller-inventory-*") do set EXTRACTED_DIR=%%d
                        
                        if defined EXTRACTED_DIR (
                            if exist ".env" copy ".env" ".env.backup" >nul 2>&1
                            
                            echo         Copiando nuevos archivos...
                            xcopy "!EXTRACTED_DIR!\*" "." /E /Y /I /H /Q >nul 2>&1
                            
                            if exist ".env.backup" (
                                copy ".env.backup" ".env" >nul 2>&1
                                del ".env.backup" >nul 2>&1
                            )
                            
                            del "!TEMP_ZIP!" >nul 2>&1
                            rmdir /s /q "!TEMP_EXTRACT!" >nul 2>&1
                            
                            echo !REMOTE_VERSION! > VERSION
                            
                            call :UPDATE_SYSTEM
                        ) else (
                            echo [!] Error: No se encontro el directorio extraido.
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
                echo Saltando actualizacion. Se iniciara la version actual.
                echo.
            )
        ) else (
            echo [OK] El sistema esta actualizado (version: !LOCAL_VERSION!)
            echo.
        )
    ) else (
        echo [i] No se pudo verificar actualizaciones (problema de conexion)
        echo.
    )
) else (
    echo [i] No se pudo verificar actualizaciones
    echo.
)

:START_SERVER
echo [2/2] Iniciando servidor de desarrollo...
echo.
echo ============================================
echo   El sistema estara disponible en:
echo   http://localhost:8000
echo ============================================
echo.
echo Para detener el servidor, presiona Ctrl+C
echo.
echo ============================================
echo.

php artisan serve

:UPDATE_SYSTEM
echo     Ejecutando migraciones y actualizando dependencias...
echo.

if exist "composer.json" (
    composer install --no-interaction --prefer-dist --optimize-autoloader >nul 2>&1
)

php artisan migrate --force >nul 2>&1
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1
php artisan view:clear >nul 2>&1

echo [OK] Sistema actualizado correctamente
echo.
exit /b
