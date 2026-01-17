<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'pro roller')</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        .sidebar {
            width: 220px;
            min-height: 100vh;
            background: #343a40;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        @media (max-width: 767.98px) {
            .sidebar {
                transform: translateX(-100%);
                width: 260px;
            }
            .sidebar.show {
                transform: translateX(0);
            }
        }
        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 1rem;
            padding-bottom: 0.5rem;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            white-space: nowrap;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .sidebar-footer {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 0.75rem 1rem;
            background: #343a40;
            flex-shrink: 0;
        }
        .sidebar-footer .user-email {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
            margin-left: 220px;
        }
        @media (max-width: 767.98px) {
            .main-content {
                margin-left: 0;
            }
        }
        @media (max-width: 767.98px) {
            body {
                padding-top: 56px;
            }
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        .sidebar-overlay.show {
            display: block;
        }
        .mobile-header {
            display: none;
            background: #343a40;
            color: white;
            padding: 0.75rem 1rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        @media (max-width: 767.98px) {
            .mobile-header {
                display: block;
            }
        }
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        @media (max-width: 575.98px) {
            .card-body {
                padding: 0.75rem;
            }
            .table {
                font-size: 0.875rem;
            }
            .btn {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    @auth
    <!-- Overlay para móviles -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
    <!-- Header móvil -->
    <div class="mobile-header d-flex justify-content-between align-items-center">
        <div>
            <button class="btn btn-link text-white p-0 me-2" onclick="toggleSidebar()">
                <i class="bi bi-list fs-4"></i>
            </button>
            <span class="fw-bold"><i class="bi bi-box-seam"></i> pro roller</span>
        </div>
        <div class="d-flex align-items-center">
            <small class="me-2 d-none d-sm-block">{{ auth()->user()->nombre }}</small>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
    
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
                <div class="sidebar-content">
                    <h5 class="text-white mb-4">
                        <i class="bi bi-box-seam"></i> pro roller
                    </h5>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('ventas.*') ? 'active' : '' }}" href="{{ route('ventas.index') }}">
                                <i class="bi bi-cart"></i> Ventas
                            </a>
                        </li>
                        
                        <li class="nav-item mt-3">
                            <hr class="text-white-50">
                            <small class="text-white-50 px-3">Catálogo</small>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}" href="{{ route('productos.index') }}">
                                <i class="bi bi-box"></i> Productos
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}" href="{{ route('categorias.index') }}">
                                <i class="bi bi-tags"></i> Categorías
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('proveedores.*') ? 'active' : '' }}" href="{{ route('proveedores.index') }}">
                                <i class="bi bi-truck"></i> Proveedores
                            </a>
                        </li>
                        
                        <li class="nav-item mt-3">
                            <hr class="text-white-50">
                            <small class="text-white-50 px-3">Relaciones</small>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}">
                                <i class="bi bi-person-badge"></i> Clientes
                            </a>
                        </li>
                        
                        <li class="nav-item mt-3">
                            <hr class="text-white-50">
                            <small class="text-white-50 px-3">Informes</small>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('informes.*') ? 'active' : '' }}" href="{{ route('informes.index') }}">
                                <i class="bi bi-graph-up"></i> Informes
                            </a>
                        </li>
                        
                        <li class="nav-item mt-3">
                            <hr class="text-white-50">
                            <small class="text-white-50 px-3">Historial</small>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('movimientos.*') ? 'active' : '' }}" href="{{ route('movimientos.index') }}">
                                <i class="bi bi-arrow-left-right"></i> Movimientos
                            </a>
                        </li>
                        
                        @role('admin')
                        <li class="nav-item mt-3">
                            <hr class="text-white-50">
                            <small class="text-white-50 px-3">Administración</small>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                                <i class="bi bi-people"></i> Usuarios
                            </a>
                        </li>
                        @endrole
                    </ul>
                </div>
                
                <div class="sidebar-footer">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="user-email">{{ auth()->user()->email }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-light p-1" title="Cerrar sesión">
                                <i class="bi bi-box-arrow-right"></i>
                            </button>
                        </form>
                    </div>
                </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
                <div class="p-3 p-md-4">
                    <!-- Alerts -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            </main>
    @else
        @yield('content')
    @endauth
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @auth
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }
        
        // Cerrar sidebar al hacer clic en un enlace (móviles)
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 767) {
                const navLinks = document.querySelectorAll('.sidebar .nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        setTimeout(toggleSidebar, 200);
                    });
                });
            }
            
            // Ajustar sidebar en resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 767) {
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('sidebarOverlay');
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                }
            });
        });
    </script>
    @endauth
    
    @stack('scripts')
</body>
</html>
