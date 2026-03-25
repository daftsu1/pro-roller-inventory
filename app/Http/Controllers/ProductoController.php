<?php

namespace App\Http\Controllers;

use App\Models\CodigoBarras;
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
            'codigos_barras' => 'nullable|string',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                // Crear el producto
                $producto = Producto::create($validated);

                $this->sincronizarCodigosBarras($producto, $validated['codigos_barras'] ?? null);

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
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['codigos_barras' => $e->getMessage()])
                ->with('error', $e->getMessage());
        }
    }

    public function show(Producto $producto)
    {
        $producto->load('categoria', 'proveedor', 'codigosBarras', 'movimientos.producto', 'detallesVenta.venta');
        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $producto->load('codigosBarras');
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
            'codigos_barras' => 'nullable|string',
        ]);

        try {
            return DB::transaction(function () use ($producto, $validated) {
            // Guardar stock anterior
            $stockAnterior = $producto->stock_actual;
            $stockNuevo = $validated['stock_actual'];
            $diferencia = $stockNuevo - $stockAnterior;

            // Actualizar producto
            $producto->update($validated);

            $this->sincronizarCodigosBarras($producto, $validated['codigos_barras'] ?? null);

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
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['codigos_barras' => $e->getMessage()])
                ->with('error', $e->getMessage());
        }
    }

    public function desactivar(Producto $producto)
    {
        if (! $producto->activo) {
            return redirect()->route('productos.index')
                ->with('warning', 'El producto ya estaba inactivo.');
        }

        $producto->update(['activo' => false]);

        return redirect()->route('productos.index')
            ->with('success', 'Producto desactivado. No aparecerá en ventas nuevas; el historial se conserva.');
    }

    public function reactivar(Producto $producto)
    {
        if ($producto->activo) {
            return redirect()->route('productos.index')
                ->with('warning', 'El producto ya estaba activo.');
        }

        $producto->update(['activo' => true]);

        return redirect()->route('productos.index')
            ->with('success', 'Producto reactivado.');
    }

    private function sincronizarCodigosBarras(Producto $producto, ?string $rawCodigos): void
    {
        $codigos = collect(preg_split("/\r\n|\n|\r/", (string) $rawCodigos))
            ->map(fn ($c) => trim((string) $c))
            ->filter(fn ($c) => $c !== '')
            ->values();

        if ($codigos->isEmpty()) {
            $producto->codigosBarras()->delete();
            return;
        }

        // Validaciones simples
        foreach ($codigos as $c) {
            if (mb_strlen($c) > 50) {
                throw new \InvalidArgumentException("El código '{$c}' supera el máximo de 50 caracteres.");
            }
        }

        $duplicadosEnFormulario = $codigos->duplicates()->values()->all();
        if (!empty($duplicadosEnFormulario)) {
            throw new \InvalidArgumentException('Hay códigos repetidos en la lista: ' . implode(', ', $duplicadosEnFormulario));
        }

        // No permitir usar el código principal del mismo producto como variante
        if ($codigos->contains($producto->codigo)) {
            throw new \InvalidArgumentException("El código '{$producto->codigo}' ya es el código principal del producto. No lo repita como variante.");
        }

        // Conflicto con códigos principales de otros productos
        $conflictoProducto = Producto::query()
            ->whereIn('codigo', $codigos->all())
            ->where('id', '!=', $producto->id)
            ->select('id', 'codigo', 'nombre')
            ->first();

        if ($conflictoProducto) {
            throw new \InvalidArgumentException("El código '{$conflictoProducto->codigo}' ya está asignado al producto '{$conflictoProducto->nombre}'. Revise el producto y decida si lo cambia.");
        }

        // Conflicto con variantes existentes de otros productos
        $conflictoVariante = CodigoBarras::query()
            ->whereIn('codigo', $codigos->all())
            ->where('producto_id', '!=', $producto->id)
            ->with(['producto:id,nombre,codigo'])
            ->first();

        if ($conflictoVariante && $conflictoVariante->producto) {
            $p = $conflictoVariante->producto;
            throw new \InvalidArgumentException("El código '{$conflictoVariante->codigo}' ya está asignado al producto '{$p->nombre}'. Revise el producto y decida si lo cambia.");
        }

        // Reemplazar lista completa (simple y consistente)
        $producto->codigosBarras()->delete();
        foreach ($codigos as $c) {
            $producto->codigosBarras()->create(['codigo' => $c]);
        }
    }
}
