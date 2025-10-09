<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitantes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_doc', ['cedula', 'pasaporte', 'otro']);
            $table->string('documento_identidad')->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('correo')->nullable()->unique();
            $table->string('telefono')->nullable();
            $table->enum('sexo', ['Masculino', 'Femenino', 'Otro']);
            $table->foreignId('pais_origen_id')->constrained('paises');
            $table->string('provincia')->nullable();
            $table->foreignId('tipo_visitante_id')->constrained('tipo_visitantes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitantes');
    }
};
