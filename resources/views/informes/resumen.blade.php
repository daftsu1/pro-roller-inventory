@extends('layouts.app')

@section('title', 'Resumen General')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-text"></i> Resumen General</h2>
    <a href="{{ route('informes.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('informes.resumen') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                           value="{{ $fechaInicio }}" required>
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                           value="{{ $fechaFin }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                        <a href="{{ route('informes.resumen') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumen de Ventas -->
<div class="row mb-4">
    <div class="col-12 mb-3">
        <h4><i class="bi bi-cart-check"></i> Resumen de Ventas</h4>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Ventas</h6>
                <h3 class="mb-0">{{ $resumenVentas->total_ventas ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Ingresos Totales</h6>
                <h3 class="mb-0 text-success">${{ number_format($resumenVentas->total_ingresos ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Promedio por Venta</h6>
                <h3 class="mb-0">${{ number_format($resumenVentas->promedio_venta ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Venta Máxima</h6>
                <h3 class="mb-0 text-primary">${{ number_format($resumenVentas->venta_maxima ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Resumen de Inventario -->
<div class="row mb-4">
    <div class="col-12 mb-3">
        <h4><i class="bi bi-box"></i> Resumen de Inventario</h4>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="text-primary me-3">
                        <i class="bi bi-box fs-1"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Productos Activos</h6>
                        <h3 class="mb-0">{{ $totalProductos }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="text-warning me-3">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Productos con Stock Bajo</h6>
                        <h3 class="mb-0 text-warning">{{ $productosBajoStock }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resumen de Clientes -->
<div class="row mb-4">
    <div class="col-12 mb-3">
        <h4><i class="bi bi-people"></i> Resumen de Clientes</h4>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="text-info me-3">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Clientes Activos</h6>
                        <h3 class="mb-0">{{ $totalClientes }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="text-success me-3">
                        <i class="bi bi-cart-check fs-1"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Clientes con Ventas (Período)</h6>
                        <h3 class="mb-0">{{ $clientesConVentas }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top 5 Productos -->
@if($topProductos->count() > 0)
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-trophy"></i> Top 5 Productos Más Vendidos</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Producto</th>
                        <th class="text-end">Cantidad Vendida</th>
                        <th class="text-end">Total Ingresos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topProductos as $index => $producto)
                    <tr>
                        <td>
                            @if($index === 0)
                                <span class="badge bg-warning text-dark">🥇</span>
                            @elseif($index === 1)
                                <span class="badge bg-secondary">🥈</span>
                            @elseif($index === 2)
                                <span class="badge bg-info">🥉</span>
                            @else
                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td><strong>{{ $producto->nombre }}</strong></td>
                        <td class="text-end"><strong>{{ $producto->cantidad_vendida }}</strong></td>
                        <td class="text-end"><strong class="text-success">${{ number_format($producto->ingresos, 2) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
