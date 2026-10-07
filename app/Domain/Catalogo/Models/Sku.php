<?php

namespace App\Domain\Catalogo\Models;

use App\Domain\Inventario\Models\MovimientoInventario;
use App\Support\AlcanceResponsable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Sku extends Model
{
    protected $fillable = [
        'modelo_id',
        'talla_id',
        'codigo',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * El SKU no tiene columna "responsable_id" propia: la hereda de su
     * Modelo. Se filtra por esa relación en lugar de usar el trait
     * FiltraPorResponsable (pensado para columnas propias).
     */
    protected static function booted(): void
    {
        static::addGlobalScope('responsable', function (Builder $query) {
            if (AlcanceResponsable::activo()) {
                $query->whereHas(
                    'modelo',
                    fn (Builder $q) => $q->where('responsable_id', AlcanceResponsable::idActual()),
                );
            }
        });
    }

    public function modelo(): BelongsTo
    {
        return $this->belongsTo(Modelo::class);
    }

    public function talla(): BelongsTo
    {
        return $this->belongsTo(Talla::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function preciosVenta(): MorphMany
    {
        return $this->morphMany(PrecioVenta::class, 'vendible');
    }
}
