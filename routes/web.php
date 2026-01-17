<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\InformeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Productos
    Route::resource('productos', ProductoController::class);
    
    // Categorías
    Route::resource('categorias', CategoriaController::class);
    
    // Clientes
    Route::resource('clientes', ClienteController::class);
    
    // Proveedores
    Route::resource('proveedores', ProveedorController::class);
    
    // Ventas
    Route::resource('ventas', VentaController::class)->except(['edit', 'update', 'store']);
    Route::get('ventas/{venta}/editar', [VentaController::class, 'editar'])->name('ventas.editar');
    Route::post('ventas/{venta}/completar', [VentaController::class, 'completar'])->name('ventas.completar');
    Route::post('ventas/{venta}/cancelar', [VentaController::class, 'cancelar'])->name('ventas.cancelar');
    Route::post('ventas/{venta}/agregar-producto', [VentaController::class, 'agregarProducto'])->name('ventas.agregar-producto');
    Route::delete('ventas/{venta}/eliminar-producto', [VentaController::class, 'eliminarProducto'])->name('ventas.eliminar-producto');
    Route::put('ventas/{venta}/actualizar', [VentaController::class, 'actualizarVenta'])->name('ventas.actualizar');
    Route::post('ventas/{venta}/cerrar', [VentaController::class, 'cerrarVenta'])->name('ventas.cerrar');
    Route::get('ventas-filtrar', [VentaController::class, 'filtrar'])->name('ventas.filtrar');
    
    // Movimientos de inventario
    Route::resource('movimientos', MovimientoInventarioController::class)->except(['edit', 'update', 'destroy']);
    
    // Usuarios (solo para administradores)
    Route::resource('usuarios', UserController::class);
    
    // Informes
    Route::get('informes', [InformeController::class, 'index'])->name('informes.index');
    Route::get('informes/ventas', [InformeController::class, 'ventas'])->name('informes.ventas');
    Route::get('informes/productos-vendidos', [InformeController::class, 'productosVendidos'])->name('informes.productos-vendidos');
    Route::get('informes/stock-bajo', [InformeController::class, 'stockBajo'])->name('informes.stock-bajo');
    Route::get('informes/clientes', [InformeController::class, 'clientes'])->name('informes.clientes');
    Route::get('informes/resumen', [InformeController::class, 'resumen'])->name('informes.resumen');
});

require __DIR__.'/auth.php';
