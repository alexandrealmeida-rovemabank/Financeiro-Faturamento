@extends('adminlte::page')

@section('title', 'Criar Role')

@section('content_header')
    <h1>Criar Role</h1>
@stop

@section('content')
    <form action="{{ route('roles.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Nome da Role</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@stop
