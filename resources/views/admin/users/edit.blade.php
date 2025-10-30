@extends('adminlte::page')

@section('title', 'Editar Usuário')

@section('content')
<div class="container-fluid">

    <h1 class="fw-bold text-primary mb-4">
        <i class="fas fa-user-cog me-2"></i>Gerenciar Acesso e Dados: {{ $user->name }}
    </h1>
    
    @include('partials.session-messages')

    {{-- Editar dados cadastrais --}}
    @can('edit users')
    <div class="card card-filter mb-4 shadow-lg border-0">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-edit me-2"></i>Editar Dados Cadastrais
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $user->name) }}" required 
                               placeholder="Nome do usuário">
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', $user->email) }}" required 
                               placeholder="Email do usuário">
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <hr>
                <p class="text-muted small">Deixe os campos de senha em branco para não alterá-la.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" placeholder="Nova senha">
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <input type="password" class="form-control" id="password_confirmation" 
                               name="password_confirmation" placeholder="Confirmar nova senha">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3 shadow-sm">
                    <i class="fas fa-save me-1"></i>Salvar Alterações
                </button>
            </form>
        </div>
    </div>
    @endcan

    {{-- Atribuir Roles --}}
    @can('assign roles to users')
    <div class="card card-filter mb-4 shadow-lg border-0">
        <div class="card-header bg-secondary text-white">
            <i class="fas fa-user-tag me-2"></i>Atribuir Perfis (Roles)
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.assignRoles', $user->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    @foreach ($roles as $role)
                        <div class="form-check form-check-inline mb-1">
                            <input class="form-check-input" type="checkbox" name="roles[]" 
                                   id="role-{{ $role->id }}" value="{{ $role->id }}"
                                   {{ in_array($role->id, $userRoles) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role-{{ $role->id }}">{{ $role->name }}</label>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary shadow-sm">
                    <i class="fas fa-save me-1"></i>Salvar Perfis
                </button>
            </form>
        </div>
    </div>
    @endcan

    {{-- Atribuir Permissões Diretas --}}
    @can('assign direct permissions to users')
    <div class="card card-filter mb-4 shadow-lg border-0">
        <div class="card-header bg-secondary text-white">
            <i class="fas fa-key me-2"></i>Atribuir Permissões Diretas (Não recomendado, use Roles)
        </div>
        <div class="card-body">
             <form action="{{ route('admin.users.assignDirectPermissions', $user->id) }}" method="POST">
                @csrf

                @php $permissionGroups = config('permissions'); @endphp

                @foreach($permissionGroups as $group)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h5 class="mb-0">{{ $group['label'] }}</h5>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input select-all-group" 
                                       id="select-all-{{ Str::slug($group['label']) }}">
                                <label class="form-check-label" for="select-all-{{ Str::slug($group['label']) }}">
                                    Selecionar Todos
                                </label>
                            </div>
                        </div>
                        <p class="text-muted small mb-2">{{ $group['description'] }}</p>

                        <div class="row permissions-group" data-group="{{ Str::slug($group['label']) }}">
                            @foreach($group['permissions'] as $permissionName)
                                @php
                                    $permission = \Spatie\Permission\Models\Permission::where('name', $permissionName)->first();
                                @endphp
                                @if($permission)
                                <div class="col-md-4 mb-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="permissions[]" 
                                               id="permission-{{ $permission->id }}" 
                                               value="{{ $permission->id }}"
                                               {{ in_array($permission->id, $userPermissions) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="permission-{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                        <hr class="mt-3 mb-3">
                    </div>
                @endforeach

                <button type="submit" class="btn btn-primary shadow-sm">
                    <i class="fas fa-save me-1"></i>Salvar Permissões Diretas
                </button>
            </form>
        </div>
    </div>
    @endcan

    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary shadow-sm mt-3">
        <i class="fas fa-arrow-left me-1"></i>Voltar para a Lista
    </a>
</div>
@stop

@push('js')
<script>
$(document).ready(function() {
    function syncSelectAll(groupSlug) {
        const groupCheckboxes = $(`.permissions-group[data-group="${groupSlug}"] input[type="checkbox"]`);
        const selectAll = $(`#select-all-${groupSlug}`);
        const allChecked = groupCheckboxes.length > 0 && groupCheckboxes.length === groupCheckboxes.filter(':checked').length;
        selectAll.prop('checked', allChecked);
    }

    $('.permissions-group').each(function() {
        const groupSlug = $(this).data('group');
        syncSelectAll(groupSlug);
    });

    $('.select-all-group').on('change', function() {
        const groupSlug = $(this).attr('id').replace('select-all-', '');
        const isChecked = $(this).is(':checked');
        $(`.permissions-group[data-group="${groupSlug}"] input[type="checkbox"]`).prop('checked', isChecked);
    });

    $('.permissions-group input[type="checkbox"]').on('change', function() {
        const groupSlug = $(this).closest('.permissions-group').data('group');
        syncSelectAll(groupSlug);
    });
});
</script>
@endpush
