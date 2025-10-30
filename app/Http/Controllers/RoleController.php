<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Models\Activity;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view roles', ['only' => ['index', 'show']]);
        $this->middleware('permission:create roles', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit roles', ['only' => ['edit', 'update', 'assignPermissions']]);
        $this->middleware('permission:delete roles', ['only' => ['destroy']]);
    }

    public function index()
    {
        $roles = Role::paginate(10);
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
        ]);

        $role = Role::create(['name' => $request->name]);

        // Limpa o cache após a criação
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
                         ->with('success', 'Role criada com sucesso!');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        $permissionGroups = config('permissions');

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions','permissionGroups'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
        ]);

        $role->update(['name' => $request->name]);

        // Limpa o cache após a atualização do nome
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
                         ->with('success', 'Role atualizada com sucesso!');
    }

    public function assignPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Permissões atuais antes da sincronização
        $currentPermissions = $role->permissions->pluck('name')->toArray();

        // Novas permissões selecionadas
        $newPermissions = Permission::whereIn('id', $request->permissions ?? [])->get();
        $newPermissionNames = $newPermissions->pluck('name')->toArray();

        // Sincroniza permissões
        $role->syncPermissions($newPermissions);

        // Limpa o cache após atribuir permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Determina permissões adicionadas e removidas
        $added = array_diff($newPermissionNames, $currentPermissions);
        $removed = array_diff($currentPermissions, $newPermissionNames);

        // Log detalhado
        activity()
            ->performedOn($role)
            ->causedBy(auth()->user())
            ->withProperties([
                'added_permissions' => $added,
                'removed_permissions' => $removed
            ])
            ->log('Permissões da role atualizadas');

        return redirect()->route('admin.roles.edit', $role->id)
                        ->with('success', 'Permissões atribuídas à role com sucesso!');
    }


    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                             ->with('error', 'Não é possível excluir a role, existem usuários atribuídos a ela!');
        }

        $role->delete();

        // Limpa o cache após a exclusão
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
                         ->with('success', 'Role excluída com sucesso!');
    }
}
