<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User; // ✅ Importar User
use Illuminate\Support\Facades\Hash; // ✅ Importar Hash

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['Admin', 'User', 'Manager', 'Operador'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Pega ou cria o role Admin
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);

        // Cria usuário Admin se não existir
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('123456'),
            ]
        );

        // Atribui a role admin
        $admin->assignRole($adminRole);
    }
}
