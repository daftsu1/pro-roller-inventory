@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Clientes</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clienteModal" onclick="abrirNuevoCliente()">
        <i class="bi bi-plus-circle"></i> Nuevo Cliente
    </button>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('clientes.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre, documento, teléfono o email..." value="{{ request('buscar') }}">
                </div>
                <div class="col-md-2">
                    <select name="activo" class="form-select">
                        <option value="">Todos</option>
                        <option value="1" {{ request('activo') == '1' ? 'selected' : '' }}>Activos</option>
                        <option value="0" {{ request('activo') == '0' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de clientes -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Total Ventas</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                    <tr>
                        <td>{{ $cliente->nombre }}</td>
                        <td>{{ $cliente->documento ?? '-' }}</td>
                        <td>{{ $cliente->telefono ?? '-' }}</td>
                        <td>{{ $cliente->email ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info">{{ $cliente->ventas_count }}</span>
                        </td>
                        <td>
                            @if($cliente->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-outline-info">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <button type="button" class="btn btn-outline-primary" onclick="abrirEditarCliente({{ $cliente->id }})">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este cliente? Esta acción no se puede deshacer.');">
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
                            No se encontraron clientes
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="mt-3">
            {{ $clientes->links() }}
        </div>
    </div>
</div>

<!-- Modal de Cliente -->
<div class="modal fade" id="clienteModal" tabindex="-1" aria-labelledby="clienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clienteModalLabel">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="clienteForm">
                @csrf
                <input type="hidden" id="cliente_id" name="cliente_id" value="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_modal" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre_modal" name="nombre" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="documento_modal" class="form-label">Documento</label>
                            <input type="text" class="form-control" id="documento_modal" name="documento">
                            <div class="invalid-feedback"></div>
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
                    
                    <div class="mb-3">
                        <label for="direccion_modal" class="form-label">Dirección</label>
                        <textarea class="form-control" id="direccion_modal" name="direccion" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="activo_modal" name="activo" value="1" checked>
                            <label class="form-check-label" for="activo_modal">
                                Cliente Activo
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let clienteModal = null;
let clienteActual = null;

document.addEventListener('DOMContentLoaded', function() {
    clienteModal = new bootstrap.Modal(document.getElementById('clienteModal'));
    
    // Manejar envío del formulario
    document.getElementById('clienteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarCliente();
    });
    
    // Limpiar formulario cuando se cierra el modal
    document.getElementById('clienteModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('clienteForm').reset();
        document.getElementById('cliente_id').value = '';
        clienteActual = null;
        limpiarErrores();
    });
});

function abrirNuevoCliente() {
    clienteActual = null;
    document.getElementById('clienteModalLabel').textContent = 'Nuevo Cliente';
    document.getElementById('cliente_id').value = '';
    document.getElementById('clienteForm').reset();
    document.getElementById('activo_modal').checked = true;
    limpiarErrores();
    clienteModal.show();
}

function abrirEditarCliente(clienteId) {
    fetch(`/clientes/${clienteId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || data.cliente) {
            const cliente = data.cliente || data;
            clienteActual = cliente;
            document.getElementById('clienteModalLabel').textContent = 'Editar Cliente';
            document.getElementById('cliente_id').value = cliente.id || '';
            
            // Limpiar errores antes de poblar los campos
            limpiarErrores();
            
            // Poblar campos
            const nombreField = document.getElementById('nombre_modal');
            nombreField.value = cliente.nombre || '';
            nombreField.classList.remove('is-invalid');
            
            document.getElementById('documento_modal').value = cliente.documento || '';
            document.getElementById('telefono_modal').value = cliente.telefono || '';
            document.getElementById('email_modal').value = cliente.email || '';
            document.getElementById('direccion_modal').value = cliente.direccion || '';
            document.getElementById('activo_modal').checked = cliente.activo !== false;
            
            clienteModal.show();
        } else {
            alert('Error al cargar el cliente');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar el cliente');
    });
}

function guardarCliente() {
    const clienteId = document.getElementById('cliente_id').value;
    const url = clienteId ? `/clientes/${clienteId}` : '/clientes';
    
    // Recopilar datos del formulario
    const data = {
        nombre: document.getElementById('nombre_modal').value.trim(),
        documento: document.getElementById('documento_modal').value.trim() || null,
        telefono: document.getElementById('telefono_modal').value.trim() || null,
        email: document.getElementById('email_modal').value.trim() || null,
        direccion: document.getElementById('direccion_modal').value.trim() || null,
        activo: document.getElementById('activo_modal').checked ? 1 : 0
    };
    
    // Para PUT, necesitamos enviar _method
    if (clienteId) {
        data._method = 'PUT';
    }
    
    fetch(url, {
        method: clienteId ? 'POST' : 'POST', // Laravel usa POST con _method para PUT
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        return response.json().then(data => {
            return { status: response.status, data };
        });
    })
    .then(({ status, data }) => {
        if (status === 200 && (data.success || !data.errors)) {
            clienteModal.hide();
            // Recargar la página para actualizar la tabla
            window.location.reload();
        } else {
            // Mostrar errores de validación
            mostrarErrores(data.errors || {});
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar el cliente');
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
    document.querySelectorAll('#clienteForm .is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
        el.setCustomValidity(''); // Limpiar validación personalizada del navegador
    });
    document.querySelectorAll('#clienteForm .invalid-feedback').forEach(el => {
        el.textContent = '';
    });
    // Limpiar estado de validación del formulario
    const form = document.getElementById('clienteForm');
    if (form) {
        form.classList.remove('was-validated');
    }
}
</script>
@endpush
@endsection
