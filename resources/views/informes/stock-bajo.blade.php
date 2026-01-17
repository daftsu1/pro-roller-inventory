@extends('layouts.app')

@section('title', 'Productos con Stock Bajo')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-exclamation-triangle"></i> Productos con Stock Bajo</h2>
    <a href="{{ route('informes.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<!-- Resumen -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="text-warning me-3">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Productos con Stock Bajo</h6>
                        <h3 class="mb-0 text-warning">{{ $productosBajoStock->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de productos -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Listado de Productos</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Proveedor</th>
                        <th class="text-end">Stock Actual</th>
                        <th class="text-end">Stock Mínimo</th>
                        <th class="text-end">Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productosBajoStock as $producto)
                    <tr class="{{ ($producto->stock_actual == 0) ? 'table-danger' : '' }}">
                        <td>{{ $producto->codigo }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $producto->categoria->nombre ?? '-' }}</td>
                        <td>{{ $producto->proveedor->nombre ?? '-' }}</td>
                        <td class="text-end">
                            <span class="badge bg-{{ $producto->stock_actual == 0 ? 'danger' : 'warning' }}">
                                {{ $producto->stock_actual }}
                            </span>
                        </td>
                        <td class="text-end">{{ $producto->stock_minimo }}</td>
                        <td class="text-end">
                            <strong class="text-danger">{{ $producto->stock_actual - $producto->stock_minimo }}</strong>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            <i class="bi bi-check-circle text-success fs-1"></i><br>
                            <strong>¡Excelente!</strong><br>
                            No hay productos con stock bajo en este momento
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
