@extends('layouts.app')

@section('title', 'Categorías')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Categorías</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoriaModal" onclick="abrirNuevaCategoria()">
        <i class="bi bi-plus-circle"></i> Nueva Categoría
    </button>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('categorias.index') }}">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre o descripción..." value="{{ request('buscar') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de categorías -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Productos</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categorias as $categoria)
                    <tr>
                        <td>{{ $categoria->nombre }}</td>
                        <td>{{ $categoria->descripcion ?? '-' }}</td>
                        <td>
                            <span class="badge bg-info">{{ $categoria->productos_count }}</span>
                        </td>
                        <td>{{ $categoria->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('categorias.show', $categoria) }}" class="btn btn-outline-info">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <button type="button" class="btn btn-outline-primary" onclick="abrirEditarCategoria({{ $categoria->id }})">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta categoría? Esta acción no se puede deshacer.');">
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
                        <td colspan="5" class="text-center text-muted">
                            <i class="bi bi-inbox"></i><br>
                            No se encontraron categorías
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="mt-3">
            {{ $categorias->links() }}
        </div>
    </div>
</div>

<!-- Modal de Categoría -->
<div class="modal fade" id="categoriaModal" tabindex="-1" aria-labelledby="categoriaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoriaModalLabel">Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="categoriaForm">
                @csrf
                <input type="hidden" id="categoria_id" name="categoria_id" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre_modal" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre_modal" name="nombre" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion_modal" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion_modal" name="descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Guardar Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let categoriaModal = null;
let categoriaActual = null;

document.addEventListener('DOMContentLoaded', function() {
    categoriaModal = new bootstrap.Modal(document.getElementById('categoriaModal'));
    
    // Manejar envío del formulario
    document.getElementById('categoriaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarCategoria();
    });
    
    // Limpiar formulario cuando se cierra el modal
    document.getElementById('categoriaModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('categoriaForm').reset();
        document.getElementById('categoria_id').value = '';
        categoriaActual = null;
        limpiarErrores();
    });
});

function abrirNuevaCategoria() {
    categoriaActual = null;
    document.getElementById('categoriaModalLabel').textContent = 'Nueva Categoría';
    document.getElementById('categoria_id').value = '';
    document.getElementById('categoriaForm').reset();
    limpiarErrores();
    categoriaModal.show();
}

function abrirEditarCategoria(categoriaId) {
    fetch(`/categorias/${categoriaId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || data.categoria) {
            const categoria = data.categoria || data;
            categoriaActual = categoria;
            document.getElementById('categoriaModalLabel').textContent = 'Editar Categoría';
            document.getElementById('categoria_id').value = categoria.id;
            document.getElementById('nombre_modal').value = categoria.nombre || '';
            document.getElementById('descripcion_modal').value = categoria.descripcion || '';
            limpiarErrores();
            categoriaModal.show();
        } else {
            alert('Error al cargar la categoría');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar la categoría');
    });
}

function guardarCategoria() {
    const categoriaId = document.getElementById('categoria_id').value;
    const formData = new FormData(document.getElementById('categoriaForm'));
    const url = categoriaId ? `/categorias/${categoriaId}` : '/categorias';
    const method = categoriaId ? 'PUT' : 'POST';
    
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
            categoriaModal.hide();
            // Recargar la página para actualizar la tabla
            window.location.reload();
        } else {
            // Mostrar errores de validación
            mostrarErrores(data.errors || {});
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar la categoría');
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
    document.querySelectorAll('#categoriaForm .is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('#categoriaForm .invalid-feedback').forEach(el => {
        el.textContent = '';
    });
}
</script>
@endpush
@endsection
