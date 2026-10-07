<?php

namespace App\Support;

/**
 * Resuelve el alcance (línea de negocio) del usuario autenticado.
 *
 * Sin usuario autenticado (CLI, seeders, tests sin actingAs) no hay alcance:
 * nunca se filtra nada fuera de una request real del panel.
 */
class AlcanceResponsable
{
    public static function activo(): bool
    {
        return auth()->check() && auth()->user()->responsable_id !== null;
    }

    public static function idActual(): ?int
    {
        return auth()->check() ? auth()->user()->responsable_id : null;
    }
}
