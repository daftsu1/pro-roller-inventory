@extends('layouts.app')

@section('title', 'Ventas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Ventas</h2>
    <a href="{{ route('ventas.create') }}" class="btn btn-primary" id="btnNuevaVenta">
        <i class="bi bi-plus-circle"></i> Nueva Venta
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form id="filtrosForm" method="GET" action="{{ route('ventas.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="numero_factura" id="numero_factura" class="form-control" placeholder="Número de factura" value="{{ request('numero_factura') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
                </div>
                <div class="col-md-3">
                    <select name="estado" id="estado_filtro" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completada</option>
                        <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <button type="button" id="btnBuscar" class="btn btn-secondary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <button type="button" id="btnLimpiar" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabs para separar ventas -->
<ul class="nav nav-tabs mb-3" id="ventasTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $estadoFiltro == 'pendiente' ? 'active' : '' }}" 
                id="pendientes-tab" 
                type="button" 
                role="tab"
                onclick="filtrarPorEstado('pendiente')">
            <i class="bi bi-clock"></i> Pendientes
            @if($ventasPendientes > 0)
                <span class="badge bg-warning ms-1" id="badgePendientes">{{ $ventasPendientes }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $estadoFiltro == 'completada' ? 'active' : '' }}" 
                id="completadas-tab" 
                type="button" 
                role="tab"
                onclick="filtrarPorEstado('completada')">
            <i class="bi bi-check-circle"></i> Completadas
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $estadoFiltro == 'cancelada' ? 'active' : '' }}" 
                id="canceladas-tab" 
                type="button" 
                role="tab"
                onclick="filtrarPorEstado('cancelada')">
            <i class="bi bi-x-circle"></i> Canceladas
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ !request('estado') || request('estado') == 'todas' ? 'active' : '' }}" 
                id="todas-tab" 
                type="button" 
                role="tab"
                onclick="filtrarPorEstado('todas')">
            <i class="bi bi-list"></i> Todas
        </button>
    </li>
</ul>

<!-- Tabla de ventas -->
<div class="card">
    <div class="card-body" id="tablaVentasContainer">
        @include('ventas._tabla', ['ventas' => $ventas])
    </div>
</div>

