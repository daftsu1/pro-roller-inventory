@extends('layouts.app')

@section('title', 'Nueva Venta')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Nueva Venta</h2>
    <a href="{{ route('ventas.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('ventas.store') }}" method="POST" id="ventaForm">
            @csrf
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="fecha" class="form-label">Fecha *</label>
                    <input type="date" class="form-control @error('fecha') is-invalid @enderror" 
                           id="fecha" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required>
                    @error('fecha')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4">
                    <label for="cliente_nombre" class="form-label">Cliente</label>
                    <input type="text" class="form-control @error('cliente_nombre') is-invalid @enderror" 
                           id="cliente_nombre" name="cliente_nombre" value="{{ old('cliente_nombre') }}">
                    @error('cliente_nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4">
                    <label for="cliente_documento" class="form-label">Documento</label>
                    <input type="text" class="form-control @error('cliente_documento') is-invalid @enderror" 
                           id="cliente_documento" name="cliente_documento" value="{{ old('cliente_documento') }}">
                    @error('cliente_documento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Productos -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Productos</h5>
                    <button type="button" class="btn btn-sm btn-primary" onclick="agregarProducto()">
                        <i class="bi bi-plus-circle"></i> Agregar Producto
                    </button>
                </div>
                <div class="card-body">
                    <div id="productosContainer">
                        <!-- Productos se agregarán aquí -->
                    </div>
                    
                    <div class="d-flex justify-content-end mt-3">
                        <h4>Total: $<span id="totalVenta">0.00</span></h4>
                    </div>
                </div>
            </div>
            
            <div id="productosHiddenInputs"></div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('ventas.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Registrar Venta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para agregar producto -->
<div class="modal fade" id="productoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="producto_select" class="form-label">Producto *</label>
                    <select class="form-select" id="producto_select">
                        <option value="">Seleccionar producto</option>
                        @foreach($productos as $producto)
                            <option value="{{ $producto->id }}" 
                                    data-nombre="{{ $producto->nombre }}"
                                    data-precio="{{ $producto->precio_venta }}"
                                    data-stock="{{ $producto->stock_actual }}">
                                {{ $producto->nombre }} - Stock: {{ $producto->stock_actual }} - ${{ number_format($producto->precio_venta, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="cantidad_producto" class="form-label">Cantidad *</label>
                    <input type="number" class="form-control" id="cantidad_producto" min="1" value="1">
                    <small class="text-muted" id="stock_disponible"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarProducto()">Agregar</button>
            </div>
        </div>
    </div>
</div>

<script>
let productosAgregados = [];
let productoModal = null;

document.addEventListener('DOMContentLoaded', function() {
    productoModal = new bootstrap.Modal(document.getElementById('productoModal'));
    
    document.getElementById('producto_select').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const stock = option.getAttribute('data-stock');
        document.getElementById('stock_disponible').textContent = `Stock disponible: ${stock}`;
        document.getElementById('cantidad_producto').max = stock;
    });
});

function agregarProducto() {
    productoModal.show();
}

function confirmarProducto() {
    const select = document.getElementById('producto_select');
    const option = select.options[select.selectedIndex];
    
    if (!select.value) {
        alert('Selecciona un producto');
        return;
    }
    
    const cantidad = parseInt(document.getElementById('cantidad_producto').value);
    const stock = parseInt(option.getAttribute('data-stock'));
    
    if (cantidad > stock) {
        alert(`Stock insuficiente. Stock disponible: ${stock}`);
        return;
    }
    
    const productoId = select.value;
    const nombre = option.getAttribute('data-nombre');
    const precio = parseFloat(option.getAttribute('data-precio'));
    
    // Verificar si ya existe
    if (productosAgregados.find(p => p.id == productoId)) {
        alert('Este producto ya fue agregado');
        return;
    }
    
    productosAgregados.push({
        id: productoId,
        nombre: nombre,
        cantidad: cantidad,
        precio: precio,
        subtotal: cantidad * precio
    });
    
    actualizarTablaProductos();
    productoModal.hide();
    
    // Limpiar formulario
    select.value = '';
    document.getElementById('cantidad_producto').value = 1;
    document.getElementById('stock_disponible').textContent = '';
}

function eliminarProducto(index) {
    productosAgregados.splice(index, 1);
    actualizarTablaProductos();
}

function actualizarTablaProductos() {
    const container = document.getElementById('productosContainer');
    const hiddenInputsContainer = document.getElementById('productosHiddenInputs');
    
    if (productosAgregados.length === 0) {
        container.innerHTML = '<p class="text-muted">No hay productos agregados</p>';
        hiddenInputsContainer.innerHTML = '';
        document.getElementById('totalVenta').textContent = '0.00';
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unit.</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    let total = 0;
    let hiddenInputs = '';
    
    productosAgregados.forEach((producto, index) => {
        total += producto.subtotal;
        html += `
            <tr>
                <td>${producto.nombre}</td>
                <td>${producto.cantidad}</td>
                <td>$${producto.precio.toFixed(2)}</td>
                <td>$${producto.subtotal.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        // Crear campos hidden para cada producto
        hiddenInputs += `<input type="hidden" name="productos[${index}][id]" value="${producto.id}">`;
        hiddenInputs += `<input type="hidden" name="productos[${index}][cantidad]" value="${producto.cantidad}">`;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
    hiddenInputsContainer.innerHTML = hiddenInputs;
    document.getElementById('totalVenta').textContent = total.toFixed(2);
}

document.getElementById('ventaForm').addEventListener('submit', function(e) {
    if (productosAgregados.length === 0) {
        e.preventDefault();
        alert('Debes agregar al menos un producto');
        return false;
    }
});
</script>
@endsection
