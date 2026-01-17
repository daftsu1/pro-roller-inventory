<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProductos = Producto::count();
        $productosBajoStock = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')->count();
        $ventasHoy = Venta::whereDate('fecha', today())->count();
        $totalVentasHoy = Venta::whereDate('fecha', today())->sum('total');
        
        $movimientosRecientes = MovimientoInventario::with('producto', 'usuario')
            ->latest()
            ->limit(10)
            ->get();

        $ventasRecientes = Venta::with('usuario')
            ->latest()
            ->limit(5)
            ->get();

        $productosBajoStockList = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')
            ->get();

        return view('dashboard', compact(
            'totalProductos',
            'productosBajoStock',
            'ventasHoy',
            'totalVentasHoy',
            'movimientosRecientes',
            'ventasRecientes',
            'productosBajoStockList'
        ));
    }
}