<!-- Modal de Nueva Venta -->
<div class="modal fade" id="ventaModal" tabindex="-1" aria-labelledby="ventaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ventaModalLabel">
                    Venta #<span id="numeroFacturaModal">---</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ventaId" value="">
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="fecha_modal" class="form-label">Fecha *</label>
                        <input type="date" class="form-control" 
                               id="fecha_modal" 
                               value="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div class="col-md-8">
                        <label for="cliente_buscar_modal" class="form-label">Buscar Cliente <small class="text-muted">(Opcional - puede dejar en blanco)</small></label>
                        <div class="d-flex gap-2">
                            <div class="position-relative flex-grow-1">
                                <input type="text" class="form-control" 
                                       id="cliente_buscar_modal" 
                                       placeholder="Buscar cliente por nombre, documento o teléfono... (opcional)">
                                <input type="hidden" id="cliente_id_modal" value="">
                                <ul class="list-group position-absolute w-100" id="clientes_lista" style="display:none; z-index:1000; max-height:200px; overflow-y:auto;"></ul>
                            </div>
                            <button type="button" class="btn btn-outline-primary" id="btnNuevoCliente" onclick="abrirModalNuevoCliente()" title="Crear nuevo cliente">
                                <i class="bi bi-person-plus"></i> Nuevo
                            </button>
                        </div>
                        <small class="text-muted">Deje en blanco para venta sin cliente</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="cliente_nombre_modal" class="form-label">Nombre del Cliente</label>
                        <input type="text" class="form-control" 
                               id="cliente_nombre_modal" 
                               placeholder="Nombre del cliente (se llena automáticamente si busca cliente)">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="cliente_documento_modal" class="form-label">RUT<small class="text-muted">(Opcional)</small></label>
                        <input type="text" class="form-control" 
                               id="cliente_documento_modal" 
                               placeholder="RUT, DNI, CI o documento de identidad">
                    </div>
                </div>
                
                <!-- Productos -->
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Productos</h6>
                        <button type="button" class="btn btn-sm btn-primary" id="btnAgregarProducto" onclick="toggleFilaAgregar()" style="display:none;">
                            <i class="bi bi-plus-circle"></i> Agregar Producto
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Fila para agregar producto (inicialmente oculta) -->
                        <div id="filaAgregarProducto" class="mb-3 p-3 bg-light rounded" style="display:none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="producto_buscar" class="form-label">Buscar Producto *</label>
                                    <div class="position-relative">
                                        <input type="text" 
                                               class="form-control" 
                                               id="producto_buscar" 
                                               placeholder="Escribe código o nombre del producto..."
                                               autocomplete="off">
                                        <div id="productos_lista" class="list-group position-absolute w-100" style="z-index: 1050; max-height: 300px; overflow-y: auto; display: none;">
                                            <!-- Los resultados se cargarán aquí dinámicamente -->
                                        </div>
                                    </div>
                                    <input type="hidden" id="producto_id_seleccionado" value="">
                                    <small class="text-muted" id="producto_info" style="display:none;"></small>
                                </div>
                                <div class="col-md-2">
                                    <label for="cantidad_producto" class="form-label">Cantidad *</label>
                                    <input type="number" class="form-control" id="cantidad_producto" min="1" value="1">
                                    <small class="text-muted" id="stock_disponible"></small>
                                </div>
                                <div class="col-md-2">
                                    <label for="descuento_porcentaje_producto" class="form-label">Descuento %</label>
                                    <input type="number" class="form-control" id="descuento_porcentaje_producto" min="0" max="100" value="0" step="0.01" placeholder="0">
                                </div>
                                <div class="col-md-2">
                                    <label for="descuento_monto_producto" class="form-label">Descuento $</label>
                                    <input type="number" class="form-control" id="descuento_monto_producto" min="0" value="0" step="0.01" placeholder="0.00">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary me-2" onclick="confirmarProducto()">
                                        <i class="bi bi-check-circle"></i> Agregar
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="cancelarAgregarProducto()">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div id="productosContainer">
                            <p class="text-muted mb-0">No hay productos agregados</p>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <button class="btn btn-link p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#descuentosCollapse" aria-expanded="false" aria-controls="descuentosCollapse" id="btnToggleDescuentos">
                                    <i class="bi bi-chevron-down" id="iconDescuentos"></i> <small class="text-muted">Descuento sobre el total (opcional)</small>
                                </button>
                                <div class="collapse mt-2" id="descuentosCollapse">
                                    <div class="mb-2">
                                        <label for="descuento_porcentaje_venta" class="form-label small">Descuento (%)</label>
                                        <input type="number" class="form-control form-control-sm" id="descuento_porcentaje_venta" min="0" max="100" value="0" step="0.01" placeholder="0" onchange="actualizarDescuentoVenta()" onblur="actualizarDescuentoVenta()">
                                    </div>
                                    <div class="mb-2">
                                        <label for="descuento_monto_venta" class="form-label small">Descuento ($)</label>
                                        <input type="number" class="form-control form-control-sm" id="descuento_monto_venta" min="0" value="0" step="0.01" placeholder="0.00" onchange="actualizarDescuentoVenta()" onblur="actualizarDescuentoVenta()">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end align-items-end">
                                <h5>Total: $<span id="totalVenta">0</span></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
                <button type="button" id="btnCompletarVenta" class="btn btn-success" style="display:none;" onclick="completarVenta()">
                    <i class="bi bi-check-circle"></i> Completar Venta
                </button>
                <button type="button" id="btnEliminarVenta" class="btn btn-danger" style="display:none;" onclick="eliminarVentaDesdeModal()">
                    <i class="bi bi-trash"></i> Eliminar Venta
                </button>
                <button type="button" id="btnCancelarVenta" class="btn btn-danger" style="display:none;" onclick="cancelarVenta()">
                    <i class="bi bi-x-circle"></i> Cancelar Venta
                </button>
                <button type="button" id="btnImprimirVenta" class="btn btn-primary" style="display:none;" onclick="imprimirVenta()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear cliente rápido -->
<div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-labelledby="nuevoClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevoClienteModalLabel">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoCliente">
                    <div class="mb-3">
                        <label for="nombre_nuevo_cliente" class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" id="nombre_nuevo_cliente" required>
                    </div>
                    <div class="mb-3">
                        <label for="documento_nuevo_cliente" class="form-label">Documento (RUT/DNI/CI) <small class="text-muted">(Opcional)</small></label>
                        <input type="text" class="form-control" id="documento_nuevo_cliente" placeholder="RUT, DNI, CI o documento de identidad">
                    </div>
                    <div class="mb-3">
                        <label for="telefono_nuevo_cliente" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono_nuevo_cliente">
                    </div>
                    <div class="mb-3">
                        <label for="email_nuevo_cliente" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email_nuevo_cliente">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarNuevoCliente()">
                    <i class="bi bi-check-circle"></i> Crear Cliente
                </button>
            </div>
        </div>
    </div>
</div>


@push('styles')
<style>
    #productos_lista {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        margin-top: 2px;
    }
    
    #productos_lista .list-group-item {
        border: none;
        border-bottom: 1px solid #dee2e6;
        cursor: pointer;
    }
    
    #productos_lista .list-group-item:last-child {
        border-bottom: none;
    }
    
    #productos_lista .list-group-item:hover {
        background-color: #f8f9fa;
    }
    
    #producto_info {
        margin-top: 0.5rem;
        padding: 0.5rem;
        background-color: #f8f9fa;
        border-radius: 0.375rem;
    }
    
    #clientes_lista {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        margin-top: 2px;
    }
    
    #clientes_lista .list-group-item {
        border: none;
        border-bottom: 1px solid #dee2e6;
        cursor: pointer;
    }
    
    #clientes_lista .list-group-item:last-child {
        border-bottom: none;
    }
    
    #clientes_lista .list-group-item:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script>
