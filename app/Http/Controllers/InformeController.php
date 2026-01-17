<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\MovimientoInventario;
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

    public function stockBajo()
    {
        $productosBajoStock = Producto::with('categoria', 'proveedor')
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->where('activo', true)
            ->orderByRaw('(stock_actual - stock_minimo) ASC')
            ->get();

        return view('informes.stock-bajo', compact('productosBajoStock'));
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
            'fechaInicio',
            'fechaFin'
        ));
    }
}
