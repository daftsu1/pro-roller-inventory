@echo off
chcp 65001 >nul
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

echo Iniciando servidor de desarrollo...
echo.
echo El sistema estará disponible en: http://localhost:8000
echo.
echo Para detener el servidor, presiona Ctrl+C
echo.
echo ============================================
echo.

php artisan serve
