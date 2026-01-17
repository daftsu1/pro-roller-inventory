@extends('layouts.app')

@section('title', 'Nuevo Movimiento')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Nuevo Movimiento de Inventario</h2>
    <a href="{{ route('movimientos.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('movimientos.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="producto_id" class="form-label">Producto *</label>
                    <select class="form-select @error('producto_id') is-invalid @enderror" 
                            id="producto_id" name="producto_id" required>
                        <option value="">Seleccionar producto</option>
                        @foreach($productos as $producto)
                            <option value="{{ $producto->id }}" 
                                    data-stock="{{ $producto->stock_actual }}"
                                    {{ old('producto_id') == $producto->id ? 'selected' : '' }}>
                                {{ $producto->nombre }} (Stock: {{ $producto->stock_actual }})
                            </option>
                        @endforeach
                    </select>
                    @error('producto_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="tipo" class="form-label">Tipo *</label>
                    <select class="form-select @error('tipo') is-invalid @enderror" 
                            id="tipo" name="tipo" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="entrada" {{ old('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                        <option value="salida" {{ old('tipo') == 'salida' ? 'selected' : '' }}>Salida</option>
                    </select>
                    @error('tipo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="cantidad" class="form-label">Cantidad *</label>
                    <input type="number" step="0.01" min="0.01" class="form-control @error('cantidad') is-invalid @enderror" 
                           id="cantidad" name="cantidad" value="{{ old('cantidad') }}" required>
                    @error('cantidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted" id="stock_info"></small>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="fecha" class="form-label">Fecha *</label>
                    <input type="date" class="form-control @error('fecha') is-invalid @enderror" 
                           id="fecha" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required>
                    @error('fecha')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="referencia" class="form-label">Referencia</label>
                    <input type="text" class="form-control @error('referencia') is-invalid @enderror" 
                           id="referencia" name="referencia" value="{{ old('referencia') }}">
                    @error('referencia')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="mb-3">
                <label for="motivo" class="form-label">Motivo *</label>
                <textarea class="form-control @error('motivo') is-invalid @enderror" 
                          id="motivo" name="motivo" rows="3" required>{{ old('motivo') }}</textarea>
                @error('motivo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Ejemplos: "Compra a proveedor", "Ajuste de inventario", "Merma detectada", etc.</small>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('movimientos.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Registrar Movimiento
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('producto_id').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const stock = option.getAttribute('data-stock');
    if (stock) {
        document.getElementById('stock_info').textContent = `Stock actual: ${stock}`;
    } else {
        document.getElementById('stock_info').textContent = '';
    }
});

document.getElementById('tipo').addEventListener('change', function() {
    const tipo = this.value;
    const cantidadInput = document.getElementById('cantidad');
    const productoSelect = document.getElementById('producto_id');
    
    if (tipo === 'salida' && productoSelect.value) {
        const option = productoSelect.options[productoSelect.selectedIndex];
        const stock = parseInt(option.getAttribute('data-stock'));
        cantidadInput.max = stock;
    }
});
</script>
@endsection
