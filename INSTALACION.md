# Guía de Instalación Detallada

## Requisitos Previos

1. **XAMPP** instalado y funcionando
2. **Composer** instalado globalmente
3. **PHP 8.1+** (viene con XAMPP)
4. **MySQL** funcionando en XAMPP

## Pasos de Instalación

### Paso 1: Verificar PHP y Composer

```bash
php -v
composer --version
```

Ambos deben estar instalados y funcionando.

### Paso 2: Instalar Dependencias de Laravel

```bash
composer install
```

Esto descargará todas las dependencias necesarias.

### Paso 3: Configurar el Entorno

```bash
# En Windows
copy .env.example .env

# O crear manualmente el archivo .env con el contenido de .env.example
```

### Paso 4: Generar Clave de Aplicación

```bash
php artisan key:generate
```

### Paso 5: Configurar Base de Datos

1. Abre phpMyAdmin (http://localhost/phpmyadmin)
2. Crea una nueva base de datos llamada `joja_cola_inventario`
3. O ejecuta en MySQL:

```sql
CREATE DATABASE joja_cola_inventario CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Edita el archivo `.env` y verifica la configuración:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=joja_cola_inventario
DB_USERNAME=root
DB_PASSWORD=
```

### Paso 6: Publicar Migraciones de Spatie Permission

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

Esto creará las migraciones necesarias para roles y permisos.

### Paso 7: Ejecutar Migraciones

```bash
php artisan migrate
```

Esto creará todas las tablas en la base de datos.

### Paso 8: Ejecutar Seeders

```bash
php artisan db:seed
```

Esto creará:
- 4 usuarios de prueba (admin, inventario, vendedor, consulta)
- Roles y permisos
- Categorías y proveedores de ejemplo

### Paso 9: Iniciar el Servidor

```bash
php artisan serve
```

El sistema estará disponible en: **http://localhost:8000**

## Acceder al Sistema

1. Ve a http://localhost:8000
2. Inicia sesión con:
   - Email: `admin@joja-cola.com`
   - Password: `password`

## Solución de Problemas

### Error: "Class 'Spatie\Permission\PermissionServiceProvider' not found"

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### Error de conexión a la base de datos

1. Verifica que MySQL esté corriendo en XAMPP
2. Verifica las credenciales en `.env`
3. Verifica que la base de datos exista

### Error: "The stream or file could not be opened"

```bash
mkdir -p storage/logs
chmod -R 775 storage bootstrap/cache
```

### Limpiar cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

## Notas Adicionales

- Si usas XAMPP, asegúrate de que Apache y MySQL estén corriendo
- El puerto 8000 debe estar libre para `php artisan serve`
- Si necesitas cambiar el puerto: `php artisan serve --port=8001`
