@extends('layouts.app')

@section('title', 'Nuevo Conteo Físico')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clipboard-check"></i> Nuevo Conteo Físico</h2>
    <a href="{{ route('conteos.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('conteos.store') }}" method="POST" id="conteoForm">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">Nombre del Conteo</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                           id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Opcional - Se generará automáticamente si se deja vacío">
                    <small class="text-muted">Si lo dejas vacío, se generará automáticamente con la fecha</small>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="fecha_conteo" class="form-label">Fecha del Conteo *</label>
                    <input type="date" class="form-control @error('fecha_conteo') is-invalid @enderror" 
                           id="fecha_conteo" name="fecha_conteo" value="{{ old('fecha_conteo', date('Y-m-d')) }}" required>
                    @error('fecha_conteo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                          id="descripcion" name="descripcion" rows="2" placeholder="Descripción opcional del conteo...">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <hr>
            
            <h5 class="mb-3">Selección de Productos</h5>
            
            <div class="mb-3">
                <label class="form-label">Tipo de Selección *</label>
                <div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_seleccion" id="tipo_todos" value="todos" checked onchange="toggleSeleccion()">
                        <label class="form-check-label" for="tipo_todos">
                            Todos los productos activos con stock
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_seleccion" id="tipo_categoria" value="categoria" onchange="toggleSeleccion()">
                        <label class="form-check-label" for="tipo_categoria">
                            Por categoría (con stock)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_seleccion" id="tipo_manual" value="manual" onchange="toggleSeleccion()">
                        <label class="form-check-label" for="tipo_manual">
                            Selección manual (solo con stock)
                        </label>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle"></i> Solo se incluyen productos con stock > 0. 
                        Si encuentra productos sin stock, puede agregarlos después desde el botón "Agregar Productos".
                    </small>
                </div>
            </div>
            
            <!-- Selección por categoría -->
            <div class="mb-3" id="categoria_group" style="display: none;">
                <label for="categoria_id" class="form-label">Categoría *</label>
                <select class="form-select @error('categoria_id') is-invalid @enderror" 
                        id="categoria_id" name="categoria_id">
                    <option value="">Seleccionar categoría</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nombre }} ({{ $categoria->productos->count() }} productos)
                        </option>
                    @endforeach
                </select>
                @error('categoria_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Selección manual -->
            <div class="mb-3" id="manual_group" style="display: none;">
                <label class="form-label">Productos *</label>
                <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                    <div class="mb-2">
                        <input type="text" class="form-control form-control-sm" id="buscar_producto" placeholder="Buscar producto...">
                    </div>
                    <div id="productos_lista">
                        @foreach($productos as $producto)
                        <div class="form-check producto-item" data-nombre="{{ strtolower($producto->nombre) }}" data-codigo="{{ strtolower($producto->codigo) }}">
                            <input class="form-check-input" type="checkbox" name="productos[]" 
                                   value="{{ $producto->id }}" id="producto_{{ $producto->id }}">
                            <label class="form-check-label" for="producto_{{ $producto->id }}">
                                <strong>{{ $producto->codigo }}</strong> - {{ $producto->nombre }}
                                <small class="text-muted">(Stock: {{ $producto->stock_actual }})</small>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <small class="text-muted">Seleccione los productos que desea incluir en el conteo</small>
                @error('productos')
                    <div class="alert alert-danger mt-2">
                        <strong>Error:</strong> {{ $message }}
                    </div>
                @enderror
                @if($errors->has('productos.*'))
                    <div class="alert alert-danger mt-2">
                        <strong>Error en productos:</strong>
                        <ul class="mb-0">
                            @foreach($errors->get('productos.*') as $error)
                                <li>{{ $error[0] }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Crear Conteo
                </button>
                <a href="{{ route('conteos.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleSeleccion() {
    const tipoTodos = document.getElementById('tipo_todos').checked;
    const tipoCategoria = document.getElementById('tipo_categoria').checked;
    const tipoManual = document.getElementById('tipo_manual').checked;
    
    document.getElementById('categoria_group').style.display = tipoCategoria ? 'block' : 'none';
    document.getElementById('manual_group').style.display = tipoManual ? 'block' : 'none';
    
    // Hacer requerido o no según el tipo
    const categoriaSelect = document.getElementById('categoria_id');
    if (tipoCategoria) {
        categoriaSelect.setAttribute('required', 'required');
    } else {
        categoriaSelect.removeAttribute('required');
    }
}

// Búsqueda de productos en selección manual
document.getElementById('buscar_producto')?.addEventListener('input', function() {
    const busqueda = this.value.toLowerCase();
    const items = document.querySelectorAll('.producto-item');
    
    items.forEach(item => {
        const nombre = item.getAttribute('data-nombre');
        const codigo = item.getAttribute('data-codigo');
        
        if (busqueda === '' || nombre.includes(busqueda) || codigo.includes(busqueda)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
            // IMPORTANTE: NO desmarcar checkboxes ocultos, solo ocultar visualmente
        }
    });
});

// Validación del formulario
document.getElementById('conteoForm')?.addEventListener('submit', function(e) {
    const tipoManual = document.getElementById('tipo_manual').checked;
    
    if (tipoManual) {
        // Buscar TODOS los checkboxes, incluso los ocultos
        const productosSeleccionados = document.querySelectorAll('input[name="productos[]"]:checked');
        console.log('Productos seleccionados:', productosSeleccionados.length);
        
        if (productosSeleccionados.length === 0) {
            e.preventDefault();
            alert('Debe seleccionar al menos un producto para el conteo.');
            return false;
        }
        
        // Verificar que los valores se están enviando
        const valores = Array.from(productosSeleccionados).map(cb => cb.value);
        console.log('Valores de productos:', valores);
        
        // Asegurar que los checkboxes ocultos también se envíen
        productosSeleccionados.forEach(cb => {
            // Si el checkbox está oculto por el filtro, asegurar que se envíe
            const item = cb.closest('.producto-item');
            if (item && item.style.display === 'none') {
                // Crear un input hidden para asegurar que se envíe
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'productos[]';
                hidden.value = cb.value;
                this.appendChild(hidden);
            }
        });
    }
    
    // Mostrar loading al enviar
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creando...';
    }
});
</script>
@endpush
@endsection
