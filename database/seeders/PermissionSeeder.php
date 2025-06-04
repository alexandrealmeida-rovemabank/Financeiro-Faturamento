<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = ['credenciado', 'estoque', 'abastecimento', 'logistica', 'relatorio', 'sistema'];
        $actions = ['visualizar', 'criar', 'editar', 'excluir', 'importar', 'gerar'];

        $allPermissions = [];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissionName = "{$action} {$module}";
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
                $allPermissions[] = $permissionName;
            }
        }

        // Admin - todas as permissões
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions($allPermissions);

        // User - só visualizar e gerar
        $user = Role::firstOrCreate(['name' => 'User']);
        $userPermissions = [];
        foreach ($modules as $module) {
            $userPermissions[] = "visualizar {$module}";
            $userPermissions[] = "gerar {$module}";
        }
        $user->syncPermissions($userPermissions);

        // Manager - todas as permissões
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->syncPermissions($allPermissions);

        // Operador - todas as permissões, exceto excluir; 
        // e sem "editar sistema" e "criar sistema"
        $operador = Role::firstOrCreate(['name' => 'Operador']);
        $operadorPermissions = [];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                if ($action === 'excluir') {
                    continue; // Operador não pode excluir em nenhum módulo
                }

                // Operador não pode criar ou editar no módulo sistema
                if ($module === 'sistema' && in_array($action, ['criar', 'editar'])) {
                    continue;
                }

                $operadorPermissions[] = "{$action} {$module}";
            }
        }
        $operador->syncPermissions($operadorPermissions);
    }
}
