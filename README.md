# pro roller - Sistema de Inventario

Sistema de gestión de inventario especializado en productos roller duo (cortinas). Gestiona productos, ventas, clientes, proveedores, categorías y reportes.

## 🚀 Características

- ✅ **Gestión de Productos**: Control completo de productos roller duo con medidas y colores
- ✅ **Control de Inventario**: Movimientos de entrada/salida con registro detallado
- ✅ **Módulo de Ventas**: Ventas con modal, búsqueda de productos, clientes y control de stock
- ✅ **Gestión de Clientes**: Registro de clientes con historial de ventas
- ✅ **Gestión de Proveedores**: Administración de proveedores
- ✅ **Categorías**: Organización de productos por categorías
- ✅ **Sistema de Roles**: Admin y Vendedor con permisos diferenciados
- ✅ **Dashboard**: Métricas y resumen de actividad
- ✅ **Reportes**: Ventas, productos vendidos, stock bajo, clientes y resumen general
- ✅ **Diseño Responsive**: Adaptado para dispositivos móviles y tablets

## 📋 Requisitos

- PHP 8.1 o superior
- Composer (gestor de dependencias de PHP)
- MySQL/MariaDB
- XAMPP (recomendado para entorno local) o servidor web con PHP

## 🔧 Instalación

### 1. Clonar el repositorio

```bash
git clone git@github.com:daftsu1/pro-roller-inventory.git
cd pro-roller-inventory
```

### 2. Instalar dependencias

```bash
composer install
```

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
DB_DATABASE=pro_roller
DB_USERNAME=root
DB_PASSWORD=
```

Crea la base de datos en MySQL:

```sql
CREATE DATABASE pro_roller CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Publicar y ejecutar migraciones

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### 6. Ejecutar seeders (datos iniciales)

```bash
php artisan db:seed
```

Este comando creará:
- Usuarios iniciales (admin y vendedor)
- Roles y permisos básicos
- Categorías (Cortinas y Servicios)
- Proveedores de ejemplo
- **77 productos roller duo** (gris, negro, blanco) con todas sus medidas

### 7. Iniciar servidor

```bash
php artisan serve
```

El sistema estará disponible en: `http://localhost:8000`

## 👤 Usuarios por defecto

Después de ejecutar los seeders, puedes iniciar sesión con:

**Administrador:**
- Email: `admin@proroller.cl`
- Password: `password`

**Vendedor:**
- Email: `vendedor@proroller.cl`
- Password: `password`

⚠️ **Importante**: Cambia las contraseñas después de la primera instalación.

## 📦 Estructura del Proyecto

```
app/
├── Http/Controllers/    # Controladores (Ventas, Productos, Clientes, etc.)
├── Models/              # Modelos Eloquent
├── Http/Middleware/     # Middleware de autenticación y permisos
database/
├── migrations/          # Migraciones de BD
└── seeders/            # Seeders (DatabaseSeeder con productos roller duo)
resources/
└── views/              # Vistas Blade
    ├── ventas/         # Módulo de ventas
    ├── productos/      # Gestión de productos
    ├── clientes/       # Gestión de clientes
    ├── proveedores/    # Gestión de proveedores
    ├── categorias/     # Gestión de categorías
    ├── informes/       # Reportes
    └── layouts/        # Plantillas base
```

## 🔐 Roles y Permisos

El sistema incluye dos roles principales:

- **Admin**: 
  - Acceso completo al sistema
  - Gestión de usuarios, productos, ventas, clientes, proveedores
  - Acceso a reportes y movimientos de inventario
  
- **Vendedor**: 
  - Ver productos y crear ventas
  - Consulta de información (sin edición)

## 🎯 Funcionalidades Principales

### Ventas
- Creación de ventas pendientes con modal
- Búsqueda de productos por código o nombre
- Búsqueda y asociación de clientes
- Control de stock en tiempo real
- Completar, cancelar y eliminar ventas
- Prevención de condiciones de carrera en el inventario

### Productos
- 77 productos roller duo pre-cargados (gris, negro, blanco)
- Búsqueda por código o nombre
- Control de stock mínimo
- Movimientos de inventario automáticos
- Precios de compra y venta

### Clientes
- Registro mediante modal
- Búsqueda por nombre, documento o teléfono
- Historial de ventas asociado
- Información de contacto

### Reportes
- Reporte de ventas por rango de fechas
- Productos más vendidos
- Productos con stock bajo
- Reporte de clientes
- Resumen general del sistema

## 📝 Tecnologías Utilizadas

- **Laravel 10**: Framework PHP
- **Blade**: Motor de plantillas
- **Bootstrap 5**: Framework CSS responsive
- **MySQL**: Base de datos
- **Spatie Laravel Permission**: Gestión de roles y permisos
- **Bootstrap Icons**: Iconografía

## 🛠️ Comandos útiles

```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Resetear base de datos (¡CUIDADO! Borra todos los datos)
php artisan migrate:fresh --seed

# Regenerar autoload
composer dump-autoload

# Crear nuevo controlador
php artisan make:controller NombreController

# Crear nueva migración
php artisan make:migration nombre_migracion

# Crear nuevo modelo
php artisan make:model NombreModelo -m
```

## 📄 Licencia

MIT

## 👥 Contribuciones

Este es un proyecto privado. Para sugerencias o reportar problemas, contacta al administrador del repositorio.
