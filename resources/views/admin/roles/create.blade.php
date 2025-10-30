@extends('adminlte::page')

@section('title', 'Criar Perfil de Acesso')

@section('content')
<div class="container-fluid">

    <h1 class="fw-bold text-primary mb-4">
        <i class="fas fa-user-shield me-2"></i>Criar Novo Perfil de Acesso
    </h1>

    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf

        {{-- Card para o nome do perfil --}}
        <div class="card card-filter mb-4 shadow-lg border-0">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-edit me-2"></i>Nome do Perfil
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" required placeholder="Digite o nome do perfil">
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Card para atribuir permissões --}}
        @can('assign permissions to roles')
        <div class="card card-filter mb-4 shadow-lg border-0">
            <div class="card-header bg-secondary text-white">
                <i class="fas fa-key me-2"></i>Atribuir Permissões
            </div>
            <div class="card-body">

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
                                               value="{{ $permission->id }}">
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

            </div>
        </div>
        @endcan

        <button type="submit" class="btn btn-primary shadow-sm mt-2">
            <i class="fas fa-save me-1"></i>Salvar Perfil
        </button>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary shadow-sm mt-2">
            <i class="fas fa-arrow-left me-1"></i>Cancelar
        </a>
    </form>
</div>
@stop

@push('css')
<style>
    .card {
        border-radius: 12px;
        overflow: hidden;
    }

    .card-header {
        border-bottom: none;
        font-weight: 600;
    }

    .btn i {
        margin-right: 4px;
    }

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
    // Checkbox "Selecionar Todos" para cada grupo
    $('.select-all-group').on('change', function() {
        const groupId = $(this).attr('id').replace('select-all-', '');
        const isChecked = $(this).is(':checked');
        $(`.permissions-group[data-group="${groupId}"] input[type="checkbox"]`).prop('checked', isChecked);
    });
});
</script>
@endpush
