@extends('layouts.app')

@section('title', 'Detalle Movimiento')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Detalle de Movimiento</h2>
    <a href="{{ route('movimientos.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Movimiento</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Fecha:</th>
                        <td>{{ $movimiento->fecha->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Producto:</th>
                        <td>{{ $movimiento->producto->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Tipo:</th>
                        <td>
                            @if($movimiento->tipo === 'entrada')
                                <span class="badge bg-success fs-6">Entrada</span>
                            @else
                                <span class="badge bg-danger fs-6">Salida</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Cantidad:</th>
                        <td><strong>{{ $movimiento->cantidad }}</strong></td>
                    </tr>
                    <tr>
                        <th>Motivo:</th>
                        <td>{{ $movimiento->motivo }}</td>
                    </tr>
                    <tr>
                        <th>Referencia:</th>
                        <td>{{ $movimiento->referencia ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Usuario:</th>
                        <td>{{ $movimiento->usuario->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Origen:</th>
                        <td>
                            @if($movimiento->venta_id)
                                <a href="{{ route('ventas.show', $movimiento->venta) }}" class="badge bg-info text-decoration-none">
                                    Venta #{{ $movimiento->venta->numero_factura }}
                                </a>
                            @else
                                <span class="badge bg-secondary">Movimiento Manual</span>
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
                <h5 class="mb-0">Información del Producto</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Código:</th>
                        <td>{{ $movimiento->producto->codigo }}</td>
                    </tr>
                    <tr>
                        <th>Nombre:</th>
                        <td>{{ $movimiento->producto->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Stock Actual:</th>
                        <td>
                            @if($movimiento->producto->tieneStockMinimo())
                                <span class="badge bg-warning fs-6">{{ $movimiento->producto->stock_actual }}</span>
                            @else
                                <span class="badge bg-success fs-6">{{ $movimiento->producto->stock_actual }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Stock Mínimo:</th>
                        <td>{{ $movimiento->producto->stock_minimo }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
