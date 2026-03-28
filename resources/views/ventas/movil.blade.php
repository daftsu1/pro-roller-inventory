@extends('layouts.app')

@section('title', 'Venta Móvil')

@section('content')
<div class="mobile-sale-page">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">Venta Móvil</h2>
            <small class="text-muted">
                Venta activa: {{ $venta->numero_factura }}
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ventas.movil', ['nueva' => 1]) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Nueva
            </a>
            <a href="{{ route('ventas.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Ventas
            </a>
        </div>
    </div>

    <div id="flash_mobile" class="alert d-none py-2 mb-3" role="alert"></div>

    @if($mostrarAvisoPendiente)
        <div class="alert alert-warning py-2">
            Se cargó automáticamente una venta pendiente con productos.
            Puedes continuarla o crear una nueva.
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header py-2 px-2">
            <button
                class="btn btn-link text-decoration-none p-0 w-100 d-flex justify-content-between align-items-center"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#pendientesRapidasCollapse"
                aria-expanded="false"
                aria-controls="pendientesRapidasCollapse"
            >
                <span class="fw-semibold text-dark">Pendientes rápidas</span>
                <span class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary">{{ $ventasPendientes->count() }}</span>
                    <i class="bi bi-chevron-down text-muted"></i>
                </span>
            </button>
        </div>
        <div id="pendientesRapidasCollapse" class="collapse">
        <div class="card-body p-2">
            @if($ventasPendientes->isEmpty())
                <small class="text-muted">No tienes ventas pendientes.</small>
            @else
                <div class="d-flex flex-column gap-2">
                    @foreach($ventasPendientes as $pendiente)
                        @php
                            $cantidadItems = $pendiente->detalles->sum('cantidad');
                            $esActiva = (int) $pendiente->id === (int) $venta->id;
                        @endphp
                        <a href="{{ route('ventas.movil', ['venta_id' => $pendiente->id]) }}"
                           class="btn btn-sm text-start {{ $esActiva ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $pendiente->numero_factura }}
                            <span class="d-block small">
                                {{ $cantidadItems }} ítems | ${{ number_format((float) $pendiente->total, 0, ',', '.') }}
                                @if($esActiva) | activa @endif
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <label for="buscar_producto" class="form-label">Buscar producto</label>
            <input
                type="text"
                id="buscar_producto"
                class="form-control mb-2"
                placeholder="Código o nombre"
                autocomplete="off"
            >
            <div class="row g-2">
                <div class="col-6">
                    <label for="cantidad_producto" class="form-label">Cantidad</label>
                    <div class="input-group">
                        <button type="button" class="btn btn-outline-secondary" id="btnCantidadMenos">-</button>
                        <input type="number" id="cantidad_producto" class="form-control text-center" min="1" value="1">
                        <button type="button" class="btn btn-outline-secondary" id="btnCantidadMas">+</button>
                    </div>
                </div>
                <div class="col-6 d-grid align-self-end">
                    <button type="button" id="btnAgregarProducto" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Agregar
                    </button>
                </div>
            </div>
            <div id="productos_lista" class="list-group mt-2 d-none"></div>
            <small id="producto_seleccionado_info" class="text-muted d-none mt-2"></small>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Carrito</span>
            <span class="badge bg-secondary" id="badge_items">0 productos</span>
        </div>
        <div class="card-body p-2" id="carrito_container">
            <p class="text-muted mb-0 p-2">Aun no agregas productos.</p>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header py-2">Instalación</div>
        <div class="card-body p-2">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" role="switch" id="tiene_instalacion_mobile">
                <label class="form-check-label" for="tiene_instalacion_mobile">
                    Agregar instalación
                </label>
            </div>
            <div id="instalacion_monto_wrap">
                <label for="monto_instalacion_mobile" class="form-label mb-1">Monto instalación</label>
                <input type="number" id="monto_instalacion_mobile" class="form-control" min="0" step="0.01" value="0">
            </div>
        </div>
    </div>
</div>

<div class="mobile-sale-footer border-top bg-white p-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Total</strong>
        <strong>$<span id="totalVenta">0</span></strong>
    </div>
    <div class="d-grid gap-2">
        <button type="button" id="btnCompletarVenta" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Completar venta
        </button>
        <a href="{{ route('ventas.movil', ['nueva' => 1]) }}" class="btn btn-outline-primary btn-sm">
            Iniciar otra venta
        </a>
    </div>
</div>
@endsection

