<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION bloquear_modificacion_movimientos()
            RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION 'Los movimientos de inventario son inmutables: use un movimiento de reverso en lugar de % en % (id=%)',
                    TG_OP, TG_TABLE_NAME, OLD.id;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER movimientos_inventario_inmutable
                BEFORE UPDATE OR DELETE ON movimientos_inventario
                FOR EACH ROW EXECUTE FUNCTION bloquear_modificacion_movimientos();

            CREATE TRIGGER movimientos_material_inmutable
                BEFORE UPDATE OR DELETE ON movimientos_material
                FOR EACH ROW EXECUTE FUNCTION bloquear_modificacion_movimientos();
        SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS movimientos_inventario_inmutable ON movimientos_inventario;
            DROP TRIGGER IF EXISTS movimientos_material_inmutable ON movimientos_material;
            DROP FUNCTION IF EXISTS bloquear_modificacion_movimientos();
        SQL);
    }
};
