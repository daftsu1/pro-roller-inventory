@extends('layouts.app')

@section('title', 'Detalle Cliente')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>{{ $cliente->nombre }}</h2>
    <div>
        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
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
                        <td>{{ $cliente->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Documento:</th>
                        <td>{{ $cliente->documento ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Teléfono:</th>
                        <td>{{ $cliente->telefono ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $cliente->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Dirección:</th>
                        <td>{{ $cliente->direccion ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Estado:</th>
                        <td>
                            @if($cliente->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Fecha Creación:</th>
                        <td>{{ $cliente->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Estadísticas</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Total Ventas:</th>
                        <td><span class="badge bg-primary fs-6">{{ $estadisticas['total_ventas'] }}</span></td>
                    </tr>
                    <tr>
                        <th>Total Compras:</th>
                        <td class="fw-bold text-success">
                            ${{ number_format($estadisticas['total_compras'], 2) }}
                        </td>
                    </tr>
                    <tr>
                        <th>Ventas Pendientes:</th>
                        <td><span class="badge bg-warning">{{ $estadisticas['ventas_pendientes'] }}</span></td>
                    </tr>
                    @if($estadisticas['venta_mas_reciente'])
                    <tr>
                        <th>Última Venta:</th>
                        <td>{{ $estadisticas['venta_mas_reciente']->fecha->format('d/m/Y') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Historial de Ventas -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Historial de Ventas</h5>
    </div>
    <div class="card-body">
        @if($cliente->ventas->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Factura</th>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cliente->ventas as $venta)
                        <tr>
                            <td>{{ $venta->numero_factura }}</td>
                            <td>{{ $venta->fecha->format('d/m/Y') }}</td>
                            <td>{{ $venta->usuario->nombre }}</td>
                            <td class="fw-bold">${{ number_format($venta->total, 2) }}</td>
                            <td>
                                @if($venta->estado === 'completada')
                                    <span class="badge bg-success">Completada</span>
                                @elseif($venta->estado === 'pendiente')
                                    <span class="badge bg-warning">Pendiente</span>
                                @else
                                    <span class="badge bg-danger">Cancelada</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('ventas.show', $venta) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($cliente->ventas->count() > 20)
            <div class="mt-3 text-center">
                <a href="{{ route('ventas.index', ['cliente_id' => $cliente->id]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-list-ul"></i> Ver todas las ventas de este cliente
                </a>
            </div>
            @endif
        @else
            <p class="text-muted mb-0">
                <i class="bi bi-inbox"></i> Este cliente no tiene ventas registradas.
            </p>
        @endif
    </div>
</div>
@endsection