let ventaActual = null;
let ventaModal = null;

// Función helper para formatear precios en CLP
function formatearCLP(valor) {
    const num = typeof valor === 'string' ? parseFloat(valor.replace(/[^\d,.-]/g, '').replace(',', '.')) : parseFloat(valor);
    if (isNaN(num)) return '0';
    return num.toLocaleString('es-CL', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

document.addEventListener('DOMContentLoaded', function() {
    ventaModal = new bootstrap.Modal(document.getElementById('ventaModal'));
    
    // Si hay una venta activa en la sesión, abrir el modal
    @if(session('venta_activa'))
        abrirVenta({{ session('venta_activa') }});
    @endif
    
    // Guardar automáticamente al cambiar campos (blur = cuando sale del campo)
    ['fecha_modal', 'cliente_nombre_modal', 'cliente_documento_modal', 'cliente_buscar_modal'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            // Guardar cuando sale del campo (blur)
            element.addEventListener('blur', function() {
                if (ventaActual && !this.disabled) {
                    guardarDatosVenta();
                }
            });
            // También guardar con Enter
            element.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && ventaActual && !this.disabled) {
                    e.preventDefault();
                    this.blur(); // Esto disparará el evento blur que guardará
                }
            });
        }
    });
    
    // Datos de productos para autocompletado (inicial desde la página)
    window.productosData = @json($productos);
    
    // Datos de clientes para autocompletado
    window.clientesData = @json($clientes);
    
    // Autocompletado de clientes
    const clienteBuscar = document.getElementById('cliente_buscar_modal');
    const clientesLista = document.getElementById('clientes_lista');
    const clienteIdModal = document.getElementById('cliente_id_modal');
    
    if (clienteBuscar) {
        clienteBuscar.addEventListener('input', function() {
            const busqueda = this.value.toLowerCase().trim();
            
            if (busqueda.length < 2) {
                clientesLista.style.display = 'none';
                return;
            }
            
            const resultados = window.clientesData.filter(cliente => {
                const nombre = (cliente.nombre || '').toLowerCase();
                const documento = (cliente.documento || '').toLowerCase();
                const telefono = (cliente.telefono || '').toLowerCase();
                return nombre.includes(busqueda) || 
                       documento.includes(busqueda) || 
                       telefono.includes(busqueda);
            }).slice(0, 10); // Máximo 10 resultados
            
            if (resultados.length > 0) {
                clientesLista.innerHTML = resultados.map(cliente => `
                    <li class="list-group-item" onclick="seleccionarCliente(${cliente.id}, '${cliente.nombre.replace(/'/g, "\\'")}', '${(cliente.documento || '').replace(/'/g, "\\'")}')">
                        <strong>${cliente.nombre}</strong>
                        ${cliente.documento ? `<br><small class="text-muted">Doc: ${cliente.documento}</small>` : ''}
                        ${cliente.telefono ? `<br><small class="text-muted">Tel: ${cliente.telefono}</small>` : ''}
                    </li>
                `).join('');
                clientesLista.style.display = 'block';
            } else {
                clientesLista.style.display = 'none';
            }
        });
        
        // Ocultar lista al hacer click fuera
        document.addEventListener('click', function(e) {
            if (!clienteBuscar.contains(e.target) && !clientesLista.contains(e.target)) {
                clientesLista.style.display = 'none';
            }
        });
    }
    
    // Función para seleccionar cliente
    window.seleccionarCliente = function(id, nombre, documento) {
        clienteIdModal.value = id;
        clienteBuscar.value = nombre;
        clientesLista.style.display = 'none';
        document.getElementById('cliente_nombre_modal').value = nombre;
        document.getElementById('cliente_documento_modal').value = documento || '';
        
        // Guardar datos de la venta si hay una venta activa
        if (ventaActual) {
            guardarDatosVenta();
        }
        // Guardar automáticamente
        if (ventaActual) {
            guardarDatosVenta();
        }
    };
    
    // Permitir limpiar cliente (venta sin cliente)
    if (clienteBuscar) {
        clienteBuscar.addEventListener('focus', function() {
            if (this.value === '' && clienteIdModal.value) {
                clienteIdModal.value = '';
                document.getElementById('cliente_nombre_modal').value = '';
                document.getElementById('cliente_documento_modal').value = '';
            }
        });
    }
    
    // Autocompletado de productos
    const productoBuscar = document.getElementById('producto_buscar');
    const productosLista = document.getElementById('productos_lista');
    const productoIdSeleccionado = document.getElementById('producto_id_seleccionado');
    const productoInfo = document.getElementById('producto_info');
    
    if (productoBuscar) {
        productoBuscar.addEventListener('input', function() {
            const busqueda = this.value.toLowerCase().trim();
            
            if (busqueda.length < 2) {
                productosLista.style.display = 'none';
                productoIdSeleccionado.value = '';
                productoInfo.style.display = 'none';
                return;
            }
            
            // Filtrar productos
            const resultados = window.productosData.filter(p => 
                p.nombre.toLowerCase().includes(busqueda) ||
                p.codigo.toLowerCase().includes(busqueda)
            ).slice(0, 10); // Limitar a 10 resultados
            
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
                            onclick="seleccionarProducto(${producto.id}, '${nombreEscapado}', ${producto.precio_venta}, ${producto.stock_actual}, '${producto.codigo}')">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${producto.nombre}</strong>
                                <br>
                                <small class="text-muted">Código: ${producto.codigo} | Stock: ${producto.stock_actual} | Precio: $${parseFloat(producto.precio_venta).toLocaleString('es-CL')}</small>
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
        
        // Manejar teclas especiales
        productoBuscar.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                productosLista.style.display = 'none';
                this.blur();
            }
        });
    }
    
    // Función para seleccionar producto
    window.seleccionarProducto = function(id, nombre, precio, stock, codigo) {
        productoIdSeleccionado.value = id;
        productoBuscar.value = `${codigo} - ${nombre}`;
        productosLista.style.display = 'none';
        
        productoInfo.innerHTML = `
            <strong>${nombre}</strong> | 
            Stock disponible: <span class="badge bg-info">${stock}</span> | 
            Precio: <span class="badge bg-success">$${parseFloat(precio).toLocaleString('es-CL')}</span>
        `;
        productoInfo.style.display = 'block';
        
        document.getElementById('stock_disponible').textContent = `Stock disponible: ${stock}`;
        document.getElementById('cantidad_producto').max = stock;
        document.getElementById('cantidad_producto').value = 1;
    };
    
    // Cuando se cierra el modal, recargar la tabla
    document.getElementById('ventaModal').addEventListener('hidden.bs.modal', function () {
        ventaActual = null;
        aplicarFiltros(); // Recargar tabla de ventas
    });
    
    // Cambiar icono del botón de descuentos cuando se expande/contrae
    const descuentosCollapse = document.getElementById('descuentosCollapse');
    const iconDescuentos = document.getElementById('iconDescuentos');
    if (descuentosCollapse && iconDescuentos) {
        descuentosCollapse.addEventListener('show.bs.collapse', function () {
            iconDescuentos.classList.remove('bi-chevron-down');
            iconDescuentos.classList.add('bi-chevron-up');
        });
        descuentosCollapse.addEventListener('hide.bs.collapse', function () {
            iconDescuentos.classList.remove('bi-chevron-up');
            iconDescuentos.classList.add('bi-chevron-down');
        });
    }
    
    // Permitir crear cliente con Enter en el formulario de nuevo cliente
    const formNuevoCliente = document.getElementById('formNuevoCliente');
    if (formNuevoCliente) {
        formNuevoCliente.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarNuevoCliente();
        });
    }
});

