<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modelo_id')->constrained('modelos')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materiales')->restrictOnDelete();
            $table->foreignId('talla_id')->nullable()->constrained('tallas')->cascadeOnDelete();
            $table->decimal('cantidad', 8, 4);
            $table->string('unidad', 20);
            $table->text('notas')->nullable();
            $table->timestamps();

            // La unicidad de "una sola línea con talla NULL por modelo+material" se valida
            // en BomService: Postgres permite múltiples NULL en un índice único.
            $table->unique(['modelo_id', 'material_id', 'talla_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_lineas');
    }
};
