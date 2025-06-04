@extends('adminlte::page')

@section('title', 'Editar Usuário')

@section('content_header')
    <h1>Editar Usuário</h1>
@stop

@section('content')
    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Nome</label>
            <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
        </div>

        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Nova Senha (opcional)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="form-group">
            <label>Confirmar Nova Senha</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>

        <div class="form-group">
            <label>Função</label>
            <select name="role" class="form-control" required>
                @foreach($roles as $role)
                <option value="{{ $role->name }}" 
                    {{ ($user->roles->first() && $user->roles->first()->name == $role->name) ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
                @endforeach
            </select>
        </div>

        @if(auth()->user()->can('editar sistema'))
            <button type="submit" class="btn btn-primary">Atualizar</button>
        @endif 
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@stop
