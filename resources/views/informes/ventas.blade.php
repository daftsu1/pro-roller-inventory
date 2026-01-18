@extends('layouts.app')

@section('title', 'Informe de Ventas')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4">
    <h2 class="mb-2 mb-md-0"><i class="bi bi-cart-check"></i> Informe de Ventas</h2>
    <a href="{{ route('informes.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('informes.ventas') }}">
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
                        <a href="{{ route('informes.ventas') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Ventas</h6>
                <h3 class="mb-0">{{ $cantidadVentas }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Ingresos Totales</h6>
                <h3 class="mb-0 text-success">${{ number_format($totalVentas, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Promedio por Venta</h6>
                <h3 class="mb-0">${{ number_format($promedioVenta, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Período</h6>
                <h6 class="mb-0">{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</h6>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de ventas -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Listado de Ventas</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>N° Factura</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $venta)
                    <tr>
                        <td>{{ $venta->fecha->format('d/m/Y') }}</td>
                        <td>{{ $venta->numero_factura ?? '-' }}</td>
                        <td>{{ $venta->cliente ? $venta->cliente->nombre : ($venta->cliente_nombre ?? 'Cliente Ocasional') }}</td>
                        <td>{{ $venta->usuario->nombre }}</td>
                        <td class="text-end"><strong>${{ number_format($venta->total, 0, ',', '.') }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            <i class="bi bi-inbox"></i><br>
                            No se encontraron ventas en el período seleccionado
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($ventas->count() > 0)
                <tfoot>
                    <tr class="table-active">
                        <th colspan="4" class="text-end">Total:</th>
                        <th class="text-end">${{ number_format($totalVentas, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
