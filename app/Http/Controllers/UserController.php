<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

require_once 'actions.php';

class UserController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(['auth', 'role:Admin']);
    // }

    public function __construct()
    {
        $this->middleware('permission:visualizar sistema')->only('index','data');
        $this->middleware('permission:criar sistema')->only(['create', 'store']);
        $this->middleware('permission:editar sistema')->only(['edit', 'update']);
        $this->middleware('permission:excluir sistema')->only(['destroy']);
    }
    /**
     * Exibe a view com a tabela de usuários.
     */
    public function index()
    {
        return view('users.index');
    }

    /**
     * Fornece os dados para o DataTables via AJAX.
     */
    public function data()
    {
        $users = User::with('roles')->select('users.*');

        return DataTables::of($users)
            ->addColumn('roles', function($user) {
                return $user->getRoleNames()->implode(', ');
            })
            ->addColumn('actions', function($user) {
                return button_user($user);
            })
            ->rawColumns(['actions']) // necessário para renderizar os botões HTML
            ->make(true);
    }

    /**
     * Mostra o formulário para criar um novo usuário.
     */
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Salva um novo usuário.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
            'role' => 'required|string'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Mostra o formulário para editar um usuário existente.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Atualiza os dados do usuário.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|string'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove o usuário do sistema.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuário excluído com sucesso!');
    }
}
