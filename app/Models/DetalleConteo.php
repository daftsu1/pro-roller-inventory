<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleConteo extends Model
{
    use HasFactory;

    protected $table = 'detalle_conteos';

    protected $fillable = [
        'conteo_fisico_id',
        'producto_id',
        'cantidad_sistema',
        'cantidad_fisica',
        'diferencia',
        'escaneado',
        'ultima_actualizacion',
    ];

    protected $casts = [
        'cantidad_sistema' => 'decimal:2',
        'cantidad_fisica' => 'integer', // Solo números enteros
        'diferencia' => 'decimal:2',
        'escaneado' => 'boolean',
        'ultima_actualizacion' => 'datetime',
    ];

    public function conteoFisico(): BelongsTo
    {
        return $this->belongsTo(ConteoFisico::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    // Método para actualizar cantidad física y recalcular diferencia
    public function actualizarCantidadFisica($cantidad)
    {
        // Asegurar que sea un entero
        $cantidadEntera = (int) round($cantidad);
        
        $this->update([
            'cantidad_fisica' => $cantidadEntera,
            'diferencia' => $cantidadEntera - $this->cantidad_sistema,
            'escaneado' => true,
            'ultima_actualizacion' => now(),
        ]);
        $this->refresh();
    }

    // Incrementar cantidad física (para escaneo múltiple)
    public function incrementarCantidad($cantidad = 1)
    {
        $nuevaCantidad = (int) $this->cantidad_fisica + (int) $cantidad;
        $this->actualizarCantidadFisica($nuevaCantidad);
    }

    // Verificar si hay diferencia
    public function tieneDiferencia(): bool
    {
        return $this->diferencia != 0;
    }

    // Obtener tipo de diferencia
    public function getTipoDiferenciaAttribute(): string
    {
        if ($this->diferencia > 0) {
            return 'sobrante';
        } elseif ($this->diferencia < 0) {
            return 'faltante';
        }
        return 'sin_diferencia';
    }
}
