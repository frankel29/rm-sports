<?php

namespace App\Domain\Materiales\Models;

use App\Domain\Catalogo\Models\Modelo;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Talla;
use App\Support\Concerns\FiltraPorResponsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostoCompra extends Model
{
    use FiltraPorResponsable;

    protected $table = 'costos_compra';

    protected $fillable = [
        'modelo_id',
        'talla_id',
        'costo',
        'proveedor_id',
        'vigente_desde',
        'vigente_hasta',
        'responsable_id',
        'origen',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'costo' => 'decimal:4',
            'vigente_desde' => 'date',
            'vigente_hasta' => 'date',
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

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }
}
