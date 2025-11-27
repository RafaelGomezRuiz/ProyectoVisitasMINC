<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso completo a todo el sistema'],
            ['nombre' => 'Supervisor', 'descripcion' => 'Supervisar localidades y gestionar datos'],
            ['nombre' => 'AgenteDeVisitas', 'descripcion' => 'Registrar visitas y reservas de su localidad'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['nombre' => $role['nombre']],
                ['descripcion' => $role['descripcion']]
            );
        }
    }
}
