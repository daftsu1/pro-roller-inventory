@extends('layouts.app')

@section('title', 'Escanear Productos - ' . $conteo->nombre)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-upc-scan"></i> Escanear Productos</h2>
    <a href="{{ route('conteos.show', $conteo) }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<!-- Información del conteo -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">{{ $conteo->nombre }}</h5>
                <small class="text-muted">Fecha: {{ $conteo->fecha_conteo->format('d/m/Y') }}</small>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-inline-block me-3">
                    <strong>Escaneados:</strong> 
                    <span class="badge bg-primary" id="contador_escaneados">{{ $productos->where('escaneado', true)->count() }}</span> / 
                    <span id="total_productos">{{ $productos->count() }}</span>
                </div>
                <form action="{{ route('conteos.finalizar', $conteo) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('¿Está seguro de finalizar el conteo?')">
                        <i class="bi bi-check-circle"></i> Finalizar Conteo
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Área de escaneo -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="mb-3">Escanear Código de Barras</h5>
        <div class="row">
            <div class="col-md-8">
                <div class="input-group input-group-lg">
                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                    <input type="text" 
                           class="form-control" 
                           id="codigo_scanner" 
                           placeholder="Escanee o escriba el código del producto..."
                           autofocus
                           autocomplete="off">
                    <button class="btn btn-primary" type="button" onclick="procesarEscaneo()">
                        <i class="bi bi-check"></i> Procesar
                    </button>
                </div>
                <div id="mensaje_escaneo" class="mt-2"></div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-info mb-0">
                    <small>
                        <i class="bi bi-info-circle"></i> 
                        Use un lector de códigos de barras o escriba el código manualmente.
                        Cada escaneo incrementa la cantidad física en 1.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lista de productos escaneados -->
<div class="card">
    <div class="card-body">
        <h5 class="mb-3">Productos Escaneados</h5>
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-hover table-sm">
                <thead class="sticky-top bg-light">
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Stock Sistema</th>
                        <th>Stock Físico</th>
                        <th>Diferencia</th>
                        <th>Última Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="productos_tabla">
                    @foreach($productos as $detalle)
                    <tr id="row_{{ $detalle->producto_id }}" class="{{ $detalle->escaneado ? '' : 'table-secondary' }}">
                        <td>{{ $detalle->producto->codigo }}</td>
                        <td>{{ $detalle->producto->nombre }}</td>
                        <td>{{ number_format($detalle->cantidad_sistema, 0) }}</td>
                        <td>
                            <strong id="cantidad_fisica_{{ $detalle->producto_id }}">{{ number_format($detalle->cantidad_fisica, 0) }}</strong>
                        </td>
                        <td id="diferencia_{{ $detalle->producto_id }}">
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
                            @if($detalle->ultima_actualizacion)
                                <small>{{ $detalle->ultima_actualizacion->format('H:i:s') }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="editarCantidad({{ $detalle->id }}, {{ $detalle->producto_id }}, '{{ $detalle->producto->codigo }}', {{ $detalle->cantidad_fisica }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para editar cantidad manual -->
<div class="modal fade" id="editarCantidadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Cantidad Física</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Producto:</strong> <span id="modal_producto_codigo"></span></p>
                <div class="mb-3">
                    <label for="modal_cantidad_fisica" class="form-label">Cantidad Física</label>
                    <input type="number" step="1" min="0" class="form-control" id="modal_cantidad_fisica" required>
                    <input type="hidden" id="modal_detalle_id">
                    <input type="hidden" id="modal_producto_id">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarCantidadManual()">Guardar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const conteoId = {{ $conteo->id }};
let escaneoTimeout = null;

// Auto-procesar cuando se escribe en el campo de escaneo (simula lector de códigos)
document.getElementById('codigo_scanner').addEventListener('input', function() {
    clearTimeout(escaneoTimeout);
    
    // Si el campo tiene contenido y parece un código completo (sin espacios o con Enter)
    escaneoTimeout = setTimeout(() => {
        if (this.value.trim().length > 0) {
            procesarEscaneo();
        }
    }, 500); // Esperar 500ms después de dejar de escribir
});

// Procesar con Enter
document.getElementById('codigo_scanner').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        procesarEscaneo();
    }
});

