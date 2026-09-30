<?php

namespace App\Domain\Materiales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrecioMaterial extends Model
{
    protected $table = 'precios_material';

    protected $fillable = [
        'material_id',
        'precio',
        'proveedor_id',
        'vigente_desde',
        'vigente_hasta',
        'origen',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:4',
            'vigente_desde' => 'date',
            'vigente_hasta' => 'date',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }
}
