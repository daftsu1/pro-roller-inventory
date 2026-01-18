@extends('layouts.app')

@section('title', 'Movimientos de Inventario')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Movimientos de Inventario</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoMovimientoModal" onclick="abrirNuevoMovimiento()">
        <i class="bi bi-plus-circle"></i> Nuevo Movimiento
    </button>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('movimientos.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <select name="tipo" class="form-select">
                        <option value="">Todos los tipos</option>
                        <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                        <option value="salida" {{ request('tipo') == 'salida' ? 'selected' : '' }}>Salida</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="producto_id" class="form-select">
                        <option value="">Todos los productos</option>
                        @foreach($productos as $producto)
                            <option value="{{ $producto->id }}" {{ request('producto_id') == $producto->id ? 'selected' : '' }}>
                                {{ $producto->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}" placeholder="Desde">
                </div>
                <div class="col-md-2">
                    <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}" placeholder="Hasta">
                </div>
                <div class="col-md-2">
                    <select name="origen" class="form-select">
                        <option value="">Todos los orígenes</option>
                        <option value="ventas" {{ request('origen') == 'ventas' ? 'selected' : '' }}>Por Ventas</option>
                        <option value="manual" {{ request('origen') == 'manual' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de movimientos -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Motivo</th>
                        <th>Usuario</th>
                        <th>Origen</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $movimiento)
                    <tr>
                        <td>{{ $movimiento->fecha->format('d/m/Y') }}</td>
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
                        <td>{{ $movimiento->usuario->nombre }}</td>
                        <td>
                            @if($movimiento->venta_id)
                                <a href="{{ route('ventas.show', $movimiento->venta) }}" class="badge bg-info text-decoration-none">
                                    Venta #{{ $movimiento->venta->numero_factura }}
                                </a>
                            @else
                                <span class="badge bg-secondary">Manual</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="abrirVerMovimiento({{ $movimiento->id }})">
                                <i class="bi bi-eye"></i> Ver
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No se encontraron movimientos</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="mt-3">
            {{ $movimientos->links() }}
        </div>
    </div>
</div>

<!-- Modal para Nuevo Movimiento -->
<div class="modal fade" id="nuevoMovimientoModal" tabindex="-1" aria-labelledby="nuevoMovimientoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevoMovimientoModalLabel">Nuevo Movimiento de Inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="movimientoForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="producto_buscar_movimiento" class="form-label">Buscar Producto *</label>
                            <div class="position-relative">
                                <input type="text" 
                                       class="form-control" 
                                       id="producto_buscar_movimiento" 
                                       placeholder="Escribe código o nombre del producto..."
                                       autocomplete="off">
                                <div id="productos_lista_movimiento" class="list-group position-absolute w-100" style="z-index: 1050; max-height: 300px; overflow-y: auto; display: none;">
                                    <!-- Los resultados se cargarán aquí dinámicamente -->
                                </div>
                            </div>
                            <input type="hidden" id="producto_id_movimiento" name="producto_id" value="">
                            <small class="text-muted" id="producto_info_movimiento" style="display:none;"></small>
                            <div class="invalid-feedback" id="producto_error"></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="tipo_movimiento" class="form-label">Tipo *</label>
                            <select class="form-select" id="tipo_movimiento" name="tipo" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="entrada">Entrada</option>
                                <option value="salida">Salida</option>
                            </select>
                            <div class="invalid-feedback" id="tipo_error"></div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="cantidad_movimiento" class="form-label">Cantidad *</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" 
                                   id="cantidad_movimiento" name="cantidad" required>
                            <small class="text-muted" id="stock_info_movimiento"></small>
                            <div class="invalid-feedback" id="cantidad_error"></div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="fecha_movimiento" class="form-label">Fecha *</label>
                            <input type="date" class="form-control" 
                                   id="fecha_movimiento" name="fecha" value="{{ date('Y-m-d') }}" required>
                            <div class="invalid-feedback" id="fecha_error"></div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="referencia_movimiento" class="form-label">Referencia</label>
                            <input type="text" class="form-control" 
                                   id="referencia_movimiento" name="referencia">
                            <div class="invalid-feedback" id="referencia_error"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="motivo_movimiento" class="form-label">Motivo *</label>
                        <textarea class="form-control" 
                                  id="motivo_movimiento" name="motivo" rows="3" required></textarea>
                        <small class="text-muted">Ejemplos: "Compra a proveedor", "Ajuste de inventario", "Merma detectada", etc.</small>
                        <div class="invalid-feedback" id="motivo_error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Registrar Movimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Ver Detalles del Movimiento -->
<div class="modal fade" id="verMovimientoModal" tabindex="-1" aria-labelledby="verMovimientoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verMovimientoModalLabel">Detalle de Movimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="verMovimientoContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let movimientoModal = null;
// Datos de productos para autocompletado
window.productosDataMovimiento = @json($productos);

document.addEventListener('DOMContentLoaded', function() {
    movimientoModal = new bootstrap.Modal(document.getElementById('nuevoMovimientoModal'));
    
    // Manejar envío del formulario
    document.getElementById('movimientoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarMovimiento();
    });
    
    // Limpiar formulario cuando se cierra el modal
    document.getElementById('nuevoMovimientoModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('movimientoForm').reset();
        document.getElementById('producto_id_movimiento').value = '';
        document.getElementById('fecha_movimiento').value = '{{ date('Y-m-d') }}';
        limpiarErroresMovimiento();
    });
    
    // Autocompletado de productos
    const productoBuscar = document.getElementById('producto_buscar_movimiento');
    const productosLista = document.getElementById('productos_lista_movimiento');
    const productoIdMovimiento = document.getElementById('producto_id_movimiento');
    const productoInfo = document.getElementById('producto_info_movimiento');
    
    if (productoBuscar) {
        productoBuscar.addEventListener('input', function() {
            const busqueda = this.value.toLowerCase().trim();
            
            if (busqueda.length < 2) {
                productosLista.style.display = 'none';
                productoIdMovimiento.value = '';
                productoInfo.style.display = 'none';
                return;
            }
            
            // Filtrar productos
            const resultados = window.productosDataMovimiento.filter(p => 
                p.nombre.toLowerCase().includes(busqueda) ||
                p.codigo.toLowerCase().includes(busqueda)
            ).slice(0, 10);
            
            if (resultados.length === 0) {
                productosLista.innerHTML = '<div class="list-group-item text-muted">No se encontraron productos</div>';
                productosLista.style.display = 'block';
                return;
            }
            
            // Mostrar resultados
            let html = '';
            resultados.forEach(producto => {
                const nombreEscapado = producto.nombre.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                html += `
                    <button type="button" 
                            class="list-group-item list-group-item-action" 
                            onclick="seleccionarProductoMovimiento(${producto.id}, '${nombreEscapado}', ${producto.stock_actual || 0}, '${producto.codigo || ''}')">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${producto.nombre}</strong>
                                <br>
                                <small class="text-muted">Código: ${producto.codigo || '-'} | Stock: ${producto.stock_actual || 0}</small>
                            </div>
                        </div>
                    </button>
                `;
            });
            
            productosLista.innerHTML = html;
            productosLista.style.display = 'block';
        });
        
        // Cerrar lista al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!productoBuscar.contains(e.target) && !productosLista.contains(e.target)) {
                productosLista.style.display = 'none';
            }
        });
        
        productoBuscar.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                productosLista.style.display = 'none';
                this.blur();
            }
        });
    }
    
    // Actualizar stock info cuando cambia el tipo
    const tipoSelect = document.getElementById('tipo_movimiento');
    const cantidadInput = document.getElementById('cantidad_movimiento');
    
    if (tipoSelect && cantidadInput) {
        tipoSelect.addEventListener('change', function() {
            actualizarStockInfoMovimiento();
        });
        cantidadInput.addEventListener('input', function() {
            actualizarStockInfoMovimiento();
        });
    }
});

