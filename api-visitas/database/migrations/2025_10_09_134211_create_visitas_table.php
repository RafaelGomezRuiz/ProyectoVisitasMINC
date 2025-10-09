<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitante_id')->constrained('visitantes')->onDelete('cascade');
            $table->foreignId('area_id')->constrained('areas');
            $table->foreignId('reserva_id')->nullable()->constrained('reservas')->onDelete('set null');
            $table->date('fecha');
            $table->time('hora_entrada');
            $table->time('hora_salida')->nullable();
            $table->integer('edad');
            $table->string('responsable')->nullable()->comment('Nombre del responsable si es menor de edad');
            $table->string('no_carnet')->nullable();
            $table->string('motivo')->nullable();
            $table->enum('estado', ['activa', 'finalizada'])->default('activa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitas');
    }
};
