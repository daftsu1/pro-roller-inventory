<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\MovimientoInventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('informes.index');
    }

    public function ventas(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        $ventas = Venta::where('estado', 'completada')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->with('usuario', 'cliente')
            ->orderBy('fecha', 'desc')
            ->get();

        $totalVentas = $ventas->sum('total');
        $cantidadVentas = $ventas->count();
        $promedioVenta = $cantidadVentas > 0 ? $totalVentas / $cantidadVentas : 0;

        // Ingresos por instalación
        $totalInstalacion = $ventas->sum('monto_instalacion');
        $cantidadVentasConInstalacion = $ventas->where('tiene_instalacion', true)->count();
        $porcentajeVentasConInstalacion = $cantidadVentas > 0 ? round(($cantidadVentasConInstalacion / $cantidadVentas) * 100, 1) : 0;

        // Ventas por día (para gráfico)
        $ventasPorDia = Venta::where('estado', 'completada')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->selectRaw('DATE(fecha) as dia, COUNT(*) as cantidad, SUM(total) as total')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        return view('informes.ventas', compact(
            'ventas',
            'fechaInicio',
            'fechaFin',
            'totalVentas',
            'cantidadVentas',
            'promedioVenta',
            'totalInstalacion',
            'cantidadVentasConInstalacion',
            'porcentajeVentasConInstalacion',
            'ventasPorDia'
        ));
    }

    public function productosVendidos(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        $productosVendidos = DetalleVenta::join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
            ->selectRaw('
                productos.id,
                productos.codigo,
                productos.nombre,
                productos.precio_venta,
                SUM(detalle_ventas.cantidad) as total_vendido,
                SUM(detalle_ventas.subtotal) as total_ingresos
            ')
            ->groupBy('productos.id', 'productos.codigo', 'productos.nombre', 'productos.precio_venta')
            ->orderBy('total_vendido', 'desc')
            ->get();

        return view('informes.productos-vendidos', compact(
            'productosVendidos',
            'fechaInicio',
            'fechaFin'
        ));
    }

    public function stockBajo(Request $request)
    {
        $proveedorFiltroId = $this->resolveProveedorIdFiltro($request);
        $productosBajoStock = $this->queryProductosBajoStock($proveedorFiltroId)->get();
        $proveedores = Proveedor::orderBy('nombre')->get();

        return view('informes.stock-bajo', compact('productosBajoStock', 'proveedores', 'proveedorFiltroId'));
    }

    public function exportarStockBajoCsv(Request $request)
    {
        $proveedorFiltroId = $this->resolveProveedorIdFiltro($request);
        $productos = $this->queryProductosBajoStock($proveedorFiltroId)->get();

        if ($productos->isEmpty()) {
            return redirect()
                ->route('informes.stock-bajo', array_filter(['proveedor_id' => $proveedorFiltroId]))
                ->with('warning', 'No hay productos para exportar con el filtro seleccionado.');
        }

        $filename = 'stock-bajo-' . now()->format('Y-m-d');
        if ($proveedorFiltroId !== null) {
            $filename .= '-proveedor-' . $proveedorFiltroId;
        }
        $filename .= '.csv';

        return response()->streamDownload(function () use ($productos) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['id', 'nombre', 'stock actual', 'stock mínimo', 'proveedor']);
            foreach ($productos as $producto) {
                fputcsv($handle, [
                    $producto->id,
                    $producto->nombre,
                    $producto->stock_actual,
                    $producto->stock_minimo,
                    $producto->proveedor->nombre ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function resolveProveedorIdFiltro(Request $request): ?int
    {
        if (! $request->filled('proveedor_id')) {
            return null;
        }

        $id = (int) $request->input('proveedor_id');

        return Proveedor::whereKey($id)->exists() ? $id : null;
    }

    private function queryProductosBajoStock(?int $proveedorId): Builder
    {
        $query = Producto::query()
            ->with(['categoria', 'proveedor'])
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->where('activo', true)
            ->orderByRaw('(stock_actual - stock_minimo) ASC');

        if ($proveedorId !== null) {
            $query->where('proveedor_id', $proveedorId);
        }

        return $query;
    }

    public function clientes(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        $clientes = Cliente::withCount(['ventas' => function($query) use ($fechaInicio, $fechaFin) {
                $query->where('estado', 'completada')
                      ->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            }])
            ->get()
            ->map(function($cliente) use ($fechaInicio, $fechaFin) {
                $ventas = $cliente->ventas()
                    ->where('estado', 'completada')
                    ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->get();
                $cliente->ventas_sum_total = $ventas->sum('total');
                return $cliente;
            })
            ->filter(function($cliente) {
                return $cliente->ventas_count > 0;
            })
            ->sortByDesc('ventas_sum_total')
            ->values();

        return view('informes.clientes', compact(
            'clientes',
            'fechaInicio',
            'fechaFin'
        ));
    }

    public function resumen(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        // Resumen de ventas
        $resumenVentas = Venta::where('estado', 'completada')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->selectRaw('
                COUNT(*) as total_ventas,
                SUM(total) as total_ingresos,
                AVG(total) as promedio_venta,
                MIN(total) as venta_minima,
                MAX(total) as venta_maxima
            ')
            ->first();

        // Ingresos por instalación (mismo período)
        $instalacionStats = Venta::where('estado', 'completada')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->selectRaw('
                COALESCE(SUM(monto_instalacion), 0) as total_instalacion,
                SUM(CASE WHEN tiene_instalacion = 1 THEN 1 ELSE 0 END) as ventas_con_instalacion
            ')
            ->first();
        $totalInstalacion = (float) ($instalacionStats->total_instalacion ?? 0);
        $cantidadVentasConInstalacion = (int) ($instalacionStats->ventas_con_instalacion ?? 0);
        $totalVentasResumen = (int) ($resumenVentas->total_ventas ?? 0);
        $porcentajeVentasConInstalacion = $totalVentasResumen > 0 ? round(($cantidadVentasConInstalacion / $totalVentasResumen) * 100, 1) : 0;

        // Resumen de productos
        $totalProductos = Producto::where('activo', true)->count();
        $productosBajoStock = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')
            ->where('activo', true)
            ->count();

        // Resumen de clientes
        $totalClientes = Cliente::where('activo', true)->count();
        $clientesConVentas = Cliente::whereHas('ventas', function($query) use ($fechaInicio, $fechaFin) {
            $query->where('estado', 'completada')
                  ->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        })->count();

        // Top 5 productos más vendidos
        $topProductos = DetalleVenta::join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
            ->selectRaw('
                productos.nombre,
                SUM(detalle_ventas.cantidad) as cantidad_vendida,
                SUM(detalle_ventas.subtotal) as ingresos
            ')
            ->groupBy('productos.id', 'productos.nombre')
            ->orderBy('cantidad_vendida', 'desc')
            ->limit(5)
            ->get();

        return view('informes.resumen', compact(
            'resumenVentas',
            'totalProductos',
            'productosBajoStock',
            'totalClientes',
            'clientesConVentas',
            'topProductos',
            'totalInstalacion',
            'cantidadVentasConInstalacion',
            'porcentajeVentasConInstalacion',
            'fechaInicio',
            'fechaFin'
        ));
    }
}
