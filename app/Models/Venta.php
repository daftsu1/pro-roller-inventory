<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_factura',
        'fecha',
        'cliente_id',
        'cliente_nombre',
        'cliente_documento',
        'total',
        'usuario_id',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total' => 'decimal:2',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public static function crearConMovimientos(array $datos)
    {
        return DB::transaction(function () use ($datos) {
            // Generar número de factura
            $ultimaVenta = self::latest('id')->first();
            $numeroFactura = 'VENT-' . str_pad(($ultimaVenta?->id ?? 0) + 1, 6, '0', STR_PAD_LEFT);

            // Crear venta
            $venta = self::create([
                'numero_factura' => $numeroFactura,
                'fecha' => $datos['fecha'] ?? now(),
                'cliente_nombre' => $datos['cliente_nombre'] ?? null,
                'cliente_documento' => $datos['cliente_documento'] ?? null,
                'total' => 0, // Se calculará después
                'usuario_id' => auth()->id(),
                'estado' => $datos['estado'] ?? 'pendiente', // Por defecto pendiente
            ]);

            $total = 0;

            // Crear detalles
            foreach ($datos['productos'] as $productoData) {
                $producto = Producto::findOrFail($productoData['id']);
                
                // Si la venta está completada, validar stock antes
                if ($venta->estado === 'completada') {
                    if ($producto->stock_actual < $productoData['cantidad']) {
                        throw new \Exception("Stock insuficiente para el producto: {$producto->nombre}");
                    }
                }

                $subtotal = $productoData['cantidad'] * $producto->precio_venta;
                $total += $subtotal;

                // Crear detalle de venta
                $detalle = $venta->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $productoData['cantidad'],
                    'precio_unitario' => $producto->precio_venta,
                    'subtotal' => $subtotal,
                ]);

                // Solo crear movimientos y descontar stock si la venta está completada
                if ($venta->estado === 'completada') {
                    // Crear movimiento de salida automático
                    MovimientoInventario::create([
                        'producto_id' => $producto->id,
                        'tipo' => 'salida',
                        'cantidad' => $productoData['cantidad'],
                        'motivo' => "Venta #{$venta->numero_factura}",
                        'venta_id' => $venta->id,
                        'detalle_venta_id' => $detalle->id,
                        'usuario_id' => auth()->id(),
                        'fecha' => now(),
                    ]);

                    // Actualizar stock
                    $producto->decrement('stock_actual', $productoData['cantidad']);
                }
            }

            // Actualizar total de venta
            $venta->update(['total' => $total]);

            return $venta;
        });
    }

    /**
     * Completar una venta pendiente (descontar stock y crear movimientos)
     */
    public function completar()
    {
        if ($this->estado !== 'pendiente') {
            throw new \Exception('Solo se pueden completar ventas pendientes');
        }

        return DB::transaction(function () {
            // Re-cargar detalles con productos frescos (lock for update para evitar condiciones de carrera)
            $detalles = $this->detalles()->with(['producto' => function($query) {
                $query->lockForUpdate(); // Bloquear fila para evitar condiciones de carrera
            }])->get();

            // Validar stock de todos los productos con lock
            foreach ($detalles as $detalle) {
                // Calcular stock disponible (considerando otras ventas pendientes)
                $cantidadEnOtrasVentasPendientes = DetalleVenta::where('producto_id', $detalle->producto_id)
                    ->whereHas('venta', function($q) {
                        $q->where('estado', 'pendiente')
                          ->where('id', '!=', $this->id); // Excluir esta venta
                    })
                    ->sum('cantidad');
                
                $stockDisponible = $detalle->producto->stock_actual - $cantidadEnOtrasVentasPendientes;
                
                if ($detalle->cantidad > $stockDisponible) {
                    throw new \Exception(
                        "Stock insuficiente para el producto: {$detalle->producto->nombre}. " .
                        "Stock disponible: {$stockDisponible}, cantidad requerida: {$detalle->cantidad}"
                    );
                }
            }

            // Crear movimientos y descontar stock (ahora con seguridad)
            foreach ($detalles as $detalle) {
                // Crear movimiento de salida
                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_id,
                    'tipo' => 'salida',
                    'cantidad' => $detalle->cantidad,
                    'motivo' => "Venta #{$this->numero_factura}",
                    'venta_id' => $this->id,
                    'detalle_venta_id' => $detalle->id,
                    'usuario_id' => auth()->id(),
                    'fecha' => now(),
                ]);

                // Actualizar stock con lock (ya está bloqueado por lockForUpdate)
                $detalle->producto->decrement('stock_actual', $detalle->cantidad);
            }

            // Cambiar estado a completada
            $this->update(['estado' => 'completada']);

            return $this;
        });
    }

    /**
     * Cancelar una venta completada (devolver stock y crear movimientos de entrada)
     */
    public function cancelar()
    {
        if ($this->estado !== 'completada') {
            throw new \Exception('Solo se pueden cancelar ventas completadas');
        }

        return DB::transaction(function () {
            // Devolver stock y crear movimientos de entrada
            foreach ($this->detalles as $detalle) {
                // Crear movimiento de entrada (devolución)
                MovimientoInventario::create([
                    'producto_id' => $detalle->producto_id,
                    'tipo' => 'entrada',
                    'cantidad' => $detalle->cantidad,
                    'motivo' => "Cancelación de Venta #{$this->numero_factura}",
                    'venta_id' => $this->id,
                    'detalle_venta_id' => $detalle->id,
                    'usuario_id' => auth()->id(),
                    'fecha' => now(),
                ]);

                // Devolver stock
                $detalle->producto->increment('stock_actual', $detalle->cantidad);
            }

            // Cambiar estado a cancelada
            $this->update(['estado' => 'cancelada']);

            return $this;
        });
    }
}