// Abrir venta existente en el modal
function abrirVenta(ventaId) {
    fetch(`/ventas/${ventaId}/editar`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        ventaActual = data.venta;
        
        // Actualizar productos disponibles para el autocompletado
        if (data.productos) {
            window.productosData = data.productos;
        }
        
        document.getElementById('ventaId').value = ventaActual.id;
        document.getElementById('numeroFacturaModal').textContent = ventaActual.numero_factura;
        // Asegurar que la fecha siempre tenga un valor (si está vacía, usar hoy)
        const fechaVenta = ventaActual.fecha || new Date().toISOString().split('T')[0];
        document.getElementById('fecha_modal').value = fechaVenta;
        document.getElementById('cliente_nombre_modal').value = ventaActual.cliente_nombre || '';
        document.getElementById('cliente_documento_modal').value = ventaActual.cliente_documento || '';
        
        // Si hay cliente_id, establecerlo
        if (ventaActual.cliente_id) {
            document.getElementById('cliente_id_modal').value = ventaActual.cliente_id;
            document.getElementById('cliente_buscar_modal').value = ventaActual.cliente_nombre || '';
        } else {
            document.getElementById('cliente_id_modal').value = '';
            document.getElementById('cliente_buscar_modal').value = '';
        }
        
        // Habilitar/deshabilitar campos según el estado
        const esPendiente = ventaActual.estado === 'pendiente';
        document.getElementById('fecha_modal').disabled = !esPendiente;
        document.getElementById('cliente_buscar_modal').disabled = !esPendiente;
        document.getElementById('cliente_nombre_modal').disabled = !esPendiente;
        document.getElementById('cliente_documento_modal').disabled = !esPendiente;
        
        // Deshabilitar botón "Nuevo Cliente" si la venta no está pendiente
        const btnNuevoCliente = document.getElementById('btnNuevoCliente');
        if (btnNuevoCliente) {
            btnNuevoCliente.disabled = !esPendiente;
        }
        
        // Mostrar/ocultar botones
        const esCompletada = ventaActual.estado === 'completada';
        document.getElementById('btnAgregarProducto').style.display = esPendiente ? 'inline-block' : 'none';
        document.getElementById('btnCompletarVenta').style.display = esPendiente ? 'inline-block' : 'none';
        document.getElementById('btnEliminarVenta').style.display = esPendiente ? 'inline-block' : 'none';
        document.getElementById('btnCancelarVenta').style.display = esCompletada ? 'inline-block' : 'none';
        document.getElementById('btnImprimirVenta').style.display = esCompletada ? 'inline-block' : 'none';
        
        actualizarTablaProductosDesdeVenta(ventaActual);
        ventaModal.show();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar la venta');
    });
}

