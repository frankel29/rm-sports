<?php

namespace App\Domain\Catalogo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Kit extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'responsable_id',
        'activo',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }

    public function componentes(): HasMany
    {
        return $this->hasMany(KitComponente::class);
    }

    public function preciosVenta(): MorphMany
    {
        return $this->morphMany(PrecioVenta::class, 'vendible');
    }
}
