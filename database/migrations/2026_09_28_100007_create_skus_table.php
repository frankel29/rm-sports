<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modelo_id')->constrained('modelos')->restrictOnDelete();
            $table->foreignId('talla_id')->constrained('tallas')->restrictOnDelete();
            $table->string('codigo')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['modelo_id', 'talla_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skus');
    }
};