// Guardar datos de la venta (fecha, cliente)
function guardarDatosVenta() {
    if (!ventaActual) return;
    
    let fecha = document.getElementById('fecha_modal').value;
    // Si la fecha está vacía, usar la fecha de hoy
    if (!fecha) {
        const hoy = new Date();
        const año = hoy.getFullYear();
        const mes = String(hoy.getMonth() + 1).padStart(2, '0');
        const dia = String(hoy.getDate()).padStart(2, '0');
        fecha = `${año}-${mes}-${dia}`;
        document.getElementById('fecha_modal').value = fecha;
    }
    
    const clienteId = document.getElementById('cliente_id_modal').value;
    const clienteNombre = document.getElementById('cliente_nombre_modal').value.trim();
    const clienteDocumento = document.getElementById('cliente_documento_modal').value.trim();
    
    // Si no hay cambios, no hacer nada
    const clienteIdActual = ventaActual.cliente_id ? ventaActual.cliente_id.toString() : '';
    if (fecha === ventaActual.fecha && 
        clienteId === clienteIdActual &&
        clienteNombre === (ventaActual.cliente_nombre || '') && 
        clienteDocumento === (ventaActual.cliente_documento || '')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('fecha', fecha);
    if (clienteId) {
        formData.append('cliente_id', clienteId);
    } else {
        formData.append('cliente_id', ''); // Enviar vacío para limpiar la relación
    }
    formData.append('cliente_nombre', clienteNombre);
    formData.append('cliente_documento', clienteDocumento);
    formData.append('_method', 'PUT');
    
    fetch(`/ventas/${ventaActual.id}/actualizar`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            ventaActual = data.venta;
            // Actualizar los campos en el modal con los nuevos valores (por si acaso)
            document.getElementById('cliente_nombre_modal').value = ventaActual.cliente_nombre || '';
            document.getElementById('cliente_documento_modal').value = ventaActual.cliente_documento || '';
        } else {
            console.error('Error al guardar:', data);
            alert('Error al guardar: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar los datos de la venta. Por favor, intenta nuevamente.');
    });
}

// Mostrar/ocultar fila para agregar producto
function toggleFilaAgregar() {
    const fila = document.getElementById('filaAgregarProducto');
    if (fila.style.display === 'none') {
        fila.style.display = 'block';
        document.getElementById('producto_buscar').focus();
    } else {
        fila.style.display = 'none';
        cancelarAgregarProducto();
    }
}

// Cancelar agregar producto
function cancelarAgregarProducto() {
    document.getElementById('producto_buscar').value = '';
    document.getElementById('descuento_porcentaje_producto').value = '0';
    document.getElementById('descuento_monto_producto').value = '0';
    document.getElementById('producto_id_seleccionado').value = '';
    document.getElementById('producto_info').style.display = 'none';
    document.getElementById('productos_lista').style.display = 'none';
    document.getElementById('cantidad_producto').value = 1;
    document.getElementById('stock_disponible').textContent = '';
}

