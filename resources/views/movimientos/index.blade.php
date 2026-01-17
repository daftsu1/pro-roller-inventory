@extends('layouts.app')

@section('title', 'Movimientos de Inventario')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Movimientos de Inventario</h2>
    <a href="{{ route('movimientos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Movimiento
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('movimientos.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <select name="tipo" class="form-select">
                        <option value="">Todos los tipos</option>
                        <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                        <option value="salida" {{ request('tipo') == 'salida' ? 'selected' : '' }}>Salida</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="producto_id" class="form-select">
                        <option value="">Todos los productos</option>
                        @foreach($productos as $producto)
                            <option value="{{ $producto->id }}" {{ request('producto_id') == $producto->id ? 'selected' : '' }}>
                                {{ $producto->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}" placeholder="Desde">
                </div>
                <div class="col-md-2">
                    <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}" placeholder="Hasta">
                </div>
                <div class="col-md-2">
                    <select name="origen" class="form-select">
                        <option value="">Todos los orígenes</option>
                        <option value="ventas" {{ request('origen') == 'ventas' ? 'selected' : '' }}>Por Ventas</option>
                        <option value="manual" {{ request('origen') == 'manual' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de movimientos -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Motivo</th>
                        <th>Usuario</th>
                        <th>Origen</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $movimiento)
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
                        <td>
                            @if($movimiento->venta_id)
                                <a href="{{ route('ventas.show', $movimiento->venta) }}" class="badge bg-info text-decoration-none">
                                    Venta #{{ $movimiento->venta->numero_factura }}
                                </a>
                            @else
                                <span class="badge bg-secondary">Manual</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('movimientos.show', $movimiento) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No se encontraron movimientos</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="mt-3">
            {{ $movimientos->links() }}
        </div>
    </div>
</div>
@endsection
