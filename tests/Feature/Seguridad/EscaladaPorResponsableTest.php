<?php

use App\Domain\Catalogo\Models\Responsable;
use App\Domain\Costeo\Models\GastoMensual;
use App\Enums\RolUsuario;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->rm = Responsable::query()->create(['codigo' => 'RM', 'nombre' => 'RM']);
    $this->gl = Responsable::query()->create(['codigo' => 'GL', 'nombre' => 'GL']);
});

it('solo el administrador general accede a responsables, configuración de IVA e importación', function (string $ruta) {
    $adminRm = User::factory()->conRol(RolUsuario::ADMIN)->create(['responsable_id' => $this->rm->id]);

    $this->actingAs($adminRm)->get($ruta)->assertForbidden();
})->with([
    '/admin/responsables',
    '/admin/configuracion-ivas',
    '/admin/importar-plantilla',
]);

it('no se puede borrar un responsable que tiene usuarios asignados (evita que esos usuarios queden como administradores generales)', function () {
    $adminRm = User::factory()->conRol(RolUsuario::ADMIN)->create(['responsable_id' => $this->rm->id]);

    // Savepoint: en Postgres un error de FK aborta la transacción del test.
    expect(fn () => DB::transaction(fn () => $this->rm->delete()))->toThrow(QueryException::class);
    expect($adminRm->fresh()->esAdminGeneral())->toBeFalse();
});

it('un administrador de línea ve los registros compartidos pero no puede modificarlos ni borrarlos', function () {
    $compartido = GastoMensual::query()->create(['concepto' => 'Arriendo del local', 'responsable_id' => null]);

    $this->actingAs(User::factory()->conRol(RolUsuario::ADMIN)->create(['responsable_id' => $this->gl->id]));

    $visible = GastoMensual::query()->findOrFail($compartido->id);

    expect(fn () => $visible->update(['monto_mensual' => 999]))->toThrow(AuthorizationException::class);
    expect(fn () => $visible->delete())->toThrow(AuthorizationException::class);

    auth()->logout();
    expect($compartido->fresh()->responsable_id)->toBeNull()
        ->and($compartido->fresh()->monto_mensual)->toBeNull();
});
