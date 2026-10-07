<?php

namespace App\Domain\Costeo\Models;

use App\Domain\Catalogo\Models\Responsable;
use App\Support\Concerns\FiltraPorResponsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GastoMensual extends Model
{
    use FiltraPorResponsable;

    protected $table = 'gastos_mensuales';

    protected $fillable = [
        'concepto',
        'monto_mensual',
        'responsable_id',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'monto_mensual' => 'decimal:2',
        ];
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }
}
