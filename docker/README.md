# Docker - Guía de Despliegue en VPS

Esta guía te ayudará a desplegar la aplicación Laravel en un VPS usando Docker.

## Requisitos Previos

- Docker instalado (versión 20.10 o superior)
- Docker Compose instalado (versión 2.0 o superior)
- Git instalado

## 🚀 Opción 1: Producción con Traefik (ya instalado en el VPS)

Si **ya tenés Traefik** configurado, la app solo se conecta a tu red Traefik y se expone por labels.

Ver: **[docker/TRAEFIK-SETUP.md](TRAEFIK-SETUP.md)**

Resumen: crear la red `traefik` si no existe, poner tu dominio en las labels de `docker-compose.production.yml`, configurar `.env` y levantar:

```bash
docker-compose -f docker-compose.production.yml up -d --build
```

## Opción 2: Docker Compose sin Traefik (desarrollo/pruebas)

### 1. Clonar el repositorio en el VPS

```bash
git clone <tu-repositorio> pro-roller-inventory
cd pro-roller-inventory
```

### 2. Configurar variables de entorno

Crea un archivo `.env` basado en `.env.example`:

```bash
cp .env.example .env
```

Edita el archivo `.env` con tus configuraciones:

```env
APP_NAME="Sistema de Inventario"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=pro_roller
DB_USERNAME=root
DB_PASSWORD=tu_password_seguro

# Otros ajustes según necesites
```

### 3. Construir y levantar los contenedores

```bash
docker-compose up -d --build
```

### 4. Ejecutar migraciones y seeders (si es necesario)

```bash
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force
```

### 5. Acceder a la aplicación

La aplicación estará disponible en `http://tu-vps-ip:8000`

## Opción 3: Usar Docker directamente (Sin Docker Compose)

### 1. Construir la imagen

```bash
docker build -t pro-roller-app -f docker/Dockerfile.production .
```

### 2. Ejecutar el contenedor

```bash
docker run -d \
  --name pro_roller_app \
  -p 8000:80 \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e DB_HOST=tu_host_db \
  -e DB_DATABASE=pro_roller \
  -e DB_USERNAME=tu_usuario \
  -e DB_PASSWORD=tu_password \
  --restart unless-stopped \
  pro-roller-app
```

## Comandos Útiles

### Ver logs
```bash
docker-compose logs -f app
```

### Ejecutar comandos Artisan
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:cache
```

### Acceder al contenedor
```bash
docker-compose exec app sh
```

### Detener los contenedores
```bash
docker-compose down
```

### Reiniciar los contenedores
```bash
docker-compose restart
```

## 🔒 Seguridad en Producción

### Con Traefik (Recomendado)
- SSL/HTTPS automático con Let's Encrypt
- Redirección automática HTTP → HTTPS
- Headers de seguridad configurados
- Base de datos no expuesta públicamente

### Sin Traefik
Si no usas Traefik, configura Nginx como reverse proxy y SSL manualmente. Ver sección anterior.

## Solución de Problemas

### Error de permisos en storage
```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Limpiar cache
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
```

### Ver logs de errores
```bash
docker-compose exec app tail -f storage/logs/laravel.log
```

## Backup de Base de Datos

```bash
docker-compose exec db mysqldump -u root -p pro_roller > backup_$(date +%Y%m%d).sql
```

## Restaurar Base de Datos

```bash
docker-compose exec -T db mysql -u root -p pro_roller < backup_20260216.sql
```