// Agregar producto a la venta existente (AJAX)
function confirmarProducto() {
    if (!ventaActual) {
        alert('No hay una venta activa');
        return;
    }
    
    const productoId = document.getElementById('producto_id_seleccionado').value;
    
    if (!productoId) {
        alert('Selecciona un producto de la lista');
        return;
    }
    
    const cantidad = parseInt(document.getElementById('cantidad_producto').value);
    
    if (!cantidad || cantidad < 1) {
        alert('La cantidad debe ser mayor a 0');
        return;
    }
    
    const descuentoPorcentaje = parseFloat(document.getElementById('descuento_porcentaje_producto').value) || 0;
    const descuentoMonto = parseFloat(document.getElementById('descuento_monto_producto').value) || 0;
    
    const formData = new FormData();
    formData.append('producto_id', productoId);
    formData.append('cantidad', cantidad);
    if (descuentoPorcentaje > 0) {
        formData.append('descuento_porcentaje', descuentoPorcentaje);
    }
    if (descuentoMonto > 0) {
        formData.append('descuento_monto', descuentoMonto);
    }
    
    fetch(`/ventas/${ventaActual.id}/agregar-producto`, {
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
            ventaActual = data.venta;
            actualizarTablaProductosDesdeVenta(ventaActual);
            
            // Mostrar advertencia si queda poco stock
            if (data.warning) {
                alert(`⚠️ ${data.warning}\n\nStock disponible restante: ${data.stock_disponible}`);
            }
            
            // Limpiar formulario
            cancelarAgregarProducto();
            
            // Recargar tabla de ventas si está visible
            aplicarFiltros();
        } else {
            // Mostrar error de validación (stock insuficiente, etc.)
            alert('❌ Error: ' + (data.message || 'No se pudo agregar el producto'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al agregar el producto. Por favor, intenta nuevamente.');
    });
}

// Eliminar producto de la venta (AJAX)
function eliminarProducto(detalleId) {
    if (!ventaActual) return;
    
    if (!confirm('¿Eliminar este producto de la venta?')) return;
    
    fetch(`/ventas/${ventaActual.id}/eliminar-producto`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ detalle_id: detalleId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ventaActual = data.venta;
            actualizarTablaProductosDesdeVenta(ventaActual);
            aplicarFiltros(); // Actualizar tabla principal
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el producto');
    });
}

// Actualizar tabla de productos desde la venta
function actualizarTablaProductosDesdeVenta(venta) {
    const container = document.getElementById('productosContainer');
    const esPendiente = venta.estado === 'pendiente';
    
    if (!venta.detalles || venta.detalles.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0">No hay productos agregados</p>';
        document.getElementById('totalVenta').textContent = '0';
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
                        <th>Descuento</th>
                        <th>Subtotal</th>
                        ${esPendiente ? '<th></th>' : ''}
                    </tr>
                </thead>
                <tbody>
    `;
    
    venta.detalles.forEach((detalle) => {
        const descuentoPorcentaje = parseFloat(detalle.descuento_porcentaje) || 0;
        const descuentoMonto = parseFloat(detalle.descuento_monto) || 0;
        let descuentoTexto = '';
        if (descuentoPorcentaje > 0) {
            descuentoTexto = `${descuentoPorcentaje}%`;
        }
        if (descuentoMonto > 0) {
            if (descuentoTexto) descuentoTexto += ' + ';
            descuentoTexto += `$${descuentoMonto.toLocaleString('es-CL')}`;
        }
        if (!descuentoTexto) descuentoTexto = '-';
        
        html += `
            <tr>
                <td>${detalle.producto.nombre}</td>
                <td>${detalle.cantidad}</td>
                <td>$${parseFloat(detalle.precio_unitario).toLocaleString('es-CL')}</td>
                <td><small class="text-muted">${descuentoTexto}</small></td>
                <td>$${parseFloat(detalle.subtotal).toLocaleString('es-CL')}</td>
                ${esPendiente ? `
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto(${detalle.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
                ` : ''}
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
    
    // Actualizar descuentos sobre el total si existen
    if (venta.descuento_porcentaje) {
        document.getElementById('descuento_porcentaje_venta').value = parseFloat(venta.descuento_porcentaje).toFixed(2);
    }
    if (venta.descuento_monto) {
        document.getElementById('descuento_monto_venta').value = parseFloat(venta.descuento_monto).toFixed(2);
    }
    
    // Expandir collapsible de descuentos si hay descuentos aplicados
    const tieneDescuentos = (venta.descuento_porcentaje && parseFloat(venta.descuento_porcentaje) > 0) || 
                            (venta.descuento_monto && parseFloat(venta.descuento_monto) > 0);
    if (tieneDescuentos) {
        const descuentosCollapse = document.getElementById('descuentosCollapse');
        const bsCollapse = new bootstrap.Collapse(descuentosCollapse, { show: true });
    }
    
    document.getElementById('totalVenta').textContent = parseFloat(venta.total).toLocaleString('es-CL');
}

// Actualizar descuento sobre el total de la venta
function actualizarDescuentoVenta() {
    if (!ventaActual) return;
    
    const descuentoPorcentaje = parseFloat(document.getElementById('descuento_porcentaje_venta').value) || 0;
    const descuentoMonto = parseFloat(document.getElementById('descuento_monto_venta').value) || 0;
    
    const updateData = {
        fecha: document.getElementById('fecha_modal').value,
        cliente_id: document.getElementById('cliente_id_modal').value || null,
        cliente_nombre: document.getElementById('cliente_nombre_modal').value || null,
        cliente_documento: document.getElementById('cliente_documento_modal').value || null,
        descuento_porcentaje: descuentoPorcentaje,
        descuento_monto: descuentoMonto
    };
    
    fetch(`/ventas/${ventaActual.id}/actualizar`, {
        method: 'PUT',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(updateData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ventaActual = data.venta;
            // Formatear total con formato CLP chileno
            const totalNum = typeof data.total === 'string' ? parseFloat(data.total.replace(/[^\d,.-]/g, '').replace(',', '.')) : parseFloat(data.total);
            document.getElementById('totalVenta').textContent = totalNum.toLocaleString('es-CL');
        } else {
            alert('Error al actualizar el descuento: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el descuento');
    });
}

// Completar venta desde el modal
function completarVenta() {
    if (!ventaActual) return;
    
    if (!confirm('¿Completar esta venta? Se descontará el stock de los productos.')) return;
    
    fetch(`/ventas/${ventaActual.id}/completar`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            ventaActual = data.venta;
            actualizarTablaProductosDesdeVenta(ventaActual);
            
            // Deshabilitar campos y botones
            document.getElementById('fecha_modal').disabled = true;
            document.getElementById('cliente_nombre_modal').disabled = true;
            document.getElementById('cliente_documento_modal').disabled = true;
            document.getElementById('btnAgregarProducto').style.display = 'none';
            document.getElementById('btnCompletarVenta').style.display = 'none';
            document.getElementById('btnCancelarVenta').style.display = 'inline-block';
            document.getElementById('btnImprimirVenta').style.display = 'inline-block';
            
            // Actualizar badge de pendientes (decrementar contador)
            const badgePendientes = document.getElementById('badgePendientes');
            if (badgePendientes) {
                const contadorActual = parseInt(badgePendientes.textContent) || 0;
                const nuevoContador = Math.max(0, contadorActual - 1);
                if (nuevoContador > 0) {
                    badgePendientes.textContent = nuevoContador;
                } else {
                    // Si no quedan pendientes, ocultar el badge
                    badgePendientes.remove();
                }
            }
            
            // Cambiar filtro a "completada" para ver la venta que acabamos de completar
            const estadoSelect = document.getElementById('estado_filtro');
            if (estadoSelect && estadoSelect.value === 'pendiente') {
                estadoSelect.value = 'completada';
                // Actualizar tabs activos
                document.querySelectorAll('#ventasTabs .nav-link').forEach(btn => {
                    btn.classList.remove('active');
                });
                const completadasTab = document.getElementById('completadas-tab');
                if (completadasTab) {
                    completadasTab.classList.add('active');
                }
            }
            
            // Recargar tabla de ventas
            aplicarFiltros();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al completar la venta');
    });
}

// Cancelar venta (devolver stock)
function cancelarVenta() {
    if (!ventaActual) return;
    
    if (!confirm('¿Cancelar esta venta? Se devolverá el stock de todos los productos a inventario. Esta acción no se puede deshacer.')) return;
    
    fetch(`/ventas/${ventaActual.id}/cancelar`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            ventaActual = data.venta;
            actualizarTablaProductosDesdeVenta(ventaActual);
            
            // Deshabilitar campos y actualizar botones
            document.getElementById('fecha_modal').disabled = true;
            document.getElementById('cliente_nombre_modal').disabled = true;
            document.getElementById('cliente_documento_modal').disabled = true;
            document.getElementById('btnAgregarProducto').style.display = 'none';
            document.getElementById('btnCompletarVenta').style.display = 'none';
            document.getElementById('btnCancelarVenta').style.display = 'none';
            document.getElementById('btnImprimirVenta').style.display = 'none';
            
            // Recargar tabla de ventas
            aplicarFiltros();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cancelar la venta');
    });
}

// Eliminar venta desde la tabla
function eliminarVenta(ventaId, numeroFactura) {
    if (!confirm(`¿Eliminar la venta #${numeroFactura}? Esta acción no se puede deshacer.`)) {
        return;
    }
    
    fetch(`/ventas/${ventaId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            aplicarFiltros(); // Recargar tabla
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar la venta');
    });
}

// Eliminar venta desde el modal
function eliminarVentaDesdeModal() {
    if (!ventaActual) return;
    
    if (!confirm(`¿Eliminar la venta #${ventaActual.numero_factura}? Esta acción no se puede deshacer.`)) {
        return;
    }
    
    fetch(`/ventas/${ventaActual.id}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar badge de pendientes si se eliminó una venta pendiente
            if (ventaActual && ventaActual.estado === 'pendiente') {
                const badgePendientes = document.getElementById('badgePendientes');
                if (badgePendientes) {
                    const contadorActual = parseInt(badgePendientes.textContent) || 0;
                    const nuevoContador = Math.max(0, contadorActual - 1);
                    if (nuevoContador > 0) {
                        badgePendientes.textContent = nuevoContador;
                    } else {
                        badgePendientes.remove();
                    }
                }
            }
            alert(data.message);
            ventaModal.hide();
            aplicarFiltros(); // Recargar tabla
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar la venta');
    });
}

// Eliminar venta desde la tabla
function eliminarVenta(ventaId, numeroFactura) {
    if (!confirm(`¿Eliminar la venta #${numeroFactura}? Esta acción no se puede deshacer.`)) {
        return;
    }
    
    fetch(`/ventas/${ventaId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            aplicarFiltros(); // Recargar tabla
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar la venta');
    });
}

// Eliminar venta desde el modal
function eliminarVentaDesdeModal() {
    if (!ventaActual) return;
    
    if (!confirm(`¿Eliminar la venta #${ventaActual.numero_factura}? Esta acción no se puede deshacer.`)) {
        return;
    }
    
    fetch(`/ventas/${ventaActual.id}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            ventaModal.hide();
            aplicarFiltros(); // Recargar tabla
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar la venta');
    });
}

// Imprimir venta
function imprimirVenta() {
    if (!ventaActual) return;
    window.open(`/ventas/${ventaActual.id}`, '_blank');
}

// Abrir modal para crear nuevo cliente
function abrirModalNuevoCliente() {
    // Limpiar formulario
    document.getElementById('formNuevoCliente').reset();
    // Abrir modal
    const nuevoClienteModal = new bootstrap.Modal(document.getElementById('nuevoClienteModal'));
    nuevoClienteModal.show();
    // Enfocar en el campo nombre
    setTimeout(() => {
        document.getElementById('nombre_nuevo_cliente').focus();
    }, 300);
}

// Guardar nuevo cliente desde el modal de venta
function guardarNuevoCliente() {
    const nombre = document.getElementById('nombre_nuevo_cliente').value.trim();
    
    if (!nombre) {
        alert('El nombre del cliente es requerido');
        document.getElementById('nombre_nuevo_cliente').focus();
        return;
    }
    
    const data = {
        nombre: nombre,
        documento: document.getElementById('documento_nuevo_cliente').value.trim() || null,
        telefono: document.getElementById('telefono_nuevo_cliente').value.trim() || null,
        email: document.getElementById('email_nuevo_cliente').value.trim() || null,
        activo: true
    };
    
    fetch('/clientes', {
        method: 'POST',
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
        if (status === 200 && data.success) {
            // Cerrar modal de nuevo cliente
            const nuevoClienteModal = bootstrap.Modal.getInstance(document.getElementById('nuevoClienteModal'));
            nuevoClienteModal.hide();
            
            // Agregar cliente a la lista de clientes disponibles
            if (window.clientesData && data.cliente) {
                window.clientesData.push(data.cliente);
            }
            
            // Seleccionar automáticamente el cliente recién creado en la venta
            seleccionarCliente(
                data.cliente.id, 
                data.cliente.nombre, 
                data.cliente.documento || ''
            );
            
            // Limpiar formulario
            document.getElementById('formNuevoCliente').reset();
        } else {
            // Mostrar errores de validación
            let mensajeError = 'Error al crear el cliente';
            if (data.errors) {
                const errores = Object.values(data.errors).flat().join(', ');
                mensajeError += ': ' + errores;
            } else if (data.message) {
                mensajeError = data.message;
            }
            alert(mensajeError);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al crear el cliente. Por favor, intenta nuevamente.');
    });
}

// ========== FILTROS AJAX (Sin recargar página) ==========
let filtroTimeout = null;

function aplicarFiltros() {
    const formData = new FormData(document.getElementById('filtrosForm'));
    const params = new URLSearchParams();
    
    for (const [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    // Mostrar loading
    const container = document.getElementById('tablaVentasContainer');
    container.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div><p class="mt-2 text-muted">Cargando ventas...</p></div>';
    
    // Hacer petición AJAX
    fetch(`{{ route('ventas.filtrar') }}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Error en la respuesta');
        return response.text();
    })
    .then(html => {
        container.innerHTML = html;
        // Actualizar URL sin recargar
        const newUrl = `{{ route('ventas.index') }}?${params.toString()}`;
        window.history.pushState({}, '', newUrl);
    })
    .catch(error => {
        console.error('Error:', error);
        container.innerHTML = '<div class="alert alert-danger">Error al cargar las ventas. Por favor, recarga la página.</div>';
    });
}

