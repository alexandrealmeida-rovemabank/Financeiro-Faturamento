@extends('adminlte::page')

@section('title', 'Editar Role')

@section('content_header')
    <h1>Editar Role</h1>
@stop

@section('content')
    <form action="{{ route('roles.update', $role->id) }}" method="POST">
        @csrf @method('PUT')

        <div class="form-group">
            <label>Nome da Role</label>
            <input type="text" name="name" value="{{ $role->name }}" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@stop
