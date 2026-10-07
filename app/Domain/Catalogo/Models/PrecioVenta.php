<?php

namespace App\Domain\Catalogo\Models;

use App\Support\Concerns\FiltraPorResponsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PrecioVenta extends Model
{
    use FiltraPorResponsable;

    protected $table = 'precios_venta';

    protected $fillable = [
        'vendible_type',
        'vendible_id',
        'precio',
        'incluye_iva',
        'vigente_desde',
        'vigente_hasta',
        'responsable_id',
        'origen',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'incluye_iva' => 'boolean',
            'vigente_desde' => 'date',
            'vigente_hasta' => 'date',
        ];
    }

    public function vendible(): MorphTo
    {
        return $this->morphTo();
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }
}