function filtrarPorEstado(estado) {
    const estadoSelect = document.getElementById('estado_filtro');
    estadoSelect.value = estado === 'todas' ? '' : estado;
    aplicarFiltros();
    
    // Actualizar tabs activos
    document.querySelectorAll('#ventasTabs .nav-link').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

// Event listeners para filtros
document.getElementById('btnBuscar')?.addEventListener('click', aplicarFiltros);
document.getElementById('btnLimpiar')?.addEventListener('click', function() {
    document.getElementById('filtrosForm').reset();
    document.getElementById('estado_filtro').value = '';
    aplicarFiltros();
});

// Filtro automático al cambiar campos (con debounce de 500ms)
['numero_factura', 'fecha_desde', 'fecha_hasta'].forEach(id => {
    const element = document.getElementById(id);
    if (element) {
        element.addEventListener('input', function() {
            clearTimeout(filtroTimeout);
            filtroTimeout = setTimeout(aplicarFiltros, 500);
        });
    }
});

// Filtro inmediato al cambiar el select de estado
const estadoSelect = document.getElementById('estado_filtro');
if (estadoSelect) {
    estadoSelect.addEventListener('change', aplicarFiltros);
}

// Manejar paginación con AJAX
document.addEventListener('click', function(e) {
    const paginationLink = e.target.closest('.pagination a');
    if (paginationLink && paginationLink.href) {
        e.preventDefault();
        const url = paginationLink.href;
        
        const container = document.getElementById('tablaVentasContainer');
        container.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></div>';
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<div class="alert alert-danger">Error al cargar la página</div>';
        });
    }
});
</script>
@endpush
@endsection
