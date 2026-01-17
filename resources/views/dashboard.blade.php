@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Dashboard</h2>
</div>

<!-- Cards de métricas -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Productos</h6>
                        <h3 class="mb-0">{{ $totalProductos }}</h3>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-box fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Bajo Stock</h6>
                        <h3 class="mb-0 text-warning">{{ $productosBajoStock }}</h3>
                    </div>
                    <div class="text-warning">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Ventas Hoy</h6>
                        <h3 class="mb-0">{{ $ventasHoy }}</h3>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-cart-check fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Hoy</h6>
                        <h3 class="mb-0 text-success">${{ number_format($totalVentasHoy, 2) }}</h3>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-currency-dollar fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Productos bajo stock -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Productos Bajo Stock</h5>
            </div>
            <div class="card-body">
                @if($productosBajoStockList->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Stock Actual</th>
                                    <th>Stock Mínimo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productosBajoStockList as $producto)
                                <tr>
                                    <td>{{ $producto->nombre }}</td>
                                    <td><span class="badge bg-warning">{{ $producto->stock_actual }}</span></td>
                                    <td>{{ $producto->stock_minimo }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No hay productos bajo stock</p>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Ventas recientes -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Ventas Recientes</h5>
            </div>
            <div class="card-body">
                @if($ventasRecientes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Factura</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ventasRecientes as $venta)
                                <tr>
                                    <td><a href="{{ route('ventas.show', $venta) }}">{{ $venta->numero_factura }}</a></td>
                                    <td>{{ $venta->fecha->format('d/m/Y') }}</td>
                                    <td>${{ number_format($venta->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No hay ventas recientes</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Movimientos recientes -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-left-right"></i> Movimientos Recientes</h5>
            </div>
            <div class="card-body">
                @if($movimientosRecientes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th>Motivo</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($movimientosRecientes as $movimiento)
                                <tr>
                                    <td>{{ $movimiento->fecha->format('d/m/Y') }}</td>
                                    <td>{{ $movimiento->producto->nombre }}</td>
                                    <td>
                                        @if($movimiento->tipo === 'entrada')
                                            <span class="badge bg-success">Entrada</span>
                                        @else
                                            <span class="badge bg-danger">Salida</span>
                                        @endif
                                    </td>
                                    <td>{{ $movimiento->cantidad }}</td>
                                    <td>{{ $movimiento->motivo }}</td>
                                    <td>{{ $movimiento->usuario->nombre }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No hay movimientos recientes</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
