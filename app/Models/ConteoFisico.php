<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConteoFisico extends Model
{
    use HasFactory;

    protected $table = 'conteos_fisicos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha_conteo',
        'estado',
        'usuario_id',
        'fecha_inicio',
        'fecha_fin',
        'observaciones',
    ];

    protected $casts = [
        'fecha_conteo' => 'date',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleConteo::class);
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeEnProceso($query)
    {
        return $query->where('estado', 'en_proceso');
    }

    public function scopeFinalizados($query)
    {
        return $query->where('estado', 'finalizado');
    }

    // Métodos de utilidad
    public function iniciar()
    {
        $this->update([
            'estado' => 'en_proceso',
            'fecha_inicio' => now(),
        ]);
    }

    public function finalizar()
    {
        $this->update([
            'estado' => 'finalizado',
            'fecha_fin' => now(),
        ]);
    }

    public function cancelar()
    {
        $this->update([
            'estado' => 'cancelado',
        ]);
    }

    public function puedeEditar(): bool
    {
        return in_array($this->estado, ['pendiente', 'en_proceso']);
    }

    public function puedeFinalizar(): bool
    {
        return $this->estado === 'en_proceso';
    }

    public function getTotalProductosAttribute(): int
    {
        return $this->detalles()->count();
    }

    public function getProductosEscaneadosAttribute(): int
    {
        return $this->detalles()->where('escaneado', true)->count();
    }

    public function getTotalDiferenciasAttribute(): int
    {
        return $this->detalles()->where('diferencia', '!=', 0)->count();
    }
}
