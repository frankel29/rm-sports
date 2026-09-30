<?php

namespace App\Domain\Catalogo\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionIva extends Model
{
    protected $table = 'configuraciones_iva';

    protected $fillable = [
        'porcentaje',
        'vigente_desde',
        'vigente_hasta',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje' => 'decimal:2',
            'vigente_desde' => 'date',
            'vigente_hasta' => 'date',
        ];
    }
}
