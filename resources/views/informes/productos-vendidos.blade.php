@extends('layouts.app')

@section('title', 'Productos Más Vendidos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-bar-chart"></i> Productos Más Vendidos</h2>
    <a href="{{ route('informes.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('informes.productos-vendidos') }}">
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
                        <a href="{{ route('informes.productos-vendidos') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de productos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Ranking de Productos Vendidos</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th class="text-end">Precio Unit.</th>
                        <th class="text-end">Cant. Vendida</th>
                        <th class="text-end">Total Ingresos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productosVendidos as $index => $producto)
                    <tr>
                        <td><span class="badge bg-primary">{{ $index + 1 }}</span></td>
                        <td>{{ $producto->codigo }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td class="text-end">${{ number_format($producto->precio_venta, 2) }}</td>
                        <td class="text-end"><strong>{{ $producto->total_vendido }}</strong></td>
                        <td class="text-end"><strong class="text-success">${{ number_format($producto->total_ingresos, 2) }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            <i class="bi bi-inbox"></i><br>
                            No se encontraron productos vendidos en el período seleccionado
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
