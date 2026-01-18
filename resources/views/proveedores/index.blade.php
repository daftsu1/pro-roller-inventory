@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Proveedores</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#proveedorModal" onclick="abrirNuevoProveedor()">
        <i class="bi bi-plus-circle"></i> Nuevo Proveedor
    </button>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('proveedores.index') }}">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre, contacto, teléfono o email..." value="{{ request('buscar') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de proveedores -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Productos</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proveedores as $proveedor)
                    <tr>
                        <td>{{ $proveedor->nombre }}</td>
                        <td>{{ $proveedor->contacto ?? '-' }}</td>
                        <td>{{ $proveedor->telefono ?? '-' }}</td>
                        <td>{{ $proveedor->email ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info">{{ $proveedor->productos_count }}</span>
                        </td>
                        <td>{{ $proveedor->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-info" onclick="abrirVerProveedor({{ $proveedor->id }})">
                                    <i class="bi bi-eye"></i> Ver
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="abrirEditarProveedor({{ $proveedor->id }})">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <form action="{{ route('proveedores.destroy', $proveedor) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este proveedor? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            <i class="bi bi-inbox"></i><br>
                            No se encontraron proveedores
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="mt-3">
            {{ $proveedores->links() }}
        </div>
    </div>
</div>

<!-- Modal de Proveedor -->
<div class="modal fade" id="proveedorModal" tabindex="-1" aria-labelledby="proveedorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="proveedorModalLabel">Nuevo Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="proveedorForm">
                @csrf
                <input type="hidden" id="proveedor_id" name="proveedor_id" value="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_modal" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre_modal" name="nombre" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="contacto_modal" class="form-label">Contacto</label>
                            <input type="text" class="form-control" id="contacto_modal" name="contacto">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telefono_modal" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono_modal" name="telefono">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email_modal" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email_modal" name="email">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Ver Detalles del Proveedor -->
<div class="modal fade" id="verProveedorModal" tabindex="-1" aria-labelledby="verProveedorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verProveedorModalLabel">Detalle Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="verProveedorContent">
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
let proveedorModal = null;
let proveedorActual = null;

document.addEventListener('DOMContentLoaded', function() {
    proveedorModal = new bootstrap.Modal(document.getElementById('proveedorModal'));
    
    // Manejar envío del formulario
    document.getElementById('proveedorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarProveedor();
    });
    
    // Limpiar formulario cuando se cierra el modal
    document.getElementById('proveedorModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('proveedorForm').reset();
        document.getElementById('proveedor_id').value = '';
        proveedorActual = null;
        limpiarErrores();
    });
});

function abrirNuevoProveedor() {
    proveedorActual = null;
    document.getElementById('proveedorModalLabel').textContent = 'Nuevo Proveedor';
    document.getElementById('proveedor_id').value = '';
    document.getElementById('proveedorForm').reset();
    limpiarErrores();
    proveedorModal.show();
}

function abrirEditarProveedor(proveedorId) {
    fetch(`/proveedores/${proveedorId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || data.proveedor) {
            const proveedor = data.proveedor || data;
            proveedorActual = proveedor;
            document.getElementById('proveedorModalLabel').textContent = 'Editar Proveedor';
            document.getElementById('proveedor_id').value = proveedor.id;
            document.getElementById('nombre_modal').value = proveedor.nombre || '';
            document.getElementById('contacto_modal').value = proveedor.contacto || '';
            document.getElementById('telefono_modal').value = proveedor.telefono || '';
            document.getElementById('email_modal').value = proveedor.email || '';
            limpiarErrores();
            proveedorModal.show();
        } else {
            alert('Error al cargar el proveedor');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar el proveedor');
    });
}

function guardarProveedor() {
    const proveedorId = document.getElementById('proveedor_id').value;
    const formData = new FormData(document.getElementById('proveedorForm'));
    const url = proveedorId ? `/proveedores/${proveedorId}` : '/proveedores';
    const method = proveedorId ? 'PUT' : 'POST';
    
    // Agregar _method para PUT
    if (method === 'PUT') {
        formData.append('_method', 'PUT');
    }
    
    fetch(url, {
        method: method,
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
        if (status === 200 && (data.success || !data.errors)) {
            proveedorModal.hide();
            // Recargar la página para actualizar la tabla
            window.location.reload();
        } else {
            // Mostrar errores de validación
            mostrarErrores(data.errors || {});
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar el proveedor');
    });
}

function mostrarErrores(errors) {
    limpiarErrores();
    Object.keys(errors).forEach(field => {
        const input = document.getElementById(`${field}_modal`);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = input.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = errors[field][0];
            }
        }
    });
}

function limpiarErrores() {
    document.querySelectorAll('#proveedorForm .is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('#proveedorForm .invalid-feedback').forEach(el => {
        el.textContent = '';
    });
}

// Abrir modal para ver detalles del proveedor
function abrirVerProveedor(proveedorId) {
    const verProveedorModal = new bootstrap.Modal(document.getElementById('verProveedorModal'));
    const content = document.getElementById('verProveedorContent');
    
    // Mostrar loading
    content.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
    verProveedorModal.show();
    
    fetch(`/proveedores/${proveedorId}`, {
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
        console.log('Datos recibidos:', data); // Debug
        if (data.success && data.proveedor) {
            const p = data.proveedor;
            const productos = p.productos || [];
            
            let productosHtml = '';
            if (productos.length > 0) {
                productosHtml = `
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Productos (${productos.length})</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Nombre</th>
                                            <th>Precio Venta</th>
                                            <th>Stock</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${productos.map(producto => `
                                            <tr>
                                                <td>${producto.codigo || '-'}</td>
                                                <td>${producto.nombre || '-'}</td>
                                                <td>$${parseFloat(producto.precio_venta || 0).toLocaleString('es-CL')}</td>
                                                <td>${producto.stock_actual || 0}</td>
                                                <td>${producto.activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>'}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                productosHtml = '<div class="alert alert-info mt-3 mb-0"><i class="bi bi-info-circle"></i> Este proveedor no tiene productos asociados.</div>';
            }
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-12">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Nombre:</th>
                                <td>${p.nombre || '-'}</td>
                            </tr>
                            <tr>
                                <th>Contacto:</th>
                                <td>${p.contacto || '-'}</td>
                            </tr>
                            <tr>
                                <th>Teléfono:</th>
                                <td>${p.telefono || '-'}</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>${p.email ? `<a href="mailto:${p.email}">${p.email}</a>` : '-'}</td>
                            </tr>
                            <tr>
                                <th>Total Productos:</th>
                                <td><span class="badge bg-info">${productos.length}</span></td>
                            </tr>
                            <tr>
                                <th>Fecha Creación:</th>
                                <td>${p.created_at ? new Date(p.created_at).toLocaleDateString('es-CL', { year: 'numeric', month: '2-digit', day: '2-digit' }) : '-'}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                ${productosHtml}
            `;
        } else {
            content.innerHTML = '<div class="alert alert-danger">Error al cargar los detalles del proveedor.</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = `<div class="alert alert-danger">Error al cargar los detalles del proveedor.<br><small>${error.message}</small></div>`;
    });
}
</script>
@endpush
@endsection
