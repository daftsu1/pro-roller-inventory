<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'precio_compra',
        'precio_venta',
        'stock_actual',
        'stock_minimo',
        'categoria_id',
        'proveedor_id',
        'activo',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function codigosBarras(): HasMany
    {
        return $this->hasMany(CodigoBarras::class);
    }

    /**
     * Búsqueda unificada para ventas:
     * - por id (si el término es numérico)
     * - por código principal o códigos de barras alternativos
     * - por nombre (like)
     */
    public static function buscarParaVenta(string $termino): Builder
    {
        $termino = trim($termino);

        return static::query()
            ->where('activo', true)
            ->where('stock_actual', '>', 0)
            ->where(function (Builder $q) use ($termino) {
                $esNumerico = ctype_digit($termino);

                if ($esNumerico) {
                    $id = (int) $termino;
                    $q->where('id', $id);
                }

                $q->orWhere(function (Builder $sub) use ($termino) {
                    $sub->where('codigo', $termino)
                        ->orWhereHas('codigosBarras', function (Builder $q2) use ($termino) {
                            $q2->where('codigo', $termino);
                        });
                });

                $q->orWhere('nombre', 'like', '%' . $termino . '%');
            });
    }

    public static function buscarPorCodigo(string $codigo): Builder
    {
        $codigo = trim($codigo);

        return static::query()->where(function (Builder $q) use ($codigo) {
            $q->where('codigo', $codigo)
                ->orWhereHas('codigosBarras', function (Builder $q2) use ($codigo) {
                    $q2->where('codigo', $codigo);
                });
        });
    }

    public function tieneStockMinimo(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }
}