@push('styles')
<style>
    .mobile-sale-page {
        padding-bottom: 150px;
    }

    .mobile-sale-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1020;
    }

    #productos_lista .list-group-item {
        cursor: pointer;
    }

    #tiene_instalacion_mobile {
        width: 2.6rem;
        height: 1.4rem;
    }

    #flash_mobile {
        position: sticky;
        top: 0.5rem;
        z-index: 1030;
    }
</style>
@endpush

@push('scripts')
<script>
let ventaActual = @json($venta);
let productosBusqueda = [];
let productoSeleccionado = null;
const montoInstalacionDefault = {{ json_encode(config('ventas.instalacion_monto_default', 12500)) }};
let searchTimeout = null;

function mostrarFlash(tipo, mensaje, autocerrar = true) {
    const flash = document.getElementById('flash_mobile');
    flash.className = `alert alert-${tipo} py-2 mb-3`;
    flash.textContent = mensaje;
    flash.classList.remove('d-none');
    if (autocerrar) {
        setTimeout(() => {
            flash.classList.add('d-none');
        }, 2600);
    }
}

function formatearCLP(valor) {
    const numero = parseFloat(valor);
    return (isNaN(numero) ? 0 : numero).toLocaleString('es-CL');
}

function actualizarBadgeItems(venta) {
    const totalItems = (venta.detalles || []).reduce((acc, detalle) => acc + parseInt(detalle.cantidad, 10), 0);
    document.getElementById('badge_items').textContent = `${totalItems} productos`;
}