function procesarEscaneo() {
    const codigo = document.getElementById('codigo_scanner').value.trim();
    const mensajeDiv = document.getElementById('mensaje_escaneo');
    
    if (!codigo) {
        mensajeDiv.innerHTML = '<div class="alert alert-warning">Por favor ingrese un código</div>';
        return;
    }
    
    mensajeDiv.innerHTML = '<div class="alert alert-info">Procesando...</div>';
    
    fetch(`/conteos/${conteoId}/escanear`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ codigo: codigo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mensajeDiv.innerHTML = `<div class="alert alert-success">✓ ${data.message}</div>`;
            
            // Actualizar la tabla
            actualizarFilaProducto(data.producto.id, data.producto.cantidad_fisica, data.producto.diferencia);
            
            // Actualizar contador
            document.getElementById('contador_escaneados').textContent = data.estadisticas.productos_escaneados;
            
            // Limpiar campo y enfocar
            document.getElementById('codigo_scanner').value = '';
            document.getElementById('codigo_scanner').focus();
            
            // Sonido de éxito (opcional)
            playBeep();
        } else {
            mensajeDiv.innerHTML = `<div class="alert alert-danger">✗ ${data.message}</div>`;
            playErrorBeep();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mensajeDiv.innerHTML = '<div class="alert alert-danger">Error al procesar el escaneo</div>';
        playErrorBeep();
    });
}

function actualizarFilaProducto(productoId, cantidadFisica, diferencia) {
    const fila = document.getElementById(`row_${productoId}`);
    const cantidadCell = document.getElementById(`cantidad_fisica_${productoId}`);
    const diferenciaCell = document.getElementById(`diferencia_${productoId}`);
    
    if (cantidadCell) {
        // Mostrar como entero
        cantidadCell.textContent = Math.round(cantidadFisica);
    }
    
    if (diferenciaCell) {
        const diferenciaEntera = Math.round(diferencia);
        if (diferenciaEntera > 0) {
            diferenciaCell.innerHTML = `<span class="badge bg-success">+${diferenciaEntera}</span>`;
        } else if (diferenciaEntera < 0) {
            diferenciaCell.innerHTML = `<span class="badge bg-danger">${diferenciaEntera}</span>`;
        } else {
            diferenciaCell.innerHTML = '<span class="text-muted">0</span>';
        }
    }
    
    // Remover clase de pendiente si estaba
    if (fila) {
        fila.classList.remove('table-secondary');
    }
}

function editarCantidad(detalleId, productoId, codigo, cantidadActual) {
    document.getElementById('modal_detalle_id').value = detalleId;
    document.getElementById('modal_producto_id').value = productoId;
    document.getElementById('modal_producto_codigo').textContent = codigo;
    document.getElementById('modal_cantidad_fisica').value = cantidadActual;
    
    const modal = new bootstrap.Modal(document.getElementById('editarCantidadModal'));
    modal.show();
}

function guardarCantidadManual() {
    const detalleId = document.getElementById('modal_detalle_id').value;
    const productoId = document.getElementById('modal_producto_id').value;
    const cantidadFisica = Math.round(parseFloat(document.getElementById('modal_cantidad_fisica').value) || 0);
    
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
            actualizarFilaProducto(productoId, data.detalle.cantidad_fisica, data.detalle.diferencia);
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('editarCantidadModal'));
            modal.hide();
            
            // Actualizar contador si es necesario
            location.reload(); // Recargar para actualizar estadísticas
        } else {
            alert(data.message || 'Error al actualizar la cantidad');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar la cantidad');
    });
}

// Sonidos (opcional)
function playBeep() {
    // Crear un beep simple usando Web Audio API
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.1);
    } catch (e) {
        // Ignorar si no se puede reproducir sonido
    }
}

function playErrorBeep() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 400;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.2);
    } catch (e) {
        // Ignorar
    }
}
</script>
@endpush
@endsection
