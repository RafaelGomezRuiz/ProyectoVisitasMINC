<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitante_id')->constrained('visitantes')->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora');
            $table->string('motivo')->nullable();
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada', 'utilizada'])->default('pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
