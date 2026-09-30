<?php

namespace App\Domain\Catalogo\Models;

use App\Domain\Catalogo\Enums\CategoriaPrenda;
use App\Domain\Costeo\Models\TarifaManoObra;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prenda extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'categoria',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'categoria' => CategoriaPrenda::class,
        ];
    }

    public function modelos(): HasMany
    {
        return $this->hasMany(Modelo::class);
    }

    public function tarifasManoObra(): HasMany
    {
        return $this->hasMany(TarifaManoObra::class);
    }
}
