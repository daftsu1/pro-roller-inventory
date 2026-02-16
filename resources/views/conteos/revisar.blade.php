@extends('layouts.app')

@section('title', 'Revisar Diferencias - ' . $conteo->nombre)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-exclamation-triangle"></i> Revisar Diferencias</h2>
    <div>
        <a href="{{ route('conteos.escanear', $conteo) }}" class="btn btn-outline-primary me-2" title="Volver a escanear productos">
            <i class="bi bi-upc-scan"></i> Volver a Escanear
        </a>
        <a href="{{ route('conteos.show', $conteo) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Información del conteo -->
<div class="card mb-4">
    <div class="card-body">
        <h5>{{ $conteo->nombre }}</h5>
        <p class="mb-0"><small class="text-muted">Fecha: {{ $conteo->fecha_conteo->format('d/m/Y') }}</small></p>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $estadisticas['total_productos'] }}</h3>
                <small class="text-muted">Total Productos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $estadisticas['con_diferencias'] }}</h3>
                <small class="text-muted">Con Diferencias</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $estadisticas['sin_diferencias'] }}</h3>
                <small>Sin Diferencias</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3 class="mb-0">
                    @if($estadisticas['total_sobrantes'] > 0)
                        +{{ number_format($estadisticas['total_sobrantes'], 0) }}
                    @else
                        {{ number_format($estadisticas['total_faltantes'], 0) }}
                    @endif
                </h3>
                <small>
                    @if($estadisticas['total_sobrantes'] > $estadisticas['total_faltantes'])
                        Sobrantes Totales
                    @else
                        Faltantes Totales
                    @endif
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Productos con diferencias -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="mb-3">Productos con Diferencias</h5>
        @if($detallesConDiferencias->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Stock Sistema</th>
                        <th>Stock Físico</th>
                        <th>Diferencia</th>
                        <th>Tipo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detallesConDiferencias as $detalle)
                    <tr id="row_detalle_{{ $detalle->id }}">
                        <td>{{ $detalle->producto->codigo }}</td>
                        <td>{{ $detalle->producto->nombre }}</td>
                        <td>{{ number_format($detalle->cantidad_sistema, 0) }}</td>
                        <td>
                            <strong id="cantidad_fisica_{{ $detalle->id }}">{{ number_format($detalle->cantidad_fisica, 0) }}</strong>
                        </td>
                        <td id="diferencia_{{ $detalle->id }}">
                            @if($detalle->diferencia > 0)
                                <span class="badge bg-success">+{{ number_format($detalle->diferencia, 0) }}</span>
                            @else
                                <span class="badge bg-danger">{{ number_format($detalle->diferencia, 0) }}</span>
                            @endif
                        </td>
                        <td id="tipo_{{ $detalle->id }}">
                            @if($detalle->diferencia > 0)
                                <span class="badge bg-success">Sobrante</span>
                            @else
                                <span class="badge bg-danger">Faltante</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="editarCantidadRevisar({{ $detalle->id }}, {{ $detalle->producto_id }}, '{{ $detalle->producto->codigo }}', {{ $detalle->cantidad_fisica }}, {{ $detalle->cantidad_sistema }})"
                                    title="Editar cantidad física">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> No hay diferencias en este conteo. ¡Todo está correcto!
        </div>
        @endif
    </div>
</div>

<!-- Aplicar ajustes -->
@if($detallesConDiferencias->count() > 0)
<div class="card">
    <div class="card-body">
        <h5 class="mb-3">Aplicar Ajustes al Inventario</h5>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> 
            <strong>Advertencia:</strong> Al aplicar los ajustes, se crearán movimientos de inventario automáticamente 
            para cada diferencia encontrada. Esta acción no se puede deshacer fácilmente.
        </div>
        
        <p>Se aplicarán los siguientes ajustes:</p>
        <ul>
            <li><strong>{{ $detallesConDiferencias->where('diferencia', '>', 0)->count() }}</strong> productos con sobrantes (entradas)</li>
            <li><strong>{{ $detallesConDiferencias->where('diferencia', '<', 0)->count() }}</strong> productos con faltantes (salidas)</li>
        </ul>
        
        <form action="{{ route('conteos.aplicar-ajustes', $conteo) }}" method="POST" id="aplicarAjustesForm">
            @csrf
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="confirmar_ajustes" name="confirmar" value="1" required>
                <label class="form-check-label" for="confirmar_ajustes">
                    Confirmo que deseo aplicar los ajustes al inventario
                </label>
            </div>
            <button type="submit" class="btn btn-primary" onclick="return confirm('¿Está seguro de aplicar los ajustes? Esta acción creará movimientos de inventario.')">
                <i class="bi bi-check-circle"></i> Aplicar Ajustes
            </button>
            <a href="{{ route('conteos.show', $conteo) }}" class="btn btn-secondary">
                Cancelar
            </a>
        </form>
    </div>
