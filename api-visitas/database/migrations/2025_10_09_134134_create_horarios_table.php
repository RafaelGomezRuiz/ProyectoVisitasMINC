<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('localidad_id')->constrained('localidades')->onDelete('cascade');
            // 1:Lunes, 2:Martes, ..., 7:Domingo
            $table->tinyInteger('dia_semana');
            $table->time('hora_apertura');
            $table->time('hora_cierre');
            $table->timestamps();

            $table->unique(['localidad_id', 'dia_semana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