function renderCarrito(venta) {
    const container = document.getElementById('carrito_container');
    const detalles = venta.detalles || [];

    if (detalles.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0 p-2">Aún no agregas productos.</p>';
        document.getElementById('totalVenta').textContent = '0';
        actualizarBadgeItems(venta);
        return;
    }

    let html = '';
    detalles.forEach((detalle) => {
        html += `
            <div class="border rounded p-2 mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold">${detalle.producto.nombre}</div>
                        <small class="text-muted">
                            Cantidad: ${detalle.cantidad} | Unit: $${formatearCLP(detalle.precio_unitario)}
                        </small>
                        <div class="small mt-1">Subtotal: $${formatearCLP(detalle.subtotal)}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarProducto(${detalle.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    const totalValor = typeof venta.total === 'string' ? parseFloat(venta.total.replace(/,/g, '')) : parseFloat(venta.total);
    document.getElementById('totalVenta').textContent = formatearCLP(totalValor);
    actualizarBadgeItems(venta);
}

function sincronizarUIInstalacion() {
    const check = document.getElementById('tiene_instalacion_mobile');
    const inputMonto = document.getElementById('monto_instalacion_mobile');
    const wrapMonto = document.getElementById('instalacion_monto_wrap');
    const tieneInstalacion = !!ventaActual.tiene_instalacion;
    const montoActual = parseFloat(ventaActual.monto_instalacion ?? 0);
    const montoFinal = (tieneInstalacion && montoActual > 0) ? montoActual : parseFloat(montoInstalacionDefault || 0);

    check.checked = tieneInstalacion;
    inputMonto.value = isNaN(montoFinal) ? '0' : montoFinal.toFixed(2);
    wrapMonto.style.display = tieneInstalacion ? 'block' : 'none';
}

function actualizarInstalacionVenta() {
    const check = document.getElementById('tiene_instalacion_mobile');
    const inputMonto = document.getElementById('monto_instalacion_mobile');
    const monto = parseFloat(inputMonto.value || '0');

    const payload = {
        fecha: ventaActual.fecha,
        cliente_id: ventaActual.cliente_id,
        cliente_nombre: ventaActual.cliente_nombre,
        cliente_documento: ventaActual.cliente_documento,
        descuento_porcentaje: parseFloat(ventaActual.descuento_porcentaje || 0),
        descuento_monto: parseFloat(ventaActual.descuento_monto || 0),
        tiene_instalacion: check.checked,
        monto_instalacion: check.checked ? (isNaN(monto) ? 0 : monto) : 0
    };

    fetch(`/ventas/${ventaActual.id}/actualizar`, {
        method: 'PUT',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
        .then((response) => response.json())
        .then((data) => {
            if (!data.success) {
                mostrarFlash('danger', data.message || 'No se pudo actualizar instalación.');
                sincronizarUIInstalacion();
                return;
            }
            ventaActual = { ...ventaActual, ...data.venta };
            renderCarrito(ventaActual);
            sincronizarUIInstalacion();
            mostrarFlash('success', 'Instalación actualizada.');
        })
        .catch(() => {
            mostrarFlash('danger', 'Error al actualizar instalación.');
            sincronizarUIInstalacion();
        });
}

function limpiarSeleccionProducto() {
    productoSeleccionado = null;
    document.getElementById('buscar_producto').value = '';
    document.getElementById('cantidad_producto').value = 1;
    document.getElementById('productos_lista').classList.add('d-none');
    document.getElementById('productos_lista').innerHTML = '';
    document.getElementById('producto_seleccionado_info').classList.add('d-none');
}

function seleccionarProducto(id) {
    const producto = productosBusqueda.find((p) => p.id === id);
    if (!producto) {
        return;
    }

    productoSeleccionado = producto;
    document.getElementById('buscar_producto').value = `${producto.codigo} - ${producto.nombre}`;
    document.getElementById('productos_lista').classList.add('d-none');
    document.getElementById('cantidad_producto').value = 1;
    document.getElementById('cantidad_producto').max = producto.stock_para_agregar ?? 0;

    const info = document.getElementById('producto_seleccionado_info');
    const stock = producto.stock_para_agregar ?? 0;
    info.textContent = `Seleccionado: ${producto.nombre} | Stock disponible: ${stock} | Precio: $${formatearCLP(producto.precio_venta)}`;
    info.classList.remove('d-none');
}

function buscarProductos(termino) {
    const lista = document.getElementById('productos_lista');

    if (!termino || termino.trim().length < 1) {
        lista.classList.add('d-none');
        lista.innerHTML = '';
        return;
    }

    fetch(`{{ route('ventas.buscar-productos') }}?q=${encodeURIComponent(termino)}&venta_id=${ventaActual.id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then((response) => response.json())
        .then((data) => {
            if (!data.success) {
                lista.innerHTML = '<button class="list-group-item list-group-item-action text-muted" type="button">No se pudo buscar productos</button>';
                lista.classList.remove('d-none');
                return;
            }

            productosBusqueda = (data.productos || []).map((producto) => {
                const stockBase = parseInt(producto.stock_disponible ?? producto.stock_actual, 10) || 0;
                const cantidadYaEnVenta = (ventaActual.detalles || [])
                    .filter((detalle) => detalle.producto_id === producto.id)
                    .reduce((acc, detalle) => acc + (parseInt(detalle.cantidad, 10) || 0), 0);

                return {
                    ...producto,
                    stock_para_agregar: Math.max(0, stockBase - cantidadYaEnVenta)
                };
            });
            if (productosBusqueda.length === 0) {
                lista.innerHTML = '<button class="list-group-item list-group-item-action text-muted" type="button">Sin resultados</button>';
                lista.classList.remove('d-none');
                return;
            }

            lista.innerHTML = productosBusqueda.map((producto) => {
                const stock = producto.stock_para_agregar ?? 0;
                return `
                    <button type="button" class="list-group-item list-group-item-action" onclick="seleccionarProducto(${producto.id})">
                        <div class="fw-semibold">${producto.nombre}</div>
                        <small class="text-muted">
                            ${producto.codigo} | Stock: ${stock} | $${formatearCLP(producto.precio_venta)}
                        </small>
                    </button>
                `;
            }).join('');
            lista.classList.remove('d-none');
        })
        .catch(() => {
            lista.innerHTML = '<button class="list-group-item list-group-item-action text-muted" type="button">Error de red al buscar</button>';
            lista.classList.remove('d-none');
        });
}

function agregarProducto() {
    if (!productoSeleccionado) {
        mostrarFlash('warning', 'Selecciona un producto de la lista.');
        return;
    }

    const cantidad = parseInt(document.getElementById('cantidad_producto').value, 10);
    const stock = parseInt(productoSeleccionado.stock_para_agregar ?? 0, 10);

    if (!cantidad || cantidad < 1) {
        mostrarFlash('warning', 'La cantidad debe ser mayor a 0.');
        return;
    }

    if (cantidad > stock) {
        mostrarFlash('warning', `Stock insuficiente. Disponible: ${stock}.`);
        return;
    }

    const btnAgregar = document.getElementById('btnAgregarProducto');
    btnAgregar.disabled = true;
    btnAgregar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Agregando...';

    const formData = new FormData();
    formData.append('producto_id', productoSeleccionado.id);
    formData.append('cantidad', cantidad);

    fetch(`/ventas/${ventaActual.id}/agregar-producto`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
        .then((response) => response.json().then((data) => ({ status: response.status, data })))
        .then(({ status, data }) => {
            if (status !== 200 || !data.success) {
                mostrarFlash('danger', data.message || 'No se pudo agregar el producto.');
                return;
            }

            ventaActual = data.venta;
            renderCarrito(ventaActual);
            limpiarSeleccionProducto();
            mostrarFlash('success', 'Producto agregado.');
            document.getElementById('buscar_producto').focus();
        })
        .catch(() => {
            mostrarFlash('danger', 'Error al agregar producto.');
        })
        .finally(() => {
            btnAgregar.disabled = false;
            btnAgregar.innerHTML = '<i class="bi bi-plus-circle"></i> Agregar';
        });
}

function eliminarProducto(detalleId) {
    if (!confirm('¿Quitar este producto de la venta?')) {
        return;
    }

    fetch(`/ventas/${ventaActual.id}/eliminar-producto`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ detalle_id: detalleId })
    })
        .then((response) => response.json())
        .then((data) => {
            if (!data.success) {
                mostrarFlash('danger', data.message || 'No se pudo eliminar el producto.');
                return;
            }

            ventaActual = data.venta;
            renderCarrito(ventaActual);
            mostrarFlash('success', 'Producto eliminado.');
        })
        .catch(() => {
            mostrarFlash('danger', 'Error al eliminar producto.');
        });
}

function completarVenta() {
    const detalles = ventaActual.detalles || [];
    if (detalles.length === 0) {
        mostrarFlash('warning', 'Agrega al menos un producto antes de completar.');
        return;
    }

    if (!confirm('¿Completar esta venta? Se descontara stock.')) {
        return;
    }

    fetch(`/ventas/${ventaActual.id}/completar`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
        .then((response) => response.json())
        .then((data) => {
            if (!data.success) {
                mostrarFlash('danger', data.message || 'No se pudo completar la venta.');
                return;
            }

            mostrarFlash('success', 'Venta completada correctamente.');
            window.location.href = '{{ route('ventas.movil', ['nueva' => 1]) }}';
        })
        .catch(() => {
            mostrarFlash('danger', 'Error al completar la venta.');
        });
}

document.addEventListener('DOMContentLoaded', function () {
    renderCarrito(ventaActual);
    sincronizarUIInstalacion();

    const inputBuscar = document.getElementById('buscar_producto');
    inputBuscar.focus();

    inputBuscar.addEventListener('input', function () {
        productoSeleccionado = null;
        document.getElementById('producto_seleccionado_info').classList.add('d-none');
        clearTimeout(searchTimeout);
        const termino = this.value;
        searchTimeout = setTimeout(() => buscarProductos(termino), 180);
    });

    inputBuscar.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            if (productoSeleccionado) {
                agregarProducto();
            } else if (productosBusqueda.length === 1) {
                seleccionarProducto(productosBusqueda[0].id);
                agregarProducto();
            }
        }
    });

    document.getElementById('btnAgregarProducto').addEventListener('click', agregarProducto);
    document.getElementById('btnCompletarVenta').addEventListener('click', completarVenta);
    document.getElementById('btnCantidadMenos').addEventListener('click', function () {
        const inputCantidad = document.getElementById('cantidad_producto');
        const actual = parseInt(inputCantidad.value || '1', 10);
        inputCantidad.value = Math.max(1, actual - 1);
    });
    document.getElementById('btnCantidadMas').addEventListener('click', function () {
        const inputCantidad = document.getElementById('cantidad_producto');
        const max = parseInt(inputCantidad.max || '9999', 10);
        const actual = parseInt(inputCantidad.value || '1', 10);
        inputCantidad.value = Math.min(max, actual + 1);
    });

    document.getElementById('tiene_instalacion_mobile').addEventListener('change', function () {
        const wrapMonto = document.getElementById('instalacion_monto_wrap');
        if (this.checked) {
            wrapMonto.style.display = 'block';
            const inputMonto = document.getElementById('monto_instalacion_mobile');
            if (!inputMonto.value || parseFloat(inputMonto.value) <= 0) {
                inputMonto.value = parseFloat(montoInstalacionDefault || 0).toFixed(2);
            }
        } else {
            wrapMonto.style.display = 'none';
        }
        actualizarInstalacionVenta();
    });
    document.getElementById('monto_instalacion_mobile').addEventListener('blur', actualizarInstalacionVenta);

    document.addEventListener('click', function (event) {
        const lista = document.getElementById('productos_lista');
        if (!inputBuscar.contains(event.target) && !lista.contains(event.target)) {
            lista.classList.add('d-none');
        }
    });
});
</script>
@endpush