// Función para seleccionar producto
window.seleccionarProductoMovimiento = function(id, nombre, stock, codigo) {
    document.getElementById('producto_id_movimiento').value = id;
    document.getElementById('producto_buscar_movimiento').value = `${codigo} - ${nombre}`;
    document.getElementById('productos_lista_movimiento').style.display = 'none';
    
    const productoInfo = document.getElementById('producto_info_movimiento');
    productoInfo.innerHTML = `
        <strong>${nombre}</strong> | 
        Stock disponible: <span class="badge bg-info">${stock}</span>
    `;
    productoInfo.style.display = 'block';
    
    actualizarStockInfoMovimiento();
};

function actualizarStockInfoMovimiento() {
    const tipo = document.getElementById('tipo_movimiento').value;
    const cantidad = document.getElementById('cantidad_movimiento').value;
    const productoId = document.getElementById('producto_id_movimiento').value;
    const stockInfo = document.getElementById('stock_info_movimiento');
    
    if (!productoId) {
        if (stockInfo) stockInfo.textContent = '';
        return;
    }
    
    const producto = window.productosDataMovimiento.find(p => p.id == productoId);
    if (!producto) return;
    
    const stockActual = producto.stock_actual || 0;
    
    if (tipo === 'salida') {
        stockInfo.innerHTML = `Stock actual: <strong>${stockActual}</strong>`;
        document.getElementById('cantidad_movimiento').max = stockActual;
        
        if (cantidad && parseFloat(cantidad) > stockActual) {
            stockInfo.className = 'text-danger';
            stockInfo.textContent = `Stock actual: ${stockActual} - ¡Cantidad excede el stock disponible!`;
        } else {
            stockInfo.className = 'text-muted';
        }
    } else {
        stockInfo.innerHTML = `Stock actual: <strong>${stockActual}</strong>`;
        stockInfo.className = 'text-muted';
    }
}

