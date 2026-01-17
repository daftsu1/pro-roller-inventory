@extends('layouts.app')

@section('title', 'Nuevo Usuario')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Nuevo Usuario</h2>
    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('usuarios.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label">Nombre Completo *</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                           id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Contraseña *</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" required>
                    <small class="text-muted">Mínimo 8 caracteres</small>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar Contraseña *</label>
                    <input type="password" class="form-control" 
                           id="password_confirmation" name="password_confirmation" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Roles *</label>
                <div class="border rounded p-3" style="background: #f8f9fa;">
                    <div class="row g-3">
                        @forelse($roles as $rol)
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" 
                                       id="rol_{{ $rol->id }}" value="{{ $rol->name }}"
                                       {{ in_array($rol->name, old('roles', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="rol_{{ $rol->id }}">
                                    <i class="bi bi-person-badge me-1"></i>
                                    {{ ucfirst($rol->name) }}
                                </label>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <p class="text-muted mb-0">No hay roles disponibles. Los roles se crean desde la base de datos.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                <small class="text-muted">Selecciona uno o más roles para el usuario. Puedes agregar más roles desde la base de datos.</small>
                @error('roles')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="activo">
                        Usuario Activo
                    </label>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
