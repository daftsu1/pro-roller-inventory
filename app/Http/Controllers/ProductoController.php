<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Producto::with('categoria', 'proveedor');

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('codigo', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        // Filtro por categoría
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Filtro por stock bajo
        if ($request->filled('stock_bajo')) {
            $query->whereColumn('stock_actual', '<=', 'stock_minimo');
        }

        $productos = $query->latest()->paginate(15);
        $categorias = Categoria::all();

        return view('productos.index', compact('productos', 'categorias'));
    }

    public function create()
    {
        $categorias = Categoria::all();
        $proveedores = Proveedor::all();
        return view('productos.create', compact('categorias', 'proveedores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|unique:productos,codigo|max:50',
            'nombre' => 'required|max:255',
            'descripcion' => 'nullable',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'categoria_id' => 'nullable|exists:categorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'activo' => 'boolean',
        ]);

        return DB::transaction(function () use ($validated) {
            // Crear el producto
            $producto = Producto::create($validated);

            // Si hay stock inicial, crear movimiento de entrada
            if ($validated['stock_actual'] > 0) {
                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'tipo' => 'entrada',
                    'cantidad' => $validated['stock_actual'],
                    'motivo' => "Stock inicial - Producto: {$producto->nombre}",
                    'usuario_id' => auth()->id(),
                    'fecha' => now(),
                ]);
            }

            return redirect()->route('productos.index')
                ->with('success', 'Producto creado exitosamente.' . ($validated['stock_actual'] > 0 ? ' Se registró el stock inicial como movimiento de entrada.' : ''));
        });
    }

    public function show(Producto $producto)
    {
        $producto->load('categoria', 'proveedor', 'movimientos.producto', 'detallesVenta.venta');
        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::all();
        $proveedores = Proveedor::all();
        return view('productos.edit', compact('producto', 'categorias', 'proveedores'));
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'codigo' => 'required|unique:productos,codigo,' . $producto->id . '|max:50',
            'nombre' => 'required|max:255',
            'descripcion' => 'nullable',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'categoria_id' => 'nullable|exists:categorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'activo' => 'boolean',
        ]);

        return DB::transaction(function () use ($producto, $validated) {
            // Guardar stock anterior
            $stockAnterior = $producto->stock_actual;
            $stockNuevo = $validated['stock_actual'];
            $diferencia = $stockNuevo - $stockAnterior;

            // Actualizar producto
            $producto->update($validated);

            // Si hay diferencia en el stock, crear movimiento de inventario
            if ($diferencia != 0) {
                $tipo = $diferencia > 0 ? 'entrada' : 'salida';
                $cantidad = abs($diferencia);
                
                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'tipo' => $tipo,
                    'cantidad' => $cantidad,
                    'motivo' => $diferencia > 0 
                        ? "Ajuste manual de stock (aumento) - Producto: {$producto->nombre}"
                        : "Ajuste manual de stock (disminución) - Producto: {$producto->nombre}",
                    'usuario_id' => auth()->id(),
                    'fecha' => now(),
                ]);
            }

            return redirect()->route('productos.index')
                ->with('success', 'Producto actualizado exitosamente.' . ($diferencia != 0 ? ' Se registró un movimiento de inventario.' : ''));
        });
    }

    public function destroy(Producto $producto)
    {
        // Verificar si tiene ventas o movimientos
        if ($producto->detallesVenta()->exists() || $producto->movimientos()->exists()) {
            return redirect()->route('productos.index')
                ->with('error', 'No se puede eliminar el producto porque tiene registros asociados.');
        }

        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }
}
