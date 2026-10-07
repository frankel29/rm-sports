<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // restrictOnDelete, NO nullOnDelete: si al borrar un responsable sus
            // usuarios quedaran con responsable_id = null, se convertirían en
            // administradores generales (escalada de privilegios).
            $table->foreignId('responsable_id')->nullable()->after('rol')
                ->constrained('responsables')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('responsable_id');
        });
    }
};
