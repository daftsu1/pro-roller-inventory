@extends('layouts.app')

@section('title', 'Detalle Usuario')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>{{ $usuario->nombre }}</h2>
    <div>
        @can('editar-usuarios')
        <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        @endcan
        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
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
                        <td>{{ $usuario->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $usuario->email }}</td>
                    </tr>
                    <tr>
                        <th>Roles:</th>
                        <td>
                            @foreach($usuario->roles as $rol)
                                <span class="badge bg-info">{{ ucfirst($rol->name) }}</span>
                            @endforeach
                            @if($usuario->roles->isEmpty())
                                <span class="text-muted">Sin rol asignado</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Estado:</th>
                        <td>
                            @if($usuario->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Fecha Creación:</th>
                        <td>{{ $usuario->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Última Actualización:</th>
                        <td>{{ $usuario->updated_at->format('d/m/Y H:i') }}</td>
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
                        <td><span class="badge bg-primary fs-6">{{ $usuario->ventas->count() }}</span></td>
                    </tr>
                    <tr>
                        <th>Total Movimientos:</th>
                        <td><span class="badge bg-info fs-6">{{ $usuario->movimientos->count() }}</span></td>
                    </tr>
                    @if($usuario->ventas->count() > 0)
                    <tr>
                        <th>Ventas Completadas:</th>
                        <td>{{ $usuario->ventas->where('estado', 'completada')->count() }}</td>
                    </tr>
                    <tr>
                        <th>Ventas Pendientes:</th>
                        <td>{{ $usuario->ventas->where('estado', 'pendiente')->count() }}</td>
                    </tr>
                    <tr>
                        <th>Total Vendido:</th>
                        <td class="fw-bold text-success">
                            ${{ number_format($usuario->ventas->where('estado', 'completada')->sum('total'), 2) }}
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Ventas recientes -->
@if($usuario->ventas->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Ventas Recientes</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuario->ventas->take(10)->sortByDesc('created_at') as $venta)
                    <tr>
                        <td>{{ $venta->numero_factura }}</td>
                        <td>{{ $venta->fecha->format('d/m/Y') }}</td>
                        <td>{{ $venta->cliente_nombre ?? '-' }}</td>
                        <td>${{ number_format($venta->total, 2) }}</td>
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
@endif

<!-- Movimientos recientes -->
@if($usuario->movimientos->count() > 0)
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Movimientos Recientes</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuario->movimientos->take(10)->sortByDesc('created_at') as $movimiento)
                    <tr>
                        <td>{{ $movimiento->fecha->format('d/m/Y H:i') }}</td>
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
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
