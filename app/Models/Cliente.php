<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'documento',
        'telefono',
        'email',
        'direccion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function ventasCompletadas(): HasMany
    {
        return $this->hasMany(Venta::class)->where('estado', 'completada');
    }

    public function getTotalComprasAttribute(): float
    {
        return $this->ventasCompletadas()->sum('total');
    }

    public function getTotalVentasAttribute(): int
    {
        return $this->ventasCompletadas()->count();
    }
}
