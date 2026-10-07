<?php

namespace App\Domain\Costeo\Models;

use App\Domain\Catalogo\Models\Prenda;
use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Costeo\Enums\FormaPago;
use App\Support\Concerns\FiltraPorResponsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifaManoObra extends Model
{
    use FiltraPorResponsable;

    protected $table = 'tarifas_mano_obra';

    protected $fillable = [
        'prenda_id',
        'operacion',
        'forma_pago',
        'valor',
        'responsable_id',
        'vigente_desde',
        'vigente_hasta',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'forma_pago' => FormaPago::class,
            'valor' => 'decimal:2',
            'vigente_desde' => 'date',
            'vigente_hasta' => 'date',
        ];
    }

    public function prenda(): BelongsTo
    {
        return $this->belongsTo(Prenda::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }
}
