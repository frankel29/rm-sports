<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modelos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('colegio_id')->constrained('colegios')->restrictOnDelete();
            $table->foreignId('prenda_id')->constrained('prendas')->restrictOnDelete();
            $table->string('genero', 20)->nullable();
            $table->string('color')->nullable();
            $table->string('tipo_abastecimiento', 20);
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->foreignId('responsable_id')->constrained('responsables')->restrictOnDelete();
            $table->foreignId('talla_desde_id')->nullable()->constrained('tallas')->nullOnDelete();
            $table->foreignId('talla_hasta_id')->nullable()->constrained('tallas')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->string('revision')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modelos');
    }
};
