<?php

namespace App\Domain\Catalogo\Models;

use App\Domain\Inventario\Models\MovimientoInventario;
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
