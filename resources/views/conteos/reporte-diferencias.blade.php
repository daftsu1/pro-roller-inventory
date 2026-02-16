<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Diferencias - {{ $conteo->nombre }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { padding: 20px; }
        }
        .header-reporte {
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-4">
        <!-- Encabezado -->
        <div class="header-reporte">
            <div class="row">
                <div class="col-md-8">
                    <h2>Reporte de Diferencias de Conteo Físico</h2>
                    <h4>{{ $conteo->nombre }}</h4>
                    <p class="mb-0">
                        <strong>Fecha del Conteo:</strong> {{ $conteo->fecha_conteo->format('d/m/Y') }}<br>
                        <strong>Creado por:</strong> {{ $conteo->usuario->nombre ?? '-' }}<br>
                        @if($conteo->fecha_fin)
                        <strong>Finalizado:</strong> {{ $conteo->fecha_fin->format('d/m/Y H:i') }}
                        @endif
                    </p>
                </div>
                <div class="col-md-4 text-end no-print">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                    <button onclick="window.close()" class="btn btn-secondary">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $estadisticas['total_productos'] }}</h3>
                        <small class="text-muted">Total Productos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $estadisticas['con_diferencias'] }}</h3>
                        <small class="text-muted">Con Diferencias</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $estadisticas['sin_diferencias'] }}</h3>
                        <small>Sin Diferencias</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-0">
                            @if($estadisticas['total_sobrantes'] > 0)
                                +{{ number_format($estadisticas['total_sobrantes'], 0) }}
                            @else
                                {{ number_format($estadisticas['total_faltantes'], 0) }}
                            @endif
                        </h3>
                        <small>
                            @if($estadisticas['total_sobrantes'] > $estadisticas['total_faltantes'])
                                Sobrantes Totales
                            @else
                                Faltantes Totales
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>

        @if($estadisticas['valor_diferencias'] > 0)
        <div class="alert alert-info">
            <strong>Valor Estimado de Diferencias:</strong> 
            ${{ number_format($estadisticas['valor_diferencias'], 2) }}
            <small class="text-muted">(basado en precio de compra)</small>
        </div>
        @endif

        <!-- Tabla de diferencias -->
        @if($detallesConDiferencias->count() > 0)
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Productos con Diferencias</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th class="text-end">Stock Sistema</th>
                                <th class="text-end">Stock Físico</th>
                                <th class="text-end">Diferencia</th>
                                <th>Tipo</th>
                                <th class="text-end">Valor Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $contador = 1; @endphp
                            @foreach($detallesConDiferencias as $detalle)
                            <tr>
                                <td>{{ $contador++ }}</td>
                                <td>{{ $detalle->producto->codigo }}</td>
                                <td>{{ $detalle->producto->nombre }}</td>
                                <td>{{ $detalle->producto->categoria->nombre ?? '-' }}</td>
                                <td class="text-end">{{ number_format($detalle->cantidad_sistema, 0) }}</td>
                                <td class="text-end"><strong>{{ number_format($detalle->cantidad_fisica, 0) }}</strong></td>
                                <td class="text-end">
                                    @if($detalle->diferencia > 0)
                                        <span class="badge bg-success">+{{ number_format($detalle->diferencia, 0) }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ number_format($detalle->diferencia, 0) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($detalle->diferencia > 0)
                                        <span class="badge bg-success">Sobrante</span>
                                    @else
                                        <span class="badge bg-danger">Faltante</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @php
                                        $valorDiferencia = abs($detalle->diferencia) * ($detalle->producto->precio_compra ?? 0);
                                    @endphp
                                    ${{ number_format($valorDiferencia, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="6" class="text-end">TOTALES:</th>
                                <th class="text-end">
                                    Sobrantes: <span class="badge bg-success">+{{ number_format($estadisticas['total_sobrantes'], 0) }}</span><br>
                                    Faltantes: <span class="badge bg-danger">{{ number_format($estadisticas['total_faltantes'], 0) }}</span>
                                </th>
                                <th></th>
                                <th class="text-end">${{ number_format($estadisticas['valor_diferencias'], 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-success">
            <h5><i class="bi bi-check-circle"></i> No hay diferencias</h5>
            <p class="mb-0">Todos los productos coinciden entre el stock del sistema y el conteo físico.</p>
        </div>
        @endif

        <!-- Resumen por tipo -->
        @if($detallesConDiferencias->count() > 0)
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">Productos con Sobrantes</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach($detallesConDiferencias->where('diferencia', '>', 0) as $detalle)
                            <li>
                                <strong>{{ $detalle->producto->codigo }}</strong> - {{ $detalle->producto->nombre }}
                                <span class="badge bg-success">+{{ number_format($detalle->diferencia, 0) }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">Productos con Faltantes</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach($detallesConDiferencias->where('diferencia', '<', 0) as $detalle)
                            <li>
                                <strong>{{ $detalle->producto->codigo }}</strong> - {{ $detalle->producto->nombre }}
                                <span class="badge bg-danger">{{ number_format($detalle->diferencia, 0) }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="mt-4 text-center text-muted no-print">
            <small>Reporte generado el {{ now()->format('d/m/Y H:i:s') }}</small>
        </div>
    </div>
</body>
</html>
