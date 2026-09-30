<?php

namespace App\Domain\Materiales\Models;

use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Talla;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomLinea extends Model
{
    protected $fillable = [
        'modelo_id',
        'material_id',
        'talla_id',
        'cantidad',
        'unidad',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:4',
        ];
    }

    public function modelo(): BelongsTo
    {
        return $this->belongsTo(Modelo::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function talla(): BelongsTo
    {
        return $this->belongsTo(Talla::class);
    }
}