</div>
@endif

<!-- Modal para editar cantidad física -->
<div class="modal fade" id="editarCantidadRevisarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Cantidad Física</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Producto:</strong> <span id="modal_revisar_producto_codigo"></span></p>
                <p><small class="text-muted">Stock Sistema: <span id="modal_revisar_stock_sistema"></span></small></p>
                <div class="mb-3">
                    <label for="modal_revisar_cantidad_fisica" class="form-label">Cantidad Física Contada</label>
                    <input type="number" step="1" min="0" class="form-control" id="modal_revisar_cantidad_fisica" required>
                    <input type="hidden" id="modal_revisar_detalle_id">
                    <input type="hidden" id="modal_revisar_producto_id">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarCantidadRevisar()">Guardar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const conteoId = {{ $conteo->id }};

function editarCantidadRevisar(detalleId, productoId, codigo, cantidadActual, stockSistema) {
    document.getElementById('modal_revisar_detalle_id').value = detalleId;
    document.getElementById('modal_revisar_producto_id').value = productoId;
    document.getElementById('modal_revisar_producto_codigo').textContent = codigo;
    document.getElementById('modal_revisar_stock_sistema').textContent = stockSistema;
    document.getElementById('modal_revisar_cantidad_fisica').value = cantidadActual;
    
    const modal = new bootstrap.Modal(document.getElementById('editarCantidadRevisarModal'));
    modal.show();
}

function guardarCantidadRevisar() {
    const detalleId = document.getElementById('modal_revisar_detalle_id').value;
    const productoId = document.getElementById('modal_revisar_producto_id').value;
    const cantidadFisica = Math.round(parseFloat(document.getElementById('modal_revisar_cantidad_fisica').value) || 0);
    const stockSistema = parseFloat(document.getElementById('modal_revisar_stock_sistema').textContent);
    
    if (cantidadFisica < 0) {
        alert('La cantidad debe ser mayor o igual a 0');
        return;
    }
    
    fetch(`/conteos/${conteoId}/detalles/${detalleId}/cantidad`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ cantidad_fisica: cantidadFisica })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar la fila en la tabla
            const nuevaCantidad = Math.round(parseFloat(data.detalle.cantidad_fisica));
            const nuevaDiferencia = parseFloat(data.detalle.diferencia);
            
            document.getElementById(`cantidad_fisica_${detalleId}`).textContent = Math.round(nuevaCantidad);
            
            const diferenciaCell = document.getElementById(`diferencia_${detalleId}`);
            const tipoCell = document.getElementById(`tipo_${detalleId}`);
            
            const diferenciaEntera = Math.round(nuevaDiferencia);
            if (diferenciaEntera > 0) {
                diferenciaCell.innerHTML = `<span class="badge bg-success">+${diferenciaEntera}</span>`;
                tipoCell.innerHTML = `<span class="badge bg-success">Sobrante</span>`;
            } else if (diferenciaEntera < 0) {
                diferenciaCell.innerHTML = `<span class="badge bg-danger">${diferenciaEntera}</span>`;
                tipoCell.innerHTML = `<span class="badge bg-danger">Faltante</span>`;
            } else {
                diferenciaCell.innerHTML = '<span class="text-muted">0</span>';
                tipoCell.innerHTML = '<span class="badge bg-secondary">Sin diferencia</span>';
                // Si ya no hay diferencia, ocultar la fila o recargar la página
                setTimeout(() => location.reload(), 1000);
            }
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('editarCantidadRevisarModal'));
            modal.hide();
            
            // Mostrar mensaje de éxito
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show';
            alert.innerHTML = `
                <i class="bi bi-check-circle"></i> Cantidad actualizada correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.card-body').prepend(alert);
            
            // Recargar estadísticas después de un momento
            setTimeout(() => location.reload(), 1500);
        } else {
            alert(data.message || 'Error al actualizar la cantidad');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar la cantidad');
    });
}
</script>
@endpush
@endsection
