<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kit_componentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kit_id')->constrained('kits')->cascadeOnDelete();
            $table->foreignId('modelo_id')->constrained('modelos')->restrictOnDelete();
            $table->decimal('cantidad', 8, 2)->default(1);
            $table->timestamps();

            $table->unique(['kit_id', 'modelo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kit_componentes');
    }
};
