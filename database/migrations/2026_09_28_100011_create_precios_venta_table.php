<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precios_venta', function (Blueprint $table) {
            $table->id();
            $table->morphs('vendible');
            $table->decimal('precio', 10, 2);
            $table->boolean('incluye_iva');
            $table->date('vigente_desde');
            $table->date('vigente_hasta')->nullable();
            $table->foreignId('responsable_id')->constrained('responsables')->restrictOnDelete();
            $table->string('origen', 20)->default('CAPTURA');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precios_venta');
    }
};
