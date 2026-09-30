<?php

namespace App\Domain\Catalogo\Models;

use App\Domain\Catalogo\Enums\Genero;
use App\Domain\Catalogo\Enums\TipoAbastecimiento;
use App\Domain\Materiales\Models\BomLinea;
use App\Domain\Materiales\Models\CostoCompra;
use App\Domain\Materiales\Models\Proveedor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modelo extends Model
{
    protected $fillable = [
        'codigo',
        'colegio_id',
        'prenda_id',
        'genero',
        'color',
        'tipo_abastecimiento',
        'proveedor_id',
        'responsable_id',
        'talla_desde_id',
        'talla_hasta_id',
        'activo',
        'revision',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'genero' => Genero::class,
            'tipo_abastecimiento' => TipoAbastecimiento::class,
            'activo' => 'boolean',
        ];
    }

    public function colegio(): BelongsTo
    {
        return $this->belongsTo(Colegio::class);
    }

    public function prenda(): BelongsTo
    {
        return $this->belongsTo(Prenda::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Responsable::class);
    }

    public function tallaDesde(): BelongsTo
    {
        return $this->belongsTo(Talla::class, 'talla_desde_id');
    }

    public function tallaHasta(): BelongsTo
    {
        return $this->belongsTo(Talla::class, 'talla_hasta_id');
    }

    public function skus(): HasMany
    {
        return $this->hasMany(Sku::class);
    }

    public function bomLineas(): HasMany
    {
        return $this->hasMany(BomLinea::class);
    }

    public function costosCompra(): HasMany
    {
        return $this->hasMany(CostoCompra::class);
    }

    public function esFabricado(): bool
    {
        return $this->tipo_abastecimiento === TipoAbastecimiento::FABRICADO;
    }

    public function esComprado(): bool
    {
        return $this->tipo_abastecimiento === TipoAbastecimiento::COMPRADO;
    }
}
