@extends('layouts.app')

@section('title', 'Detalle Venta')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Venta #{{ $venta->numero_factura }}</h2>
    <div>
        @if($venta->estado === 'pendiente')
            <form action="{{ route('ventas.completar', $venta) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Completar esta venta? Se descontará el stock de los productos.')">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Completar Venta
                </button>
            </form>
        @endif
        <a href="{{ route('ventas.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información de la Venta</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Número de Factura:</th>
                        <td>{{ $venta->numero_factura }}</td>
                    </tr>
                    <tr>
                        <th>Fecha:</th>
                        <td>{{ $venta->fecha->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Cliente:</th>
                        <td>{{ $venta->cliente_nombre ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Documento:</th>
                        <td>{{ $venta->cliente_documento ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Vendedor:</th>
                        <td>{{ $venta->usuario->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Estado:</th>
                        <td>
                            @if($venta->estado === 'completada')
                                <span class="badge bg-success">Completada</span>
                            @elseif($venta->estado === 'pendiente')
                                <span class="badge bg-warning">Pendiente</span>
                            @else
                                <span class="badge bg-danger">Cancelada</span>
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
                <h5 class="mb-0">Resumen</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span>${{ number_format($venta->total, 2) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <strong>Total:</strong>
                    <strong class="fs-4 text-success">${{ number_format($venta->total, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detalles de la venta -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Productos Vendidos</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($venta->detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->producto->nombre }}</td>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>${{ number_format($detalle->precio_unitario, 2) }}</td>
                        <td>${{ number_format($detalle->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total:</th>
                        <th>${{ number_format($venta->total, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
