<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimientoInventarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = MovimientoInventario::with('producto', 'usuario', 'venta');

        // Filtros
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        // Filtrar por origen
        if ($request->filled('origen')) {
            if ($request->origen == 'ventas') {
                $query->whereNotNull('venta_id');
            } elseif ($request->origen == 'manual') {
                $query->whereNull('venta_id');
            }
        }

        $movimientos = $query->latest('fecha')->latest('id')->paginate(20);
        // Productos activos para el buscador (solo campos necesarios)
        $productos = Producto::where('activo', true)
            ->select('id', 'codigo', 'nombre', 'stock_actual')
            ->orderBy('nombre')
            ->get();

        return view('movimientos.index', compact('movimientos', 'productos'));
    }

    public function create()
    {
        $productos = Producto::where('activo', true)->orderBy('nombre')->get();
        return view('movimientos.create', compact('productos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'tipo' => 'required|in:entrada,salida',
            'cantidad' => 'required|numeric|min:0.01',
            'motivo' => 'required|string|max:255',
            'fecha' => 'required|date',
            'referencia' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $producto = Producto::findOrFail($validated['producto_id']);

            // Validar stock si es salida
            if ($validated['tipo'] === 'salida' && $producto->stock_actual < $validated['cantidad']) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock insuficiente. Stock actual: ' . $producto->stock_actual,
                        'errors' => ['cantidad' => ['Stock insuficiente. Stock actual: ' . $producto->stock_actual]]
                    ], 422);
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Stock insuficiente. Stock actual: ' . $producto->stock_actual);
            }

            // Crear movimiento
            $movimiento = MovimientoInventario::create([
                'producto_id' => $validated['producto_id'],
                'tipo' => $validated['tipo'],
                'cantidad' => $validated['cantidad'],
                'motivo' => $validated['motivo'],
                'usuario_id' => auth()->id(),
                'fecha' => $validated['fecha'],
                'referencia' => $validated['referencia'] ?? null,
            ]);

            // Actualizar stock
            if ($validated['tipo'] === 'entrada') {
                $producto->increment('stock_actual', $validated['cantidad']);
            } else {
                $producto->decrement('stock_actual', $validated['cantidad']);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Movimiento registrado exitosamente.',
                    'movimiento' => $movimiento->load('producto', 'usuario')
                ]);
            }

            return redirect()->route('movimientos.index')
                ->with('success', 'Movimiento registrado exitosamente.');
        });
    }

    public function show(MovimientoInventario $movimiento)
    {
        $movimiento->load('producto', 'usuario', 'venta', 'detalleVenta');
        
        if (request()->expectsJson() || request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'movimiento' => [
                    'id' => $movimiento->id,
                    'fecha' => $movimiento->fecha->format('d/m/Y'),
                    'tipo' => $movimiento->tipo,
                    'cantidad' => $movimiento->cantidad,
                    'motivo' => $movimiento->motivo,
                    'referencia' => $movimiento->referencia ?? '-',
                    'usuario' => $movimiento->usuario->nombre ?? '-',
                    'venta_id' => $movimiento->venta_id,
                    'venta_numero_factura' => $movimiento->venta->numero_factura ?? null,
                    'producto' => [
                        'id' => $movimiento->producto->id,
                        'codigo' => $movimiento->producto->codigo,
                        'nombre' => $movimiento->producto->nombre,
                        'stock_actual' => $movimiento->producto->stock_actual,
                        'stock_minimo' => $movimiento->producto->stock_minimo,
                        'tiene_stock_minimo' => $movimiento->producto->tieneStockMinimo()
                    ]
                ]
            ]);
        }
        
        return view('movimientos.show', compact('movimiento'));
    }
}
