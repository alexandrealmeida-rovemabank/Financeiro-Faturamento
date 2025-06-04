@extends('adminlte::page')

@section('title', 'Criar Usuário')

@section('content_header')
    <h1>Criar Usuário</h1>
@stop

@section('content')
    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Nome</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Senha</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Confirmar Senha</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Função</label>
            <select name="role" class="form-control" required>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" 
                        {{ (isset($user) && $user->roles->first()->name == $role->name) ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>


        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@stop
