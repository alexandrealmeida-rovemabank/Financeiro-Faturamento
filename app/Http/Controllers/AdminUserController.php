<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view users', ['only' => ['index', 'edit']]);
        $this->middleware('permission:create users', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit users', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete users', ['only' => ['destroy']]);
        $this->middleware('permission:assign roles to users', ['only' => ['assignRoles']]);
        $this->middleware('permission:assign direct permissions to users', ['only' => ['assignDirectPermissions']]);
    }

    public function index()
    {
        $users = User::paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $roles = Role::whereIn('id', $request->roles ?? [])->get();
        $user->syncRoles($roles);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Log de criação do usuário
        activity('Usuário')
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties([
                'roles' => $roles->pluck('name')->toArray()
            ])
            ->log("Usuário '{$user->name}' criado");

        return redirect()->route('admin.users.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();
        $permissions = Permission::all();
        $userPermissions = $user->permissions->pluck('id')->toArray();
        return view('admin.users.edit', compact('user', 'roles', 'userRoles', 'permissions', 'userPermissions'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Log da atualização
        activity('Usuário')
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->log("Dados do usuário '{$user->name}' atualizados");

        return redirect()->route('admin.users.edit', $user->id)
                         ->with('success', 'Dados do usuário atualizados com sucesso!');
    }

    public function assignRoles(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.edit', $user->id)
                             ->with('error', 'Você não pode alterar seus próprios perfis de acesso.');
        }

        if ($user->hasRole('admin') && !auth()->user()->hasRole('admin')) {
            return redirect()->route('admin.users.edit', $user->id)
                             ->with('error', 'Você não tem permissão para alterar os perfis de um administrador.');
        }

        $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $roles = Role::whereIn('id', $request->roles ?? [])->get();

        $adminRole = Role::where('name', 'admin')->first();
        if (!auth()->user()->hasRole('admin') && $roles->contains($adminRole)) {
            return redirect()->route('admin.users.edit', $user->id)
                             ->with('error', 'Apenas administradores podem atribuir o perfil de admin.');
        }

        $user->syncRoles($roles);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Log de alteração de roles
        activity('Usuário')
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties(['roles' => $roles->pluck('name')->toArray()])
            ->log("Perfis (Roles) do usuário '{$user->name}' atualizados");

        return redirect()->route('admin.users.edit', $user->id)
                         ->with('success', 'Perfis (Roles) atribuídos com sucesso!');
    }

    public function assignDirectPermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $permissions = Permission::whereIn('id', $request->permissions ?? [])->get();

        $user->syncPermissions($permissions);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Log de alteração de permissões diretas
        activity('Usuário')
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties(['permissions' => $permissions->pluck('name')->toArray()])
            ->log("Permissões diretas do usuário '{$user->name}' atualizadas");

        return redirect()->route('admin.users.edit', $user->id)
                         ->with('success', 'Permissões diretas atribuídas com sucesso!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                             ->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        $user->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Log de exclusão
        activity('Usuário')
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->log("Usuário '{$user->name}' excluído");

        return redirect()->route('admin.users.index')
                         ->with('success', 'Usuário excluído com sucesso!');
    }
}
