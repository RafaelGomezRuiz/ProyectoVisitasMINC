<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssignDefaultRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asignar el rol de Administrador a todos los usuarios con rol 'admin'
        $adminRole = Role::where('nombre', 'Administrador')->first();
        $supervisorRole = Role::where('nombre', 'Supervisor')->first();

        if ($adminRole) {
            User::where('rol', 'admin')
                ->each(function ($user) use ($adminRole) {
                    $user->roles()->syncWithoutDetaching([$adminRole->id]);
                });
        }

        if ($supervisorRole) {
            User::where('rol', 'supervisor')
                ->each(function ($user) use ($supervisorRole) {
                    $user->roles()->syncWithoutDetaching([$supervisorRole->id]);
                });
        }
    }
}
