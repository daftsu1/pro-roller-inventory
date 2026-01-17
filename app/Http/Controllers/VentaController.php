<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Por defecto mostrar pendientes (las nuevas ventas)
        $estadoFiltro = $request->get('estado', 'pendiente');
        
        $query = Venta::with('usuario'); // Removemos detalles.producto para mejor rendimiento

        // Filtros
        if ($request->filled('numero_factura')) {
            $query->where('numero_factura', 'like', "%{$request->numero_factura}%");
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        // Filtrar por estado
        if ($request->filled('estado') && $request->estado !== 'todas') {
            $query->where('estado', $request->estado);
        } elseif (!$request->filled('estado')) {
            // Por defecto mostrar pendientes (las nuevas ventas)
            $query->where('estado', 'pendiente');
        }

        // Optimización: solo cargar lo necesario
        $ventas = $query->select('ventas.*')
            ->latest('fecha')
            ->latest('id')
            ->paginate(20)
            ->withQueryString(); // Mantener filtros en la paginación
        
        // Ventas pendientes (para el contador)
        $ventasPendientes = Venta::where('estado', 'pendiente')->count();
        
        // Productos para el modal de venta
        $productos = Producto::where('activo', true)
            ->where('stock_actual', '>', 0)
            ->orderBy('nombre')
            ->get();
        
        // Clientes para búsqueda en el modal de venta
        $clientes = \App\Models\Cliente::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'documento', 'telefono', 'email']);

        return view('ventas.index', compact('ventas', 'productos', 'clientes', 'ventasPendientes', 'estadoFiltro'));
    }

    public function create()
    {
        // Crear venta pendiente vacía inmediatamente
        $ultimaVenta = Venta::latest('id')->first();
        $numeroFactura = 'VENT-' . str_pad(($ultimaVenta?->id ?? 0) + 1, 6, '0', STR_PAD_LEFT);
        
        $venta = Venta::create([
            'numero_factura' => $numeroFactura,
            'fecha' => now(),
            'total' => 0,
            'usuario_id' => auth()->id(),
            'estado' => 'pendiente',
        ]);

        // Redirigir al modal de edición de esta venta
        return redirect()->route('ventas.index', ['editar_venta' => $venta->id])
            ->with('venta_activa', $venta->id);
    }

    public function editar(Venta $venta)
    {
        // Cargar venta con detalles para el modal
        $venta->load('detalles.producto');
        // Productos para autocompletado (solo campos necesarios)
        $productos = Producto::where('activo', true)
            ->where('stock_actual', '>', 0)
            ->select('id', 'codigo', 'nombre', 'precio_venta', 'stock_actual')
            ->orderBy('nombre')
            ->get();
        
        return response()->json([
            'venta' => $venta,
            'productos' => $productos
        ]);
    }

    public function agregarProducto(Request $request, Venta $venta)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        try {
            $producto = Producto::findOrFail($validated['producto_id']);
            
            // Calcular stock disponible (stock actual - cantidad en otras ventas pendientes)
            $cantidadEnVentasPendientes = \App\Models\DetalleVenta::where('producto_id', $producto->id)
                ->whereHas('venta', function($q) use ($venta) {
                    $q->where('estado', 'pendiente')
                      ->where('id', '!=', $venta->id); // Excluir la venta actual
                })
                ->sum('cantidad');
            
            $stockDisponible = $producto->stock_actual - $cantidadEnVentasPendientes;
            
            // Verificar si ya existe el producto en la venta
            $detalleExistente = $venta->detalles()->where('producto_id', $producto->id)->first();
            
            // Calcular cantidad total que tendría este producto en la venta
            $cantidadTotalEnVenta = ($detalleExistente ? $detalleExistente->cantidad : 0) + $validated['cantidad'];
            
            // Validar stock disponible
            if ($cantidadTotalEnVenta > $stockDisponible) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock insuficiente. Stock disponible: {$stockDisponible} (considerando otras ventas pendientes)",
                    'stock_disponible' => $stockDisponible,
                    'stock_actual' => $producto->stock_actual
                ], 400);
            }
            
            if ($detalleExistente) {
                // Actualizar cantidad
                $detalleExistente->increment('cantidad', $validated['cantidad']);
                $detalleExistente->update([
                    'subtotal' => $detalleExistente->cantidad * $producto->precio_venta
                ]);
            } else {
                // Crear nuevo detalle
                $venta->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $validated['cantidad'],
                    'precio_unitario' => $producto->precio_venta,
                    'subtotal' => $validated['cantidad'] * $producto->precio_venta,
                ]);
            }

            // Recalcular total
            $total = $venta->fresh()->detalles->sum('subtotal');
            $venta->update(['total' => $total]);

            return response()->json([
                'success' => true,
                'venta' => $venta->fresh()->load('detalles.producto'),
                'total' => number_format($venta->total, 2),
                'stock_disponible' => $stockDisponible - $cantidadTotalEnVenta, // Stock restante después de agregar
                'warning' => $stockDisponible - $cantidadTotalEnVenta < 5 ? 'Queda poco stock disponible después de agregar este producto' : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function eliminarProducto(Request $request, Venta $venta)
    {
        // Leer detalle_id del JSON o del input
        $detalleId = $request->input('detalle_id');
        
        if (!$detalleId) {
            // Intentar leer del JSON body
            $json = $request->json()->all();
            $detalleId = $json['detalle_id'] ?? null;
        }
        
        if (!$detalleId) {
            return response()->json([
                'success' => false,
                'message' => 'detalle_id es requerido'
            ], 400);
        }

        try {
            $detalle = $venta->detalles()->findOrFail($detalleId);
            $detalle->delete();

            // Recalcular total
            $total = $venta->fresh()->detalles->sum('subtotal');
            $venta->update(['total' => $total]);

            return response()->json([
                'success' => true,
                'venta' => $venta->fresh()->load('detalles.producto'),
                'total' => number_format($venta->total, 2)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function actualizarVenta(Request $request, Venta $venta)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'cliente_id' => 'nullable|exists:clientes,id',
            'cliente_nombre' => 'nullable|string|max:255',
            'cliente_documento' => 'nullable|string|max:50',
        ]);

        // Si hay cliente_id, usarlo y mantener compatibilidad con campos manuales
        $updateData = [
            'fecha' => $validated['fecha'],
            'cliente_nombre' => $validated['cliente_nombre'] ?? null,
            'cliente_documento' => $validated['cliente_documento'] ?? null,
        ];
        
        // Si se proporciona cliente_id, usarlo; si es null, limpiar la relación
        if (isset($validated['cliente_id'])) {
            $updateData['cliente_id'] = $validated['cliente_id'];
        }

        $venta->update($updateData);

        return response()->json([
            'success' => true,
            'venta' => $venta->fresh()
        ]);
    }

    public function cerrarVenta(Venta $venta)
    {
        // Cerrar el modal y redirigir
        return redirect()->route('ventas.index', ['estado' => 'pendiente'])
            ->with('success', 'Venta #' . $venta->numero_factura . ' guardada como pendiente. Puedes completarla cuando esté lista.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'cliente_nombre' => 'nullable|string|max:255',
            'cliente_documento' => 'nullable|string|max:50',
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        try {
            $venta = Venta::crearConMovimientos([
                'fecha' => $validated['fecha'],
                'cliente_nombre' => $validated['cliente_nombre'] ?? null,
                'cliente_documento' => $validated['cliente_documento'] ?? null,
                'productos' => $validated['productos'],
                'estado' => 'pendiente', // Siempre crear como pendiente
            ]);

            // Cerrar modal y redirigir
            return redirect()->route('ventas.index')
                ->with('success', 'Venta creada como pendiente (Factura: ' . $venta->numero_factura . '). Recuerda completarla para descontar el stock.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la venta: ' . $e->getMessage());
        }
    }

    public function show(Venta $venta)
    {
        $venta->load('usuario', 'detalles.producto', 'movimientos');
        return view('ventas.show', compact('venta'));
    }

    public function completar(Venta $venta)
    {
        try {
            $venta->completar();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Venta completada exitosamente. El stock ha sido descontado.',
                    'venta' => $venta->fresh()->load('detalles.producto')
                ]);
            }
            
            return redirect()->route('ventas.index', ['estado' => 'pendiente'])
                ->with('success', 'Venta #' . $venta->numero_factura . ' completada exitosamente. El stock ha sido descontado.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            
            return redirect()->back()
                ->with('error', 'Error al completar la venta: ' . $e->getMessage());
        }
    }

    public function cancelar(Venta $venta)
    {
        try {
            $venta->cancelar();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Venta cancelada exitosamente. El stock ha sido devuelto.',
                    'venta' => $venta->fresh()->load('detalles.producto')
                ]);
            }
            
            return redirect()->route('ventas.index', ['estado' => 'completada'])
                ->with('success', 'Venta #' . $venta->numero_factura . ' cancelada exitosamente. El stock ha sido devuelto.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            
            return redirect()->back()
                ->with('error', 'Error al cancelar la venta: ' . $e->getMessage());
        }
    }

    public function destroy(Venta $venta)
    {
        // Solo permitir eliminar ventas pendientes
        if ($venta->estado !== 'pendiente') {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar ventas pendientes'
                ], 400);
            }
            
            return redirect()->route('ventas.index')
                ->with('error', 'Solo se pueden eliminar ventas pendientes.');
        }

        try {
            $numeroFactura = $venta->numero_factura;
            
            // Eliminar detalles de venta (cascade debería hacerlo automáticamente, pero por si acaso)
            $venta->detalles()->delete();
            
            // Eliminar la venta
            $venta->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Venta eliminada exitosamente.'
                ]);
            }

            return redirect()->route('ventas.index', ['estado' => 'pendiente'])
                ->with('success', 'Venta #' . $numeroFactura . ' eliminada exitosamente.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la venta: ' . $e->getMessage()
                ], 400);
            }

            return redirect()->back()
                ->with('error', 'Error al eliminar la venta: ' . $e->getMessage());
        }
    }

    public function filtrar(Request $request)
    {
        $query = Venta::with('usuario');

        // Filtros
        if ($request->filled('numero_factura')) {
            $query->where('numero_factura', 'like', "%{$request->numero_factura}%");
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        if ($request->filled('estado') && $request->estado !== 'todas') {
            $query->where('estado', $request->estado);
        } elseif (!$request->filled('estado')) {
            $query->where('estado', 'pendiente');
        }

        $ventas = $query->select('ventas.*')
            ->latest('fecha')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        if ($request->ajax()) {
            return view('ventas._tabla', compact('ventas'))->render();
        }

        return redirect()->route('ventas.index', $request->all());
    }
}
