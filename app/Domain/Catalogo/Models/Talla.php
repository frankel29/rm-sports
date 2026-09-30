<?php

namespace App\Domain\Catalogo\Models;

use App\Domain\Catalogo\Enums\TipoTalla;
use Illuminate\Database\Eloquent\Model;

class Talla extends Model
{
    protected $fillable = [
        'codigo',
        'orden',
        'tipo',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoTalla::class,
        ];
    }
}
