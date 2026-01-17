@extends('layouts.app')

@section('title', 'Informe de Clientes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people"></i> Informe de Clientes</h2>
    <a href="{{ route('informes.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('informes.clientes') }}">
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
                        <a href="{{ route('informes.clientes') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de clientes -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Clientes por Total de Compras</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th class="text-end">Total Ventas</th>
                        <th class="text-end">Total Compras</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $index => $cliente)
                    <tr>
                        <td><span class="badge bg-info">{{ $index + 1 }}</span></td>
                        <td><strong>{{ $cliente->nombre }}</strong></td>
                        <td>{{ $cliente->documento ?? '-' }}</td>
                        <td>{{ $cliente->email ?? '-' }}</td>
                        <td>{{ $cliente->telefono ?? '-' }}</td>
                        <td class="text-end"><strong>{{ $cliente->ventas_count }}</strong></td>
                        <td class="text-end"><strong class="text-success">${{ number_format($cliente->ventas_sum_total ?? 0, 2) }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            <i class="bi bi-inbox"></i><br>
                            No se encontraron clientes con ventas en el período seleccionado
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
