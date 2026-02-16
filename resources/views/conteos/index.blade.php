@extends('layouts.app')

@section('title', 'Conteos Físicos')

@section('content')
@php
    use Illuminate\Support\Str;
@endphp
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clipboard-check"></i> Conteos Físicos</h2>
    <a href="{{ route('conteos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Conteo
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('conteos.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                        <option value="finalizado" {{ request('estado') == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                        <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}" placeholder="Desde">
                </div>
                <div class="col-md-3">
                    <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}" placeholder="Hasta">
                </div>
                <div class="col-md-3 d-flex gap-2 justify-content-md-end justify-content-start">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="{{ route('conteos.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de conteos -->
<div class="card">
    <div class="card-body">
        @if($conteos->count() === 0)
            <div class="d-flex flex-column align-items-center justify-content-center text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-clipboard-x fs-1 text-muted"></i>
                </div>
                <h5 class="mb-2">No se encontraron conteos físicos</h5>
                <p class="text-muted mb-4">Crea un conteo y empieza a pistolear productos para ir contando en tiempo real.</p>
                <a href="{{ route('conteos.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Crear primer conteo
                </a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Fecha Conteo</th>
                        <th>Estado</th>
                        <th>Productos</th>
                        <th>Escaneados</th>
                        <th>Diferencias</th>
                        <th>Creado por</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($conteos as $conteo)
                    @php
                        $nombreAutoGenerado = "Conteo Físico - " . $conteo->fecha_conteo->format('d/m/Y');
                        $mostrarNombre = $conteo->nombre && $conteo->nombre !== $nombreAutoGenerado;
                    @endphp
                    <tr>
                        <td>
                            <strong>Conteo #{{ $conteo->id }}</strong>
                            @if($mostrarNombre)
                                <br><small class="text-muted">{{ $conteo->nombre }}</small>
                            @endif
                            @if($conteo->descripcion)
                                <br><small class="text-muted">{{ Str::limit($conteo->descripcion, 50) }}</small>
                            @endif
                        </td>
                        <td>{{ $conteo->fecha_conteo->format('d/m/Y') }}</td>
                        <td>
                            @if($conteo->estado === 'pendiente')
                                <span class="badge bg-secondary">Pendiente</span>
                            @elseif($conteo->estado === 'en_proceso')
                                <span class="badge bg-primary">En Proceso</span>
                            @elseif($conteo->estado === 'finalizado')
                                <span class="badge bg-success">Finalizado</span>
                            @else
                                <span class="badge bg-danger">Cancelado</span>
                            @endif
                        </td>
                        <td>{{ $conteo->detalles_count ?? 0 }}</td>
                        <td>
                            {{ $conteo->escaneados_count ?? 0 }} / {{ $conteo->detalles_count ?? 0 }}
                        </td>
                        <td>
                            @if(($conteo->diferencias_count ?? 0) > 0)
                                <span class="badge bg-warning">{{ $conteo->diferencias_count }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td>{{ $conteo->usuario->nombre ?? '-' }}</td>
                        <td class="text-end">
                            <div class="btn-group" role="group" aria-label="Acciones">
                                <a href="{{ route('conteos.show', $conteo) }}" class="btn btn-sm btn-outline-info" title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($conteo->estado === 'pendiente' || $conteo->estado === 'en_proceso')
                                    <a href="{{ route('conteos.escanear', $conteo) }}" class="btn btn-sm btn-outline-primary" title="Escanear">
                                        <i class="bi bi-upc-scan"></i>
                                    </a>
                                @endif
                                @if($conteo->estado === 'en_proceso')
                                    <form action="{{ route('conteos.finalizar', $conteo) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Finalizar" onclick="return confirm('¿Está seguro de finalizar este conteo?')">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($conteo->estado === 'finalizado')
                                    <a href="{{ route('conteos.revisar', $conteo) }}" class="btn btn-sm btn-outline-warning" title="Revisar diferencias">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </a>
                                    <a href="{{ route('conteos.reporte-diferencias', $conteo) }}" class="btn btn-sm btn-outline-secondary" title="Ver reporte" target="_blank">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </a>
                                @endif
                                @if(in_array($conteo->estado, ['pendiente', 'en_proceso']))
                                    <form action="{{ route('conteos.cancelar', $conteo) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancelar" onclick="return confirm('¿Está seguro de cancelar este conteo?')">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="mt-3">
            {{ $conteos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
