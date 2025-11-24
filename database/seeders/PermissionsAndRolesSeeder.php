<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PermissionsAndRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpa o cache de permissões e roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- CRIAÇÃO DAS PERMISSÕES ---
        // Permissões para gerenciar o próprio sistema de acesso
        $permissions = [
            'view users', 
            'create users', 
            'edit users', 
            'delete users',

            'view roles', 
            'create roles', 
            'edit roles', 
            'delete roles',

            'view permissions', 
            'create permissions', 
            'edit permissions', 
            'delete permissions',

            'assign roles to users',
            'assign permissions to roles',
            'assign direct permissions to users',

            'view logs',

            'view cliente',
            'show cliente',
            'edit cliente',

            'view credenciado',
            'show credenciado',
            'edit credenciado',

            'view faturamento',
            'show faturamento',
            'edit faturamento',
            'delete faturamento',
            'create faturamento',
            'addPagamento faturamento',
            'addDesconto faturamento',

            'view cobranca',
            'show cobranca',
            'edit cobranca',
            'delete cobranca',

            'view reprocessamento',
            'run reprocessamento geral',
            'run reprocessamento personalizado',
            'run reprocessamento ultimas transações',

            'view parametros globais',
            'edit parametros globais',
            'reset parametros globais',
            'create parametros globais',
            'delete parametros globais',

            'view relatorios',
            'generate relatorios',

        ];


        // Crie as permissões
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // --- CRIAÇÃO DAS ROLES ---
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'financeiro', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'cobranca', 'guard_name' => 'web']);

        // --- ATRIBUIÇÃO DE PERMISSÕES À ROLE ADMIN ---
        // O Admin tem todas as permissões
        $adminRole->givePermissionTo(Permission::all());

        // --- CRIAÇÃO DO USUÁRIO ADMIN ---
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('123456'), // Use uma senha segura em produção!
            ]
        );

        // --- ATRIBUIÇÃO DA ROLE ADMIN AO USUÁRIO ---
        $adminUser->assignRole($adminRole);
    }
}
