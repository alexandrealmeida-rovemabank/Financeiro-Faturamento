@extends('adminlte::page')

@section('title', 'Usuários')

@section('content_header')
    <h1 class="fw-bold text-primary">
        <i class="fas fa-users-cog me-2"></i>Gerenciar Usuários
    </h1>
@stop

@section('content')
<div class="container-fluid">

    @include('layouts.notificacoes')
    @include('partials.session-messages')

    @can('create users')
        <div class="mb-3">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-user-plus me-1"></i>Novo Usuário
            </a>
        </div>
    @endcan

    <div class="card card-filter mb-4 shadow-lg border-0">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="fas fa-list me-2"></i>
            <h5 class="mb-0">Usuários Cadastrados</h5>
        </div>

        <div class="card-body p-0">
            <table class="table table-hover table-bordered mb-0 align-middle text-sm">
                <thead class="table-light">
                    <tr class="text-secondary">
                        <th style="width: 5%">ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Perfis (Roles)</th>
                        <th class="text-center" style="width: 25%">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="fw-semibold text-secondary">{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    <span class="badge bg-primary shadow-sm">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="text-center">
                                @can('edit users')
                                    <a href="{{ route('admin.users.edit', $user->id) }}" 
                                        class="btn btn-sm btn-warning shadow-sm mb-1">
                                        <i class="fas fa-user-cog me-1"></i>Gerenciar
                                    </a>
                                @endcan
                                @can('delete users')
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger shadow-sm mb-1" 
                                            onclick="return confirm('Tem certeza que deseja excluir este usuário?');">
                                            <i class="fas fa-trash-alt me-1"></i>Excluir
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted p-3">
                                <i class="fas fa-info-circle me-1"></i>Nenhum usuário encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-end">
        {{ $users->links() }}
    </div>
</div>
@stop

@push('css')
<style>
    /* Card */
    .card {
        border-radius: 12px;
        overflow: hidden;
    }

    .card-header {
        border-bottom: none;
        font-weight: 600;
    }

    /* Botões */
    .btn i {
        margin-right: 4px;
    }

    /* Tabela */
    table th, table td {
        vertical-align: middle !important;
    }

    table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-size: 0.85rem;
    }
</style>
@endpush
