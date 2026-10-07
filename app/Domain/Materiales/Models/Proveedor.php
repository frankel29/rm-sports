<?php

namespace App\Domain\Materiales\Models;

use App\Domain\Catalogo\Models\Responsable;
use App\Support\Concerns\FiltraPorResponsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proveedor extends Model
{
    use FiltraPorResponsable;

    protected $table = 'proveedores';

    protected $fillable = [
        'codigo',
        'nombre',
        'ruc',
        'telefono',
        'que_provee',
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
}
