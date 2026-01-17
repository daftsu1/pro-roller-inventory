<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:ver-usuarios')->only(['index', 'show']);
        $this->middleware('permission:crear-usuarios')->only(['create', 'store']);
        $this->middleware('permission:editar-usuarios')->only(['edit', 'update']);
        $this->middleware('permission:eliminar-usuarios')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = User::with('roles');

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        // Filtro por rol
        if ($request->filled('rol')) {
            $query->role($request->rol);
        }

        // Filtro por estado
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo == '1');
        }

        $usuarios = $query->latest()->paginate(15);
        $roles = Role::all();

        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'activo' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $usuario = User::create([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'activo' => $request->has('activo') ? true : false,
        ]);

        // Asignar roles
        if ($request->filled('roles')) {
            $usuario->syncRoles($validated['roles']);
        }

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function show(User $usuario)
    {
        $usuario->load('roles', 'ventas', 'movimientos');
        return view('usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario)
    {
        $roles = Role::all();
        $usuario->load('roles');
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id . '|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'activo' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $updateData = [
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'activo' => $request->has('activo') ? true : false,
        ];

        // Solo actualizar contraseña si se proporciona
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $usuario->update($updateData);

        // Actualizar roles
        if ($request->has('roles')) {
            $usuario->syncRoles($validated['roles'] ?? []);
        }

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $usuario)
    {
        // No permitir auto-eliminación
        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // Verificar si tiene registros asociados
        if ($usuario->ventas()->exists() || $usuario->movimientos()->exists()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No se puede eliminar el usuario porque tiene registros asociados (ventas o movimientos).');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}
