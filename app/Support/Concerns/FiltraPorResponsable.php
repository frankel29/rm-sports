<?php

namespace App\Support\Concerns;

use App\Support\AlcanceResponsable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Para modelos con columna "responsable_id" propia (modelos, kits, precios,
 * proveedores, materiales, costos de compra, tarifas de mano de obra, gastos,
 * movimientos). Si el usuario autenticado tiene una línea asignada:
 *
 * - LECTURA: solo ve registros de su línea o sin línea asignada (compartidos).
 * - ESCRITURA: todo lo que cree o edite queda forzado a su propia línea,
 *   sin importar qué se haya enviado desde el formulario — así un usuario
 *   de línea no puede "mover" un registro a la otra línea ni crear uno ahí.
 * - COMPARTIDOS: los ve pero no puede editarlos ni borrarlos (si los editara,
 *   el forzado los convertiría en registros de su línea y desaparecerían
 *   para la otra). Solo el administrador general los modifica.
 *
 * Un administrador general (responsable_id = null) no tiene ningún filtro.
 *
 * Límite conocido: un update/delete masivo por query builder
 * (Model::query()->update([...])) no dispara eventos de modelo. El scope sí
 * limita las filas afectadas, pero no se fuerza la línea. No usar ese patrón
 * sobre estos modelos en código que corra con un usuario de línea.
 */
trait FiltraPorResponsable
{
    protected static function bootFiltraPorResponsable(): void
    {
        static::addGlobalScope('responsable', function (Builder $query) {
            if (! AlcanceResponsable::activo()) {
                return;
            }

            $tabla = $query->getModel()->getTable();

            $query->where(function (Builder $q) use ($tabla) {
                $q->where("{$tabla}.responsable_id", AlcanceResponsable::idActual())
                    ->orWhereNull("{$tabla}.responsable_id");
            });
        });

        static::creating(function (Model $model) {
            if (AlcanceResponsable::activo()) {
                $model->responsable_id = AlcanceResponsable::idActual();
            }
        });

        static::updating(function (Model $model) {
            if (! AlcanceResponsable::activo()) {
                return;
            }

            static::impedirSiEsCompartido($model);
            $model->responsable_id = AlcanceResponsable::idActual();
        });

        static::deleting(function (Model $model) {
            if (AlcanceResponsable::activo()) {
                static::impedirSiEsCompartido($model);
            }
        });
    }

    private static function impedirSiEsCompartido(Model $model): void
    {
        if ($model->getOriginal('responsable_id') === null) {
            throw new AuthorizationException('Este registro es compartido entre líneas: solo el administrador general puede modificarlo.');
        }
    }
}
