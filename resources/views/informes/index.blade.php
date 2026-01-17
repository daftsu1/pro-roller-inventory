@extends('layouts.app')

@section('title', 'Informes')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mb-md-4">
    <h2 class="mb-2 mb-md-0"><i class="bi bi-graph-up"></i> Informes</h2>
</div>

<div class="row g-3">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2 mb-md-3">
                    <div class="text-primary me-2 me-md-3">
                        <i class="bi bi-cart-check fs-2 fs-md-1"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0">Ventas</h5>
                        <small class="text-muted d-none d-md-block">Reporte de ventas por período</small>
                    </div>
                </div>
                <p class="card-text text-muted small d-none d-md-block">Consulta todas las ventas realizadas en un rango de fechas con estadísticas detalladas.</p>
                <a href="{{ route('informes.ventas') }}" class="btn btn-primary btn-sm d-md-block">
                    <i class="bi bi-arrow-right"></i> Ver Informe
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="text-success me-3">
                        <i class="bi bi-bar-chart fs-1"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0">Productos Vendidos</h5>
                        <small class="text-muted">Ranking de productos más vendidos</small>
                    </div>
                </div>
                <p class="card-text text-muted">Consulta qué productos se venden más y genera más ingresos.</p>
                <a href="{{ route('informes.productos-vendidos') }}" class="btn btn-success">
                    <i class="bi bi-arrow-right"></i> Ver Informe
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="text-warning me-3">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0">Stock Bajo</h5>
                        <small class="text-muted">Productos con stock mínimo</small>
                    </div>
                </div>
                <p class="card-text text-muted">Lista de productos que necesitan reposición de inventario.</p>
                <a href="{{ route('informes.stock-bajo') }}" class="btn btn-warning">
                    <i class="bi bi-arrow-right"></i> Ver Informe
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="text-info me-3">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0">Clientes</h5>
                        <small class="text-muted">Análisis de clientes</small>
                    </div>
                </div>
                <p class="card-text text-muted">Consulta clientes más frecuentes y que más compran.</p>
                <a href="{{ route('informes.clientes') }}" class="btn btn-info">
                    <i class="bi bi-arrow-right"></i> Ver Informe
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="text-primary me-3">
                        <i class="bi bi-file-earmark-text fs-1"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0">Resumen General</h5>
                        <small class="text-muted">Vista general del negocio</small>
                    </div>
                </div>
                <p class="card-text text-muted">Resumen completo con todas las métricas principales del negocio.</p>
                <a href="{{ route('informes.resumen') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-right"></i> Ver Informe
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
