<?php

namespace App\Http\Controllers;

use App\Models\ConteoFisico;
use App\Models\DetalleConteo;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConteoFisicoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = ConteoFisico::with('usuario')
            ->withCount('detalles')
            ->withCount([
                'detalles as escaneados_count' => function ($q) {
                    $q->where('escaneado', true);
                },
                'detalles as diferencias_count' => function ($q) {
                    $q->where('diferencia', '!=', 0);
                },
            ]);

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_conteo', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_conteo', '<=', $request->fecha_hasta);
        }

        $conteos = $query->latest('fecha_conteo')->latest('id')->paginate(15);

        return view('conteos.index', compact('conteos'));
    }

    public function create()
    {
        $categorias = Categoria::with('productos')->whereHas('productos', function($q) {
            $q->where('activo', true)->where('stock_actual', '>', 0);
        })->get();
        
        // Solo productos activos con stock > 0
        $productos = Producto::where('activo', true)
            ->where('stock_actual', '>', 0)
            ->select('id', 'codigo', 'nombre', 'stock_actual', 'categoria_id')
            ->orderBy('nombre')
            ->get();

        return view('conteos.create', compact('categorias', 'productos'));
    }

    public function store(Request $request)
    {
        // Validación personalizada según el tipo de selección
        $rules = [
            'nombre' => 'nullable|string|max:255', // Ahora es opcional
            'descripcion' => 'nullable|string',
            'fecha_conteo' => 'required|date',
            'tipo_seleccion' => 'required|in:todos,categoria,manual',
        ];
        
        // Agregar reglas según el tipo de selección
        if ($request->tipo_seleccion === 'categoria') {
            $rules['categoria_id'] = 'required|exists:categorias,id';
        }
        
        if ($request->tipo_seleccion === 'manual') {
            $rules['productos'] = 'required|array|min:1';
            $rules['productos.*'] = 'required|exists:productos,id';
        }
        
        try {
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Por favor corrija los errores en el formulario.');
        }

        try {
            return DB::transaction(function () use ($validated) {
            // Generar nombre automático si no se proporciona
            $nombre = $validated['nombre'] ?? null;
            if (empty($nombre)) {
                $fechaFormateada = \Carbon\Carbon::parse($validated['fecha_conteo'])->format('d/m/Y');
                $nombre = "Conteo Físico - {$fechaFormateada}";
            }
            
            // Crear el conteo físico
            $conteo = ConteoFisico::create([
                'nombre' => $nombre,
                'descripcion' => $validated['descripcion'] ?? null,
                'fecha_conteo' => $validated['fecha_conteo'],
                'estado' => 'pendiente',
                'usuario_id' => auth()->id(),
            ]);

            // Determinar qué productos incluir (SOLO con stock > 0)
            $productos = collect();
            
            if ($validated['tipo_seleccion'] === 'todos') {
                $productos = Producto::where('activo', true)
                    ->where('stock_actual', '>', 0)
                    ->get();
            } elseif ($validated['tipo_seleccion'] === 'categoria') {
                $productos = Producto::where('activo', true)
                    ->where('stock_actual', '>', 0)
                    ->where('categoria_id', $validated['categoria_id'])
                    ->get();
            } elseif ($validated['tipo_seleccion'] === 'manual') {
                if (empty($validated['productos']) || !is_array($validated['productos'])) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Debe seleccionar al menos un producto para el conteo.');
                }
                $productos = Producto::where('activo', true)
                    ->where('stock_actual', '>', 0)
                    ->whereIn('id', $validated['productos'])
                    ->get();
            }

            // Validar que haya productos para crear el conteo
            if ($productos->isEmpty()) {
                // Eliminar el conteo creado si no hay productos
                $conteo->delete();
                
                $mensaje = match($validated['tipo_seleccion']) {
                    'todos' => 'No hay productos activos con stock disponible para crear el conteo.',
                    'categoria' => 'La categoría seleccionada no tiene productos activos con stock disponible.',
                    'manual' => 'Los productos seleccionados no tienen stock disponible. Solo se incluyen productos con stock > 0.',
                    default => 'No hay productos con stock disponible para crear el conteo.'
                };
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', $mensaje);
            }

            // Crear detalles del conteo con snapshot del stock actual
            foreach ($productos as $producto) {
                DetalleConteo::create([
                    'conteo_fisico_id' => $conteo->id,
                    'producto_id' => $producto->id,
                    'cantidad_sistema' => $producto->stock_actual,
                    'cantidad_fisica' => 0,
                    'diferencia' => -$producto->stock_actual,
                    'escaneado' => false,
                ]);
            }

            return redirect()->route('conteos.show', $conteo)
                ->with('success', 'Conteo físico creado exitosamente con ' . $productos->count() . ' producto(s). Puede comenzar a escanear productos.');
            });
        } catch (\Exception $e) {
            \Log::error('Error al crear conteo físico: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el conteo físico: ' . $e->getMessage());
        }
    }

    public function show(ConteoFisico $conteo)
    {
        $conteo->load('usuario', 'detalles.producto.categoria');
        
        // Verificar si se pueden aplicar ajustes
        $tieneAjustesAplicados = MovimientoInventario::where('referencia', "Conteo #{$conteo->id}")->exists();
        $puedeEditar = !$tieneAjustesAplicados && $conteo->estado !== 'cancelado';
        
        // Productos que ya están en el conteo
        $productosEnConteo = $conteo->detalles->pluck('producto_id')->toArray();
        
        // Productos disponibles para agregar (que no estén ya en el conteo)
        // Incluir TODOS los productos activos (incluso sin stock) para poder agregarlos manualmente
        $productosDisponibles = Producto::where('activo', true)
            ->whereNotIn('id', $productosEnConteo)
            ->select('id', 'codigo', 'nombre', 'stock_actual')
            ->orderBy('nombre')
            ->get();
        
        $estadisticas = [
            'total_productos' => $conteo->detalles()->count(),
            'productos_escaneados' => $conteo->detalles()->where('escaneado', true)->count(),
            'con_diferencias' => $conteo->detalles()->where('diferencia', '!=', 0)->count(),
            'sin_diferencias' => $conteo->detalles()->where('diferencia', 0)->count(),
        ];

        return view('conteos.show', compact('conteo', 'estadisticas', 'productosDisponibles', 'puedeEditar'));
    }
    
    public function agregarProductos(Request $request, ConteoFisico $conteo)
    {
        // Verificar si se pueden aplicar ajustes
        $tieneAjustesAplicados = MovimientoInventario::where('referencia', "Conteo #{$conteo->id}")->exists();
        
        if ($conteo->estado === 'cancelado' || $tieneAjustesAplicados) {
            return redirect()->route('conteos.show', $conteo)
                ->with('error', 'No se puede editar este conteo. Ya se aplicaron los ajustes o está cancelado.');
        }
        
        $validated = $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*' => 'required|exists:productos,id',
        ]);
        
        return DB::transaction(function () use ($conteo, $validated) {
            // Productos que ya están en el conteo
            $productosEnConteo = $conteo->detalles->pluck('producto_id')->toArray();
            
            // Filtrar solo los productos que no están ya en el conteo
            $productosNuevos = array_diff($validated['productos'], $productosEnConteo);
            
            if (empty($productosNuevos)) {
                return redirect()->route('conteos.show', $conteo)
                    ->with('error', 'Los productos seleccionados ya están en el conteo.');
            }
            
            // Permitir agregar cualquier producto activo (incluso sin stock)
            $productos = Producto::where('activo', true)
                ->whereIn('id', $productosNuevos)
                ->get();
            
            $agregados = 0;
            foreach ($productos as $producto) {
                // Verificar que no exista ya
                if (!DetalleConteo::where('conteo_fisico_id', $conteo->id)
                    ->where('producto_id', $producto->id)
                    ->exists()) {
                    DetalleConteo::create([
                        'conteo_fisico_id' => $conteo->id,
                        'producto_id' => $producto->id,
                        'cantidad_sistema' => $producto->stock_actual,
                        'cantidad_fisica' => 0,
                        'diferencia' => -$producto->stock_actual,
                        'escaneado' => false,
                    ]);
                    $agregados++;
                }
            }
            
            // Si está finalizado pero sin ajustes, volver a "en_proceso" para permitir edición
            if ($conteo->estado === 'finalizado' && !$tieneAjustesAplicados) {
                $conteo->update(['estado' => 'en_proceso']);
            }
            
            return redirect()->route('conteos.show', $conteo)
                ->with('success', "Se agregaron {$agregados} producto(s) al conteo.");
        });
    }

    public function escanear(ConteoFisico $conteo)
    {
        // Permitir escanear si está finalizado PERO aún no se han aplicado ajustes
        $tieneAjustesAplicados = MovimientoInventario::where('referencia', "Conteo #{$conteo->id}")->exists();
        
        if ($conteo->estado === 'cancelado' || $tieneAjustesAplicados) {
            return redirect()->route('conteos.show', $conteo)
                ->with('error', 'No se puede editar este conteo. Ya se aplicaron los ajustes o está cancelado.');
        }
        
        // Si está finalizado pero sin ajustes, permitir editar (volver a "en_proceso")
        if ($conteo->estado === 'finalizado' && !$tieneAjustesAplicados) {
            $conteo->update(['estado' => 'en_proceso']);
        }

        // Iniciar conteo si está pendiente
        if ($conteo->estado === 'pendiente') {
            $conteo->iniciar();
        }

        $productos = $conteo->detalles()
            ->with('producto')
            ->orderBy('escaneado')
            ->orderBy('ultima_actualizacion', 'desc')
            ->get();

        return view('conteos.escanear', compact('conteo', 'productos'));
    }

    public function procesarEscaneo(Request $request, ConteoFisico $conteo)
    {
        // Permitir escanear si está finalizado PERO aún no se han aplicado ajustes
        $tieneAjustesAplicados = MovimientoInventario::where('referencia', "Conteo #{$conteo->id}")->exists();
        
        if ($conteo->estado === 'cancelado' || $tieneAjustesAplicados) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede editar este conteo. Ya se aplicaron los ajustes o está cancelado.'
            ], 422);
        }
        
        // Si está finalizado pero sin ajustes, permitir editar
        if ($conteo->estado === 'finalizado' && !$tieneAjustesAplicados) {
            $conteo->update(['estado' => 'en_proceso']);
        }

        $validated = $request->validate([
            'codigo' => 'required|string',
        ]);

        return DB::transaction(function () use ($conteo, $validated) {
            // Buscar producto por código
            $producto = Producto::where('codigo', $validated['codigo'])
                ->where('activo', true)
                ->first();

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado con código: ' . $validated['codigo']
                ], 404);
            }

            // Buscar o crear detalle del conteo para este producto
            $detalle = DetalleConteo::firstOrCreate(
                [
                    'conteo_fisico_id' => $conteo->id,
                    'producto_id' => $producto->id,
                ],
                [
                    'cantidad_sistema' => $producto->stock_actual,
                    'cantidad_fisica' => 0,
                    'diferencia' => -$producto->stock_actual,
                    'escaneado' => false,
                ]
            );

            // Incrementar cantidad física
            $detalle->incrementarCantidad(1);

            // Iniciar conteo si está pendiente
            if ($conteo->estado === 'pendiente') {
                $conteo->iniciar();
            }

            return response()->json([
                'success' => true,
                'message' => 'Producto escaneado: ' . $producto->nombre,
                'producto' => [
                    'id' => $producto->id,
                    'codigo' => $producto->codigo,
                    'nombre' => $producto->nombre,
                    'cantidad_sistema' => $detalle->cantidad_sistema,
                    'cantidad_fisica' => $detalle->cantidad_fisica,
                    'diferencia' => $detalle->diferencia,
                ],
                'estadisticas' => [
                    'total_productos' => $conteo->detalles()->count(),
                    'productos_escaneados' => $conteo->detalles()->where('escaneado', true)->count(),
                ]
            ]);
        });
    }

    public function actualizarCantidadManual(Request $request, ConteoFisico $conteo, DetalleConteo $detalle)
    {
        // Permitir editar si está finalizado PERO aún no se han aplicado ajustes
        // (verificamos si hay movimientos relacionados con este conteo)
        $tieneAjustesAplicados = MovimientoInventario::where('referencia', "Conteo #{$conteo->id}")->exists();
        
        if ($conteo->estado === 'cancelado' || $tieneAjustesAplicados) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede editar este conteo. Ya se aplicaron los ajustes o está cancelado.'
            ], 422);
        }

        if ($detalle->conteo_fisico_id != $conteo->id) {
            return response()->json([
                'success' => false,
                'message' => 'El detalle no pertenece a este conteo.'
            ], 422);
        }

        $validated = $request->validate([
            'cantidad_fisica' => 'required|integer|min:0',
        ]);

        $detalle->actualizarCantidadFisica($validated['cantidad_fisica']);

        return response()->json([
            'success' => true,
            'message' => 'Cantidad actualizada correctamente.',
            'detalle' => [
                'cantidad_sistema' => $detalle->cantidad_sistema,
                'cantidad_fisica' => $detalle->cantidad_fisica,
                'diferencia' => $detalle->diferencia,
            ]
        ]);
    }

    public function finalizar(ConteoFisico $conteo)
    {
        if (!$conteo->puedeFinalizar()) {
            return redirect()->route('conteos.show', $conteo)
                ->with('error', 'No se puede finalizar este conteo en su estado actual.');
        }

        $conteo->finalizar();

        return redirect()->route('conteos.revisar', $conteo)
            ->with('success', 'Conteo finalizado. Revise las diferencias antes de aplicar ajustes.');
    }

    public function revisar(ConteoFisico $conteo)
    {
        if ($conteo->estado !== 'finalizado') {
            return redirect()->route('conteos.show', $conteo)
                ->with('error', 'Debe finalizar el conteo antes de revisar diferencias.');
        }

        $conteo->load('usuario', 'detalles.producto.categoria');
        
        $detallesConDiferencias = $conteo->detalles()
            ->with('producto')
            ->where('diferencia', '!=', 0)
            ->orderByRaw('ABS(diferencia) DESC')
            ->get();

        $estadisticas = [
            'total_productos' => $conteo->detalles()->count(),
            'con_diferencias' => $detallesConDiferencias->count(),
            'sin_diferencias' => $conteo->detalles()->where('diferencia', 0)->count(),
            'total_sobrantes' => $conteo->detalles()->where('diferencia', '>', 0)->sum('diferencia'),
            'total_faltantes' => abs($conteo->detalles()->where('diferencia', '<', 0)->sum('diferencia')),
        ];

        return view('conteos.revisar', compact('conteo', 'detallesConDiferencias', 'estadisticas'));
    }

    public function aplicarAjustes(Request $request, ConteoFisico $conteo)
    {
        if ($conteo->estado !== 'finalizado') {
            return redirect()->route('conteos.show', $conteo)
                ->with('error', 'Debe finalizar el conteo antes de aplicar ajustes.');
        }

        $validated = $request->validate([
            'confirmar' => 'required|accepted',
        ]);

        return DB::transaction(function () use ($conteo) {
            $detallesConDiferencias = $conteo->detalles()
                ->where('diferencia', '!=', 0)
                ->with('producto')
                ->get();

            $ajustesAplicados = 0;

            foreach ($detallesConDiferencias as $detalle) {
                if ($detalle->diferencia == 0) {
                    continue;
                }

                $producto = $detalle->producto;
                $cantidadAjuste = abs($detalle->diferencia);
                $tipo = $detalle->diferencia > 0 ? 'entrada' : 'salida';

                // Crear movimiento de inventario
                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'tipo' => $tipo,
                    'cantidad' => $cantidadAjuste,
                    'motivo' => "Ajuste por conteo físico: {$conteo->nombre}",
                    'usuario_id' => auth()->id(),
                    'fecha' => $conteo->fecha_conteo,
                    'referencia' => "Conteo #{$conteo->id}",
                ]);

                // Actualizar stock del producto
                if ($tipo === 'entrada') {
                    $producto->increment('stock_actual', $cantidadAjuste);
                } else {
                    // Validar que no quede negativo
                    $nuevoStock = $producto->stock_actual - $cantidadAjuste;
                    if ($nuevoStock < 0) {
                        $producto->stock_actual = 0;
                    } else {
                        $producto->decrement('stock_actual', $cantidadAjuste);
                    }
                    $producto->save();
                }
                
                // Refrescar producto para obtener valores actualizados
                $producto->refresh();

                $ajustesAplicados++;
            }

            // Actualizar observaciones del conteo
            $conteo->update([
                'observaciones' => ($conteo->observaciones ?? '') . "\nAjustes aplicados el " . now()->format('d/m/Y H:i') . " por " . auth()->user()->nombre . ". Total de ajustes: {$ajustesAplicados}",
            ]);

            return redirect()->route('conteos.show', $conteo)
                ->with('success', "Se aplicaron {$ajustesAplicados} ajustes al inventario correctamente.");
        });
    }

    public function reporteDiferencias(ConteoFisico $conteo)
    {
        if ($conteo->estado !== 'finalizado') {
            return redirect()->route('conteos.show', $conteo)
                ->with('error', 'El conteo debe estar finalizado para generar el reporte.');
        }

        $conteo->load('usuario', 'detalles.producto.categoria');
        
        $detallesConDiferencias = $conteo->detalles()
            ->with('producto')
            ->where('diferencia', '!=', 0)
            ->orderByRaw('ABS(diferencia) DESC')
            ->get();

        $estadisticas = [
            'total_productos' => $conteo->detalles()->count(),
            'con_diferencias' => $detallesConDiferencias->count(),
            'sin_diferencias' => $conteo->detalles()->where('diferencia', 0)->count(),
            'total_sobrantes' => $conteo->detalles()->where('diferencia', '>', 0)->sum('diferencia'),
            'total_faltantes' => abs($conteo->detalles()->where('diferencia', '<', 0)->sum('diferencia')),
            'valor_diferencias' => $conteo->detalles()
                ->join('productos', 'detalle_conteos.producto_id', '=', 'productos.id')
                ->selectRaw('SUM(ABS(detalle_conteos.diferencia) * productos.precio_compra) as total')
                ->where('detalle_conteos.diferencia', '!=', 0)
                ->value('total') ?? 0,
        ];

        return view('conteos.reporte-diferencias', compact('conteo', 'detallesConDiferencias', 'estadisticas'));
    }

    public function cancelar(ConteoFisico $conteo)
    {
        if (!in_array($conteo->estado, ['pendiente', 'en_proceso'])) {
            return redirect()->route('conteos.show', $conteo)
                ->with('error', 'Solo se pueden cancelar conteos pendientes o en proceso.');
        }

        $conteo->cancelar();

        return redirect()->route('conteos.index')
            ->with('success', 'Conteo cancelado exitosamente.');
    }
}
