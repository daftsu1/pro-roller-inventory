# Sistema de Inventario - Joja Cola

Sistema de inventario que gestiona entradas, salidas y ventas de productos.

## 🚀 Características

- ✅ Gestión de productos
- ✅ Control de inventario (entradas/salidas)
- ✅ Módulo de ventas
- ✅ Sistema de roles y permisos
- ✅ Dashboard con métricas
- ✅ Reportes básicos

## 📋 Requisitos

- PHP 8.1 o superior
- **Composer** (gestor de dependencias de PHP)
  - ⚠️ Si no lo tienes instalado, ve a [INSTALAR_COMPOSER.md](INSTALAR_COMPOSER.md)
- MySQL/MariaDB
- XAMPP (para entorno local)

## 🔧 Instalación

### 0. Verificar requisitos

Asegúrate de tener Composer instalado:

```bash
composer --version
```

Si no está instalado, sigue la guía: [INSTALAR_COMPOSER.md](INSTALAR_COMPOSER.md)

### 1. Navegar al proyecto

```bash
cd f:\xamp\htdocs\joja-cola
```

### 2. Instalar dependencias

```bash
composer install
```

Esto descargará Laravel y todas las dependencias necesarias (puede tardar varios minutos).

### 3. Configurar entorno

```bash
copy .env.example .env
php artisan key:generate
```

### 4. Configurar base de datos

Edita el archivo `.env` y configura tu conexión a MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=joja_cola_inventario
DB_USERNAME=root
DB_PASSWORD=
```

Crea la base de datos en MySQL:
```sql
CREATE DATABASE joja_cola_inventario;
```

### 5. Publicar migraciones de Spatie Permission

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### 6. Ejecutar seeders

```bash
php artisan db:seed
```

Este comando creará:
- Usuarios iniciales (admin, vendedor, inventario, consulta)
- Roles y permisos básicos
- Categorías y proveedores de ejemplo

### 7. Iniciar servidor

```bash
php artisan serve
```

El sistema estará disponible en: `http://localhost:8000`

## 👤 Usuarios por defecto

Después de ejecutar los seeders, puedes iniciar sesión con:

**Administrador:**
- Email: `admin@joja-cola.com`
- Password: `password`

**Vendedor:**
- Email: `vendedor@joja-cola.com`
- Password: `password`

**Inventario:**
- Email: `inventario@joja-cola.com`
- Password: `password`

**Consulta:**
- Email: `consulta@joja-cola.com`
- Password: `password`

## 📦 Estructura del Proyecto

```
app/
├── Http/Controllers/    # Controladores
├── Models/              # Modelos Eloquent
├── Services/            # Servicios de lógica de negocio
database/
├── migrations/          # Migraciones de BD
├── seeders/            # Seeders para datos iniciales
resources/
└── views/              # Vistas Blade
```

## 🔐 Roles y Permisos

El sistema incluye los siguientes roles:

- **Admin**: Acceso completo
- **Inventario**: Gestión de productos e inventario
- **Vendedor**: Solo ventas y consulta de productos
- **Consulta**: Solo lectura

## 📝 Notas

- El sistema usa **Laravel 10** con **Blade**
- **Spatie Laravel Permission** para roles y permisos
- **Bootstrap 5** para estilos
- Diseño responsive

## 🛠️ Comandos útiles

```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Crear nuevo controlador
php artisan make:controller NombreController

# Crear nueva migración
php artisan make:migration nombre_migracion

# Crear nuevo modelo
php artisan make:model NombreModelo -m
```

## 📄 Licencia

MIT
