<?php

namespace App\Domain\Materiales\Models;

use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Inventario\Models\MovimientoMaterial;
use App\Domain\Materiales\Enums\TipoMaterial;
use App\Domain\Materiales\Enums\UnidadMaterial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $table = 'materiales';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'unidad',
        'composicion',
        'color',
        'ancho_m',
        'responsable_id',
        'activo',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoMaterial::class,
            'unidad' => UnidadMaterial::class,
            'ancho_m' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }

    public function precios(): HasMany
    {
        return $this->hasMany(PrecioMaterial::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoMaterial::class);
    }
}
