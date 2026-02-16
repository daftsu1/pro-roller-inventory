#!/bin/sh

set -e

echo "Iniciando aplicación Laravel..."

# Esperar a que la base de datos esté lista
echo "Esperando conexión a la base de datos..."
until php -r "try { \$pdo = new PDO('mysql:host=${DB_HOST:-db};port=${DB_PORT:-3306}', '${DB_USERNAME:-root}', '${DB_PASSWORD:-}'); \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); exit(0); } catch (PDOException \$e) { exit(1); }" 2>/dev/null; do
    echo "Esperando MySQL..."
    sleep 2
done

echo "Base de datos conectada!"

# Crear directorios necesarios si no existen
mkdir -p storage/logs
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Configurar permisos
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Copiar .env si no existe
if [ ! -f .env ]; then
    echo "Copiando .env.example a .env..."
    cp .env.example .env
fi

# Si tenemos variables del compose (Docker), sobrescribir en .env para que queue/redis usen el contenedor
if [ -n "${APP_KEY}" ]; then
    echo "Ajustando .env con variables del contenedor..."
    [ -n "${QUEUE_CONNECTION}" ] && sed -i "s/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=${QUEUE_CONNECTION}/" .env || true
    [ -n "${REDIS_HOST}" ] && sed -i "s/^REDIS_HOST=.*/REDIS_HOST=${REDIS_HOST}/" .env || true
    [ -n "${REDIS_PORT}" ] && sed -i "s/^REDIS_PORT=.*/REDIS_PORT=${REDIS_PORT}/" .env || true
    grep -q "^REDIS_CLIENT=" .env && sed -i "s/^REDIS_CLIENT=.*/REDIS_CLIENT=${REDIS_CLIENT:-predis}/" .env || echo "REDIS_CLIENT=predis" >> .env
    grep -q "^APP_KEY=" .env && sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env || echo "APP_KEY=${APP_KEY}" >> .env
fi

if [ -z "$(grep APP_KEY .env | cut -d '=' -f2)" ] || [ "$(grep APP_KEY .env | cut -d '=' -f2)" = "" ]; then
    echo "Generando clave de aplicación..."
    php artisan key:generate --force
fi

# Ejecutar migraciones (solo si no están ejecutadas)
echo "Ejecutando migraciones..."
php artisan migrate --force || true

# Limpiar cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Aplicación lista!"

# Ejecutar supervisor
exec "$@"
