<?php

namespace App\Domain\Catalogo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Colegio extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'contacto',
        'telefono',
        'es_generico',
        'activo',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'es_generico' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function modelos(): HasMany
    {
        return $this->hasMany(Modelo::class);
    }
}
