<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Añade la columna para la clave foránea
            $table->foreignId('localidad_id')
                ->nullable() // Permite que un usuario no esté asignado a ninguna localidad
                ->after('password') // Coloca la columna después de la contraseña
                ->constrained('localidades') // Crea la restricción de clave foránea a la tabla 'localidades'
                ->onDelete('set null'); // Si se borra la localidad, el campo en user se pone nulo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Elimina primero la restricción de la clave foránea
            $table->dropForeign(['localidad_id']);
            // Luego elimina la columna
            $table->dropColumn('localidad_id');
        });
    }
};
