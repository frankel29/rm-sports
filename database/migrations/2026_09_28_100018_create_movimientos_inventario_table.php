<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sku_id')->constrained('skus')->restrictOnDelete();
            $table->string('tipo', 20);
            $table->decimal('cantidad', 12, 2);
            $table->decimal('costo_unitario', 12, 4)->nullable();
            $table->date('fecha_hecho');
            $table->nullableMorphs('referencia');
            $table->foreignId('responsable_id')->nullable()->constrained('responsables')->nullOnDelete();
            $table->string('ubicacion', 20)->nullable();
            $table->string('origen', 20)->default('CAPTURA');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->text('nota')->nullable();
            $table->foreignId('movimiento_reversado_id')->nullable()
                ->constrained('movimientos_inventario')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['sku_id', 'fecha_hecho']);
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
