<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\Producto;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        $this->crearPermisos();
        
        // Crear roles
        $this->crearRoles();
        
        // Crear usuarios
        $this->crearUsuarios();
        
        // Crear datos de ejemplo
        $this->crearDatosEjemplo();
    }

    private function crearPermisos()
    {
        $permisos = [
            // Productos
            'ver-productos',
            'crear-productos',
            'editar-productos',
            'eliminar-productos',
            
            // Inventario
            'ver-movimientos',
            'crear-movimientos',
            'ajustar-inventario',
            
            // Ventas
            'ver-ventas',
            'crear-ventas',
            'cancelar-ventas',
            
            // Usuarios (solo admin)
            'ver-usuarios',
            'crear-usuarios',
            'editar-usuarios',
            'eliminar-usuarios',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }
    }

    private function crearRoles()
    {
        // Admin - Todos los permisos
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());
        
        // Vendedor - Solo ventas
        $vendedor = Role::firstOrCreate(['name' => 'vendedor']);
        $vendedor->syncPermissions([
            'ver-productos',
            'ver-ventas', 'crear-ventas',
        ]);
    }

    private function crearUsuarios()
    {
        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@proroller.cl'],
            [
                'nombre' => 'Administrador',
                'password' => Hash::make('password'),
                'activo' => true,
            ]
        );
        $admin->assignRole('admin');
        
        // Vendedor
        $vendedor = User::firstOrCreate(
            ['email' => 'vendedor@proroller.cl'],
            [
                'nombre' => 'Vendedor',
                'password' => Hash::make('password'),
                'activo' => true,
            ]
        );
        $vendedor->assignRole('vendedor');
    }

    private function crearDatosEjemplo()
    {
        // Categorías
        $categorias = [
            ['nombre' => 'Cortinas', 'descripcion' => 'Cortinas y persianas'],
            ['nombre' => 'Servicios', 'descripcion' => 'Servicios diversos'],
        ];
        
        foreach ($categorias as $cat) {
            Categoria::firstOrCreate(
                ['nombre' => $cat['nombre']],
                ['descripcion' => $cat['descripcion']]
            );
        }
        
        // Proveedores
        $proveedores = [
            ['nombre' => 'Proveedor Principal', 'contacto' => 'Juan Pérez', 'telefono' => '555-0001', 'email' => 'contacto@proveedor.com'],
            ['nombre' => 'Distribuidora ABC', 'contacto' => 'María González', 'telefono' => '555-0002', 'email' => 'ventas@abc.com'],
        ];
        
        foreach ($proveedores as $prov) {
            Proveedor::firstOrCreate(
                ['nombre' => $prov['nombre']],
                $prov
            );
        }
        
        // Productos Roller Duo
        $this->crearProductosRollerDuo();
    }
    
    private function crearProductosRollerDuo()
    {
        $categoriaCortinas = Categoria::where('nombre', 'Cortinas')->first();
        $proveedorPrincipal = Proveedor::where('nombre', 'Proveedor Principal')->first();
        
        if (!$categoriaCortinas || !$proveedorPrincipal) {
            return;
        }
        
        // Medidas para altura 200
        $medidas200 = ['60x200', '80x200', '100x200', '120x200', '130x200', '140x200', '150x200', '160x200', '170x200', '180x200', '200x200', '220x200', '240x200'];
        
        // Medidas para altura 240
        $medidas240 = ['60x240', '80x240', '100x240', '120x240', '140x240', '150x240', '160x240', '170x240', '180x240', '200x240', '220x240', '240x240'];
        
        // Colores
        $colores = ['gris', 'negro', 'blanco'];
        
        foreach ($colores as $color) {
            // Agregar productos con altura 200
            foreach ($medidas200 as $medida) {
                $nombre = "roller duo {$color} {$medida}";
                $codigo = strtoupper(str_replace(' ', '-', $nombre));
                
                Producto::firstOrCreate(
                    ['nombre' => $nombre],
                    [
                        'codigo' => $codigo,
                        'descripcion' => "Cortina roller duo {$color} medida {$medida}",
                        'precio_compra' => 0,
                        'precio_venta' => 0,
                        'stock_actual' => 0,
                        'stock_minimo' => 5,
                        'categoria_id' => $categoriaCortinas->id,
                        'proveedor_id' => $proveedorPrincipal->id,
                        'activo' => true,
                    ]
                );
            }
            
            // Agregar productos con altura 240
            foreach ($medidas240 as $medida) {
                // Para negro, no incluir 130x240 según la tabla
                if ($color === 'negro' && $medida === '130x240') {
                    continue;
                }
                
                $nombre = "roller duo {$color} {$medida}";
                $codigo = strtoupper(str_replace(' ', '-', $nombre));
                
                Producto::firstOrCreate(
                    ['nombre' => $nombre],
                    [
                        'codigo' => $codigo,
                        'descripcion' => "Cortina roller duo {$color} medida {$medida}",
                        'precio_compra' => 0,
                        'precio_venta' => 0,
                        'stock_actual' => 0,
                        'stock_minimo' => 5,
                        'categoria_id' => $categoriaCortinas->id,
                        'proveedor_id' => $proveedorPrincipal->id,
                        'activo' => true,
                    ]
                );
            }
        }
    }
}
