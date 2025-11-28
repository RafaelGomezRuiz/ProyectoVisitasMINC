<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Permite documentos nulos para visitantes menores sin cédula ni pasaporte (tipo_doc='otro').
     */
    public function up(): void
    {
        Schema::table('visitantes', function (Blueprint $table) {
            // Cambiar documento_identidad a nullable y remover unique constraint
            $table->string('documento_identidad')->nullable()->change();
            // También necesitamos un índice único condicional que ignore los nulls
            // Pero Laravel no soporta esto directamente, así que usamos rawStatement
        });

        // En algunas bases de datos podríamos usar índice único condicional:
        // DB::statement('ALTER TABLE visitantes ADD UNIQUE INDEX unique_documento_identity (documento_identidad) WHERE documento_identidad IS NOT NULL');
        // Pero para compatibilidad, dejamos que la aplicación valide.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitantes', function (Blueprint $table) {
            $table->string('documento_identidad')->change();
        });
    }
};
