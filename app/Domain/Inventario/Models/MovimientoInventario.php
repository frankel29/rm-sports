<?php

namespace App\Domain\Inventario\Models;

use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Catalogo\Models\Sku;
use App\Domain\Inventario\Enums\OrigenRegistro;
use App\Domain\Inventario\Enums\TipoMovimientoInventario;
use App\Domain\Inventario\Enums\Ubicacion;
use App\Domain\Inventario\Models\Concerns\EsInmutable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MovimientoInventario extends Model
{
    use EsInmutable;

    protected $table = 'movimientos_inventario';

    public const UPDATED_AT = null;

    protected $fillable = [
        'sku_id',
        'tipo',
        'cantidad',
        'costo_unitario',
        'fecha_hecho',
        'referencia_type',
        'referencia_id',
        'responsable_id',
        'ubicacion',
        'origen',
        'user_id',
        'nota',
        'movimiento_reversado_id',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoMovimientoInventario::class,
            'cantidad' => 'decimal:2',
            'costo_unitario' => 'decimal:4',
            'fecha_hecho' => 'date',
            'ubicacion' => Ubicacion::class,
            'origen' => OrigenRegistro::class,
        ];
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(Sku::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referencia(): MorphTo
    {
        return $this->morphTo();
    }

    public function movimientoReversado(): BelongsTo
    {
        return $this->belongsTo(self::class, 'movimiento_reversado_id');
    }
}
