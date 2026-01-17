@if($ventas->total() > 0)
    <div class="mb-3 text-muted small">
        Mostrando {{ $ventas->firstItem() }} - {{ $ventas->lastItem() }} de {{ $ventas->total() }} ventas
    </div>
@endif

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Factura</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Usuario</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $venta)
            <tr>
                <td>{{ $venta->numero_factura }}</td>
                <td>{{ $venta->fecha->format('d/m/Y') }}</td>
                <td>{{ $venta->cliente_nombre ?? '-' }}</td>
                <td class="fw-bold">${{ number_format($venta->total, 2) }}</td>
                <td>{{ $venta->usuario->nombre }}</td>
                <td>
                    @if($venta->estado === 'completada')
                        <span class="badge bg-success">Completada</span>
                    @elseif($venta->estado === 'pendiente')
                        <span class="badge bg-warning">Pendiente</span>
                    @else
                        <span class="badge bg-danger">Cancelada</span>
                    @endif
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-info" onclick="abrirVenta({{ $venta->id }})">
                            <i class="bi bi-eye"></i> {{ $venta->estado === 'pendiente' ? 'Editar' : 'Ver' }}
                        </button>
                        @if($venta->estado === 'pendiente')
                            <button type="button" class="btn btn-outline-danger" onclick="eliminarVenta({{ $venta->id }}, '{{ $venta->numero_factura }}')">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted">
                    <i class="bi bi-inbox"></i><br>
                    No se encontraron ventas
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginación -->
@if($ventas->hasPages())
    <div class="mt-3">
        {{ $ventas->links() }}
    </div>
@endif
