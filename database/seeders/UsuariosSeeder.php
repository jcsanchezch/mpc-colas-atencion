<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos
        $permisosVentanilla  = ['tickets.all'];
        $permisosSupervisor  = ['tickets.all', 'trabajadores.all', 'dias.all', 'ventanillas.all', 'tramites.all'];
        $permisosAdmin       = ['usuarios.all'];
        $todosLosPermisos    = array_merge($permisosVentanilla, $permisosSupervisor, $permisosAdmin);

        foreach ($todosLosPermisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // Roles
        $rolVentanilla = Role::firstOrCreate(['name' => 'Ventanilla']);
        $rolVentanilla->syncPermissions($permisosVentanilla);

        $rolSupervisor = Role::firstOrCreate(['name' => 'Supervisor']);
        $rolSupervisor->syncPermissions($permisosSupervisor);

        $rolAdmin = Role::firstOrCreate(['name' => 'Admin']);
        $rolAdmin->syncPermissions($todosLosPermisos);

        // Usuarios de prueba
        User::factory()->create([
            'name'     => 'Admin',
            'email'    => 'admin@mail.com',
            'password' => 'admin@mail.com',
        ])->assignRole('Admin');

        User::factory()->create([
            'name'     => 'Supervisor',
            'email'    => 'supervisor@mail.com',
            'password' => 'supervisor@mail.com',
        ])->assignRole('Supervisor');

        User::factory()->create([
            'name'     => 'Ventanilla',
            'email'    => 'ventanilla@mail.com',
            'password' => 'ventanilla@mail.com',
        ])->assignRole('Ventanilla');
    }
}
