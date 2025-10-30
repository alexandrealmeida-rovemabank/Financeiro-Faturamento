@extends('adminlte::page')

@section('title', 'Editar Perfil de Acesso')

@section('content_header')
    <h1 class="fw-bold text-primary">
        <i class="fas fa-user-shield me-2"></i>Editar Perfil de Acesso
    </h1>
@stop

@section('content')
<div class="container-fluid">

    <h4 class="mb-4 fw-semibold">Editar Perfil: <span class="text-secondary">{{ $role->name }}</span></h4>

    @include('partials.session-messages')

    {{-- Card para renomear o perfil --}}
    @can('edit roles')
    <div class="card card-filter mb-4 shadow-lg border-0">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-edit me-2"></i>Renomear Perfil
        </div>
        <div class="card-body">
            <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="input-group mb-2">
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           name="name" value="{{ old('name', $role->name) }}" required>
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="fas fa-save me-1"></i>Atualizar Nome
                    </button>
                </div>
                @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </form>
        </div>
    </div>
    @endcan

    {{-- Card para atribuir permissões --}}
    @can('assign permissions to roles')
    <div class="card card-filter mb-4 shadow-lg border-0">
        <div class="card-header bg-secondary text-white">
            <i class="fas fa-key me-2"></i>Atribuir Permissões
        </div>
        <div class="card-body">

            <form action="{{ route('admin.roles.assignPermissions', $role->id) }}" method="POST">
                @csrf

                @php
                    $permissionGroups = config('permissions');
                @endphp

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
                                <div class="col-md-4">
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="permissions[]" 
                                               id="permission-{{ $permission->id }}" 
                                               value="{{ $permission->id }}"
                                               {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
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
                    <i class="fas fa-save me-1"></i>Salvar Permissões
                </button>
            </form>
        </div>
    </div>
    @endcan

    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary shadow-sm mt-2">
        <i class="fas fa-arrow-left me-1"></i>Voltar para a Lista
    </a>
</div>
@stop

@push('css')
<style>
    /* Cards com sombra e borda arredondada */
    .card {
        border-radius: 12px;
        overflow: hidden;
    }

    .card-header {
        border-bottom: none;
        font-weight: 600;
    }

    /* Inputs e botões */
    .input-group .form-control {
        border-radius: 6px 0 0 6px;
    }

    .btn i {
        margin-right: 4px;
    }

    /* Permissões */
    .permissions-group .form-check-label {
        font-size: 0.9rem;
    }

    .permissions-group .form-check-input {
        margin-top: 0.3rem;
    }

    hr {
        border-color: #e0e0e0;
    }
</style>
@endpush

@push('js')
<script>
$(document).ready(function() {
    // Função "Selecionar Todos" por grupo
    function syncSelectAll(groupSlug) {
        const groupCheckboxes = $(`.permissions-group[data-group="${groupSlug}"] input[type="checkbox"]`);
        const selectAll = $(`#select-all-${groupSlug}`);
        const allChecked = groupCheckboxes.length > 0 && groupCheckboxes.length === groupCheckboxes.filter(':checked').length;
        selectAll.prop('checked', allChecked);
    }

    // Inicializa os selects all com base nos checkboxes marcados
    $('.permissions-group').each(function() {
        const groupSlug = $(this).data('group');
        syncSelectAll(groupSlug);
    });

    // Checkbox "Selecionar Todos"
    $('.select-all-group').on('change', function() {
        const groupSlug = $(this).attr('id').replace('select-all-', '');
        const isChecked = $(this).is(':checked');
        $(`.permissions-group[data-group="${groupSlug}"] input[type="checkbox"]`).prop('checked', isChecked);
    });

    // Atualiza o checkbox "Selecionar Todos" ao marcar/desmarcar individualmente
    $('.permissions-group input[type="checkbox"]').on('change', function() {
        const groupSlug = $(this).closest('.permissions-group').data('group');
        syncSelectAll(groupSlug);
    });
});
</script>
@endpush
