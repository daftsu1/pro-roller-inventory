<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Cliente::withCount(['ventas as ventas_count']);

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('documento', 'like', "%{$buscar}%")
                  ->orWhere('telefono', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        // Filtro por estado
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo == '1');
        }

        $clientes = $query->latest()->paginate(15);

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'documento' => 'nullable|string|max:50|unique:clientes,documento',
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'direccion' => 'nullable|string',
                'activo' => 'boolean',
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

        $cliente = Cliente::create([
            'nombre' => $validated['nombre'],
            'documento' => $validated['documento'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'email' => $validated['email'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'activo' => $request->has('activo') ? true : false,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cliente creado exitosamente.',
                'cliente' => $cliente
            ]);
        }

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['ventas' => function($query) {
            $query->with('usuario')->latest()->limit(20);
        }]);
        
        // Estadísticas
        $estadisticas = [
            'total_ventas' => $cliente->ventas()->where('estado', 'completada')->count(),
            'total_compras' => $cliente->ventas()->where('estado', 'completada')->sum('total'),
            'ventas_pendientes' => $cliente->ventas()->where('estado', 'pendiente')->count(),
            'venta_mas_reciente' => $cliente->ventas()->latest()->first(),
        ];

        return view('clientes.show', compact('cliente', 'estadisticas'));
    }

    public function edit(Cliente $cliente)
    {
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'cliente' => $cliente
            ]);
        }

        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        try {
            // Si es una solicitud JSON, usar json()->all(), sino usar all()
            if ($request->isJson()) {
                $data = $request->json()->all();
                // Remover _method si existe (no es necesario para validación)
                unset($data['_method']);
                $request->merge($data);
            }
            
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'documento' => 'nullable|string|max:50|unique:clientes,documento,' . $cliente->id,
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'direccion' => 'nullable|string',
                'activo' => 'boolean',
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

        // Preparar datos para actualización
        $updateData = [
            'nombre' => $validated['nombre'],
            'documento' => $validated['documento'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'email' => $validated['email'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'activo' => isset($validated['activo']) ? (bool)$validated['activo'] : ($request->has('activo') ? true : false),
        ];

        $cliente->update($updateData);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado exitosamente.',
                'cliente' => $cliente->fresh()
            ]);
        }

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(Cliente $cliente)
    {
        // Verificar si tiene ventas asociadas
        if ($cliente->ventas()->count() > 0) {
            return redirect()->route('clientes.index')
                ->with('error', 'No se puede eliminar el cliente porque tiene ventas asociadas.');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }
}
