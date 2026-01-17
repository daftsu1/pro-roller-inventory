@extends('layouts.app')

@section('title', 'Detalle Proveedor')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>{{ $proveedor->nombre }}</h2>
    <div>
        <a href="{{ route('proveedores.edit', $proveedor) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">
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
                        <th width="40%">Nombre:</th>
                        <td>{{ $proveedor->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Contacto:</th>
                        <td>{{ $proveedor->contacto ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Teléfono:</th>
                        <td>{{ $proveedor->telefono ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>
                            @if($proveedor->email)
                                <a href="mailto:{{ $proveedor->email }}">{{ $proveedor->email }}</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Total Productos:</th>
                        <td><span class="badge bg-info fs-6">{{ $proveedor->productos->count() }}</span></td>
                    </tr>
                    <tr>
                        <th>Fecha Creación:</th>
                        <td>{{ $proveedor->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Productos de este proveedor -->
@if($proveedor->productos->count() > 0)
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Productos de este Proveedor</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Precio Compra</th>
                        <th>Precio Venta</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proveedor->productos as $producto)
                    <tr>
                        <td>{{ $producto->codigo }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td>${{ number_format($producto->precio_compra, 2) }}</td>
                        <td>${{ number_format($producto->precio_venta, 2) }}</td>
                        <td>
                            @if($producto->tieneStockMinimo())
                                <span class="badge bg-warning">{{ $producto->stock_actual }}</span>
                            @else
                                {{ $producto->stock_actual }}
                            @endif
                        </td>
                        <td>
                            @if($producto->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('productos.show', $producto) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Este proveedor no tiene productos asociados.
</div>
@endif
@endsection
