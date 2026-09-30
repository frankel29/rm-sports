<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materiales', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('tipo', 20);
            $table->string('unidad', 20);
            $table->string('composicion')->nullable();
            $table->string('color')->nullable();
            $table->decimal('ancho_m', 6, 2)->nullable();
            $table->foreignId('responsable_id')->constrained('responsables')->restrictOnDelete();
            $table->boolean('activo')->default(true);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materiales');
    }
};
