<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Proveedor::withCount('productos');

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('contacto', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        $proveedores = $query->latest()->paginate(15);

        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'contacto' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $proveedor = Proveedor::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Proveedor creado exitosamente.',
                'proveedor' => $proveedor
            ]);
        }

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor creado exitosamente.');
    }

    public function show($proveedore)
    {
        $proveedor = Proveedor::findOrFail($proveedore);
        $proveedor->load('productos');
        
        // Detectar si es petición AJAX/JSON
        if (request()->expectsJson() || request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'proveedor' => [
                    'id' => $proveedor->id,
                    'nombre' => $proveedor->nombre ?? '',
                    'contacto' => $proveedor->contacto ?? '',
                    'telefono' => $proveedor->telefono ?? '',
                    'email' => $proveedor->email ?? '',
                    'created_at' => $proveedor->created_at ? $proveedor->created_at->toDateTimeString() : null,
                    'productos' => $proveedor->productos->map(function($producto) {
                        return [
                            'id' => $producto->id,
                            'codigo' => $producto->codigo ?? '',
                            'nombre' => $producto->nombre ?? '',
                            'precio_venta' => $producto->precio_venta ?? 0,
                            'stock_actual' => $producto->stock_actual ?? 0,
                            'activo' => $producto->activo ?? false
                        ];
                    })->toArray()
                ]
            ]);
        }
        
        return view('proveedores.show', compact('proveedor'));
    }

    public function edit($proveedore)
    {
        $proveedor = Proveedor::findOrFail($proveedore);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'proveedor' => $proveedor
            ]);
        }

        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $proveedore)
    {
        $proveedor = Proveedor::findOrFail($proveedore);
        
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'contacto' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $proveedor->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Proveedor actualizado exitosamente.',
                'proveedor' => $proveedor->fresh()
            ]);
        }

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function destroy($proveedore)
    {
        $proveedor = Proveedor::findOrFail($proveedore);
        
        // Verificar si tiene productos asociados
        if ($proveedor->productos()->count() > 0) {
            return redirect()->route('proveedores.index')
                ->with('error', 'No se puede eliminar el proveedor porque tiene productos asociados.');
        }

        $proveedor->delete();

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado exitosamente.');
    }
}