function abrirNuevoMovimiento() {
    document.getElementById('movimientoForm').reset();
    document.getElementById('producto_id_movimiento').value = '';
    document.getElementById('fecha_movimiento').value = '{{ date('Y-m-d') }}';
    document.getElementById('producto_info_movimiento').style.display = 'none';
    limpiarErroresMovimiento();
    if (movimientoModal) {
        movimientoModal.show();
    }
}

function guardarMovimiento() {
    const formData = new FormData(document.getElementById('movimientoForm'));
    
    fetch('/movimientos', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        return response.json().then(data => {
            return { status: response.status, data };
        });
    })
    .then(({ status, data }) => {
        if (status === 200 && data.success) {
            if (movimientoModal) movimientoModal.hide();
            window.location.reload();
        } else {
            mostrarErroresMovimiento(data.errors || {});
            if (data.message) {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al registrar el movimiento');
    });
}

function mostrarErroresMovimiento(errors) {
    limpiarErroresMovimiento();
    Object.keys(errors).forEach(field => {
        const input = document.getElementById(`${field}_movimiento`);
        const errorDiv = document.getElementById(`${field}_error`);
        if (input) {
            input.classList.add('is-invalid');
            if (errorDiv) {
                errorDiv.textContent = errors[field][0];
            }
        }
    });
}

function limpiarErroresMovimiento() {
    document.querySelectorAll('#movimientoForm .is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('#movimientoForm .invalid-feedback').forEach(el => {
        el.textContent = '';
    });
}

// Abrir modal para ver detalles del movimiento
function abrirVerMovimiento(movimientoId) {
    const verMovimientoModal = new bootstrap.Modal(document.getElementById('verMovimientoModal'));
    const content = document.getElementById('verMovimientoContent');
    
    // Mostrar loading
    content.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
    verMovimientoModal.show();
    
    fetch(`/movimientos/${movimientoId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP error! status: ${response.status} - ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.movimiento) {
            const m = data.movimiento;
            const p = m.producto;
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6 class="mb-3">Información del Movimiento</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Fecha:</th>
                                <td>${m.fecha}</td>
                            </tr>
                            <tr>
                                <th>Producto:</th>
                                <td>${p.nombre}</td>
                            </tr>
                            <tr>
                                <th>Tipo:</th>
                                <td>${m.tipo === 'entrada' ? '<span class="badge bg-success">Entrada</span>' : '<span class="badge bg-danger">Salida</span>'}</td>
                            </tr>
                            <tr>
                                <th>Cantidad:</th>
                                <td><strong>${m.cantidad}</strong></td>
                            </tr>
                            <tr>
                                <th>Motivo:</th>
                                <td>${m.motivo || '-'}</td>
                            </tr>
                            <tr>
                                <th>Referencia:</th>
                                <td>${m.referencia || '-'}</td>
                            </tr>
                            <tr>
                                <th>Usuario:</th>
                                <td>${m.usuario || '-'}</td>
                            </tr>
                            <tr>
                                <th>Origen:</th>
                                <td>${m.venta_id ? `<a href="/ventas/${m.venta_id}" class="badge bg-info text-decoration-none">Venta #${m.venta_numero_factura || m.venta_id}</a>` : '<span class="badge bg-secondary">Movimiento Manual</span>'}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6 class="mb-3">Información del Producto</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Código:</th>
                                <td>${p.codigo || '-'}</td>
                            </tr>
                            <tr>
                                <th>Nombre:</th>
                                <td>${p.nombre || '-'}</td>
                            </tr>
                            <tr>
                                <th>Stock Actual:</th>
                                <td>${p.tiene_stock_minimo ? `<span class="badge bg-warning">${p.stock_actual}</span>` : `<span class="badge bg-success">${p.stock_actual}</span>`}</td>
                            </tr>
                            <tr>
                                <th>Stock Mínimo:</th>
                                <td>${p.stock_minimo || '-'}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
        } else {
            content.innerHTML = '<div class="alert alert-danger">Error al cargar los detalles del movimiento.</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = `<div class="alert alert-danger">Error al cargar los detalles del movimiento.<br><small>${error.message}</small></div>`;
    });
}
</script>
@endpush
@endsection
