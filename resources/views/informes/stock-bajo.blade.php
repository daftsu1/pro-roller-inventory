@extends('layouts.app')

@section('title', 'Productos con Stock Bajo')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <h2 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Productos con Stock Bajo</h2>
    <div class="d-flex flex-wrap gap-2">
        @if($productosBajoStock->isNotEmpty())
            <a href="{{ route('informes.stock-bajo.exportar-csv', array_filter(['proveedor_id' => $proveedorFiltroId, 'categoria_id' => $categoriaFiltroId])) }}" class="btn btn-outline-primary">
                <i class="bi bi-download"></i> Exportar CSV
            </a>
        @endif
        <a href="{{ route('informes.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<form method="GET" action="{{ route('informes.stock-bajo') }}" class="card mb-4">
    <div class="card-body py-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-6 col-lg-4">
                <label for="proveedor_id" class="form-label mb-1">Proveedor</label>
                <select name="proveedor_id" id="proveedor_id" class="form-select">
                    <option value="">Todos los proveedores</option>
                    @foreach($proveedores as $proveedor)
                        <option value="{{ $proveedor->id }}" {{ (string) $proveedorFiltroId === (string) $proveedor->id ? 'selected' : '' }}>
                            {{ $proveedor->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 col-lg-4">
                <label for="categoria_id" class="form-label mb-1">Categoría</label>
                <select name="categoria_id" id="categoria_id" class="form-select">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ (string) $categoriaFiltroId === (string) $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-secondary">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>
        </div>
    </div>
</form>

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
                            @if($proveedorFiltroId || $categoriaFiltroId)
                                <i class="bi bi-inbox fs-1"></i><br>
                                <strong>Sin resultados</strong><br>
                                No hay productos con stock bajo para los filtros seleccionados (proveedor y/o categoría).
                            @else
                                <i class="bi bi-check-circle text-success fs-1"></i><br>
                                <strong>¡Excelente!</strong><br>
                                No hay productos con stock bajo en este momento
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
