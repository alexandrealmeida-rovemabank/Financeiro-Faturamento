@extends('adminlte::page')

@section('title', 'Criar Nova Permissão')


@section('content')
<div class="container">
    <h1>Criar Nova Permissão</h1>
    <form action="{{ route('admin.permissions.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nome da Permissão</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    <small class="form-text text-muted">Use o formato: "ação objeto" (ex: "view users", "create roles").</small>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Salvar Permissão</button>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary mt-3">Cancelar</a>
    </form>
</div>
@endsection