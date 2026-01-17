# Guía de Instalación - pro roller

Esta guía te ayudará a instalar y configurar el sistema de inventario **pro roller** en tu entorno local.

## 📋 Requisitos Previos

- **XAMPP** instalado y funcionando (o servidor web con PHP)
- **PHP 8.1+** (viene incluido con XAMPP)
- **MySQL** funcionando en XAMPP
- **Composer** instalado globalmente ([descargar aquí](https://getcomposer.org/download/))

## 🔧 Pasos de Instalación

### 1. Verificar requisitos

Abre una terminal (PowerShell o CMD) y verifica que tienes todo instalado:

```bash
php -v
composer --version
```

Ambos comandos deben mostrar versiones instaladas.

### 2. Clonar o descargar el proyecto

Si clonaste desde Git:

```bash
git clone git@github.com:daftsu1/pro-roller-inventory.git
cd pro-roller-inventory
```

Si descargaste un ZIP, extrae el contenido en tu carpeta de htdocs (ej: `C:\xampp\htdocs\pro-roller-inventory`).

### 3. Instalar dependencias de Composer

```bash
composer install
```

⚠️ Esto puede tardar varios minutos la primera vez. Descargará Laravel y todas las dependencias necesarias.

### 4. Configurar el archivo .env

```bash
copy .env.example .env
php artisan key:generate
```

El comando `key:generate` creará una clave de aplicación única.

### 5. Configurar la base de datos

Abre el archivo `.env` y configura tu conexión a MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pro_roller
DB_USERNAME=root
DB_PASSWORD=
```

**Nota**: Si tu MySQL tiene contraseña, agrégala en `DB_PASSWORD`.

### 6. Crear la base de datos

Abre phpMyAdmin (`http://localhost/phpmyadmin`) o usa la línea de comandos de MySQL y crea la base de datos:

```sql
CREATE DATABASE pro_roller CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 7. Asegurar que MySQL está corriendo

Abre **XAMPP Control Panel** y verifica que MySQL esté en estado "Running" (verde). Si no, haz clic en "Start".

### 8. Publicar y ejecutar migraciones

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

Esto creará todas las tablas necesarias en la base de datos.

### 9. Ejecutar seeders (datos iniciales)

```bash
php artisan db:seed
```

Este comando creará:
- ✅ Usuarios: `admin@proroller.cl` y `vendedor@proroller.cl`
- ✅ Roles: Admin y Vendedor
- ✅ Categorías: Cortinas y Servicios
- ✅ Proveedores de ejemplo
- ✅ **77 productos roller duo** (gris, negro, blanco) con todas sus medidas

### 10. Iniciar el servidor

```bash
php artisan serve
```

Verás un mensaje como:
```
INFO  Server running on [http://127.0.0.1:8000]
```

### 11. Acceder al sistema

Abre tu navegador y ve a: `http://localhost:8000`

Inicia sesión con:
- **Email**: `admin@proroller.cl`
- **Password**: `password`

## ✅ Verificación de Instalación

Si todo está correcto, deberías ver:

1. ✅ Pantalla de login sin errores
2. ✅ Dashboard con métricas después de iniciar sesión
3. ✅ 77 productos en el módulo de Productos
4. ✅ Posibilidad de crear ventas en el módulo de Ventas

## 🐛 Solución de Problemas

### Error: "No se puede establecer una conexión a la base de datos"

**Causa**: MySQL no está corriendo en XAMPP.

**Solución**:
1. Abre XAMPP Control Panel
2. Haz clic en "Start" en la fila de MySQL
3. Verifica que muestre "Running" en verde
4. Reinicia el servidor de Laravel (`php artisan serve`)

### Error: "Class 'Spatie\Permission\...' not found"

**Causa**: Las dependencias no están instaladas o el autoload está desactualizado.

**Solución**:
```bash
composer install
composer dump-autoload -o
```

### Error: "The .env file does not exist"

**Causa**: No copiaste el archivo `.env.example` a `.env`.

**Solución**:
```bash
copy .env.example .env
php artisan key:generate
```

### Los productos no aparecen

**Causa**: Los seeders no se ejecutaron correctamente.

**Solución**:
```bash
php artisan db:seed
```

O para resetear todo y volver a empezar:
```bash
php artisan migrate:fresh --seed
```

⚠️ **CUIDADO**: `migrate:fresh` borra todas las tablas y datos existentes.

## 📝 Próximos Pasos

Después de instalar:

1. **Cambiar contraseñas**: Modifica las contraseñas de los usuarios por defecto
2. **Configurar proveedores**: Actualiza los datos de los proveedores de ejemplo
3. **Ajustar precios**: Configura los precios de compra y venta de los productos
4. **Agregar stock inicial**: Usa el módulo de Movimientos para agregar stock inicial a los productos

## 💡 Notas Importantes

- El archivo `.env` contiene información sensible. **NUNCA** lo subas a Git (ya está en `.gitignore`)
- Para producción, cambia `APP_DEBUG=false` en el archivo `.env`
- Siempre realiza backups de tu base de datos antes de actualizar el sistema

---

¡Listo! Tu sistema **pro roller** está instalado y funcionando. 🎉
