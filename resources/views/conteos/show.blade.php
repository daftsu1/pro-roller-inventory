@extends('layouts.app')

@section('title', 'Detalle de Conteo Físico')

@section('content')
@php
    $nombreAutoGenerado = "Conteo Físico - " . $conteo->fecha_conteo->format('d/m/Y');
    $mostrarNombre = $conteo->nombre && $conteo->nombre !== $nombreAutoGenerado;
@endphp
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clipboard-check"></i> Conteo #{{ $conteo->id }} 
        @if($mostrarNombre)
            <small class="text-muted">- {{ $conteo->nombre }}</small>
        @endif
    </h2>
    <a href="{{ route('conteos.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<!-- Información del conteo -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Información del Conteo</h5>
                <table class="table table-borderless">
                    @if($mostrarNombre)
                    <tr>
                        <th width="40%">Nombre:</th>
                        <td>{{ $conteo->nombre }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>Descripción:</th>
                        <td>{{ $conteo->descripcion ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Fecha del Conteo:</th>
                        <td>{{ $conteo->fecha_conteo->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Estado:</th>
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
                    </tr>
                    <tr>
                        <th>Creado por:</th>
                        <td>{{ $conteo->usuario->nombre ?? '-' }}</td>
                    </tr>
                    @if($conteo->fecha_inicio)
                    <tr>
                        <th>Fecha Inicio:</th>
                        <td>{{ $conteo->fecha_inicio->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                    @if($conteo->fecha_fin)
                    <tr>
                        <th>Fecha Fin:</th>
                        <td>{{ $conteo->fecha_fin->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            <div class="col-md-6">
                <h5>Estadísticas</h5>
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $estadisticas['total_productos'] }}</h3>
                                <small class="text-muted">Total Productos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $estadisticas['productos_escaneados'] }}</h3>
                                <small>Escaneados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card bg-warning">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $estadisticas['con_diferencias'] }}</h3>
                                <small class="text-muted">Con Diferencias</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $estadisticas['sin_diferencias'] }}</h3>
                                <small>Sin Diferencias</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if($conteo->observaciones)
        <div class="mt-3">
            <h6>Observaciones:</h6>
            <div class="alert alert-info mb-0">
                {!! nl2br(e($conteo->observaciones)) !!}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Acciones -->
<div class="card mb-4">
    <div class="card-body">
        <h5>Acciones</h5>
        <div class="btn-group" role="group">
            @if($conteo->estado === 'pendiente' || $conteo->estado === 'en_proceso')
                <a href="{{ route('conteos.escanear', $conteo) }}" class="btn btn-primary">
                    <i class="bi bi-upc-scan"></i> Escanear Productos
                </a>
            @endif
            @if($conteo->estado === 'en_proceso')
                <form action="{{ route('conteos.finalizar', $conteo) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('¿Está seguro de finalizar este conteo?')">
                        <i class="bi bi-check-circle"></i> Finalizar Conteo
                    </button>
                </form>
            @endif
            @if($conteo->estado === 'finalizado')
                <a href="{{ route('conteos.revisar', $conteo) }}" class="btn btn-warning">
                    <i class="bi bi-exclamation-triangle"></i> Revisar Diferencias
                </a>
                <a href="{{ route('conteos.reporte-diferencias', $conteo) }}" class="btn btn-secondary" target="_blank">
                    <i class="bi bi-file-earmark-text"></i> Ver Reporte
                </a>
            @endif
            @if(in_array($conteo->estado, ['pendiente', 'en_proceso']))
                <form action="{{ route('conteos.cancelar', $conteo) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger" onclick="return confirm('¿Está seguro de cancelar este conteo?')">
                        <i class="bi bi-x-circle"></i> Cancelar Conteo
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<!-- Lista de productos -->
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Productos del Conteo</h5>
            @if($puedeEditar)
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#agregarProductosModal">
                    <i class="bi bi-plus-circle"></i> Agregar Productos
                </button>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Stock Sistema</th>
                        <th>Stock Físico</th>
                        <th>Diferencia</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conteo->detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->producto->codigo }}</td>
                        <td>{{ $detalle->producto->nombre }}</td>
                        <td>{{ number_format($detalle->cantidad_sistema, 0) }}</td>
                        <td>
                            @if($detalle->escaneado)
                                <strong>{{ number_format($detalle->cantidad_fisica, 0) }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($detalle->diferencia != 0)
                                @if($detalle->diferencia > 0)
                                    <span class="badge bg-success">+{{ number_format($detalle->diferencia, 0) }}</span>
                                @else
                                    <span class="badge bg-danger">{{ number_format($detalle->diferencia, 0) }}</span>
                                @endif
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td>
                            @if($detalle->escaneado)
                                <span class="badge bg-success">Escaneado</span>
                            @else
                                <span class="badge bg-secondary">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay productos en este conteo</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para agregar productos -->
@if($puedeEditar && $productosDisponibles->count() > 0)
<div class="modal fade" id="agregarProductosModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Productos al Conteo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('conteos.agregar-productos', $conteo) }}" method="POST" id="agregarProductosForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Seleccione los productos que desea agregar al conteo:</p>
                    <div class="mb-2">
                        <input type="text" class="form-control form-control-sm" id="buscar_producto_agregar" placeholder="Buscar producto...">
                    </div>
                    <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                        @foreach($productosDisponibles as $producto)
                        <div class="form-check producto-item-agregar" data-nombre="{{ strtolower($producto->nombre) }}" data-codigo="{{ strtolower($producto->codigo) }}">
                            <input class="form-check-input" type="checkbox" name="productos[]" 
                                   value="{{ $producto->id }}" id="agregar_producto_{{ $producto->id }}">
                            <label class="form-check-label" for="agregar_producto_{{ $producto->id }}">
                                <strong>{{ $producto->codigo }}</strong> - {{ $producto->nombre }}
                                <small class="text-muted">(Stock: {{ $producto->stock_actual }})</small>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @if($productosDisponibles->count() === 0)
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Todos los productos activos ya están en este conteo.
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Agregar Productos Seleccionados
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Búsqueda de productos en modal de agregar
document.getElementById('buscar_producto_agregar')?.addEventListener('input', function() {
    const busqueda = this.value.toLowerCase();
    const items = document.querySelectorAll('.producto-item-agregar');
    
    items.forEach(item => {
        const nombre = item.getAttribute('data-nombre');
        const codigo = item.getAttribute('data-codigo');
        
        if (busqueda === '' || nombre.includes(busqueda) || codigo.includes(busqueda)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Validación del formulario de agregar productos
document.getElementById('agregarProductosForm')?.addEventListener('submit', function(e) {
    const productosSeleccionados = document.querySelectorAll('#agregarProductosForm input[name="productos[]"]:checked');
    
    if (productosSeleccionados.length === 0) {
        e.preventDefault();
        alert('Debe seleccionar al menos un producto para agregar.');
        return false;
    }
});
</script>
@endpush
@endif
@endsection
