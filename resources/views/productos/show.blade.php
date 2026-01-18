@extends('layouts.app')

@section('title', 'Detalle Producto')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>{{ $producto->nombre }}</h2>
    <div>
        <a href="{{ route('productos.edit', $producto) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('productos.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información General</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Código:</th>
                        <td>{{ $producto->codigo }}</td>
                    </tr>
                    <tr>
                        <th>Nombre:</th>
                        <td>{{ $producto->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Descripción:</th>
                        <td>{{ $producto->descripcion ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Categoría:</th>
                        <td>{{ $producto->categoria->nombre ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Proveedor:</th>
                        <td>{{ $producto->proveedor->nombre ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Estado:</th>
                        <td>
                            @if($producto->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Precios y Stock</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Precio Compra:</th>
                        <td>${{ number_format($producto->precio_compra, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Precio Venta:</th>
                        <td class="fw-bold text-success">${{ number_format($producto->precio_venta, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Stock Actual:</th>
                        <td>
                            @if($producto->tieneStockMinimo())
                                <span class="badge bg-warning fs-6">{{ $producto->stock_actual }}</span>
                            @else
                                <span class="badge bg-success fs-6">{{ $producto->stock_actual }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Stock Mínimo:</th>
                        <td>{{ $producto->stock_minimo }}</td>
                    </tr>
                    @if($producto->tieneStockMinimo())
                    <tr>
                        <th colspan="2">
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle"></i> 
                                Stock bajo el mínimo
                            </div>
                        </th>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Movimientos recientes del producto -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Movimientos Recientes</h5>
    </div>
    <div class="card-body">
        @if($producto->movimientos->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Motivo</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($producto->movimientos->take(10) as $movimiento)
                        <tr>
                            <td>{{ $movimiento->fecha->format('d/m/Y') }}</td>
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
            <p class="text-muted mb-0">No hay movimientos registrados para este producto</p>
        @endif
    </div>
</div>
@endsection
