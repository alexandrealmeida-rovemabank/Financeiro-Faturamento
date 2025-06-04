@extends('adminlte::page')

@section('title', 'Usuários')

@section('content')
@include('layouts.notificacoes')
<h1>Usuários</h1>

<div id="export-buttons"></div>
<br><br>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div id="export-buttons"></div>

    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Novo Usuário
    </a>
</div>

<div class="card">
    <div class="card-header">
        {{-- Se quiser adicionar filtros depois, segue modelo --}}
        {{-- 
        <div class="row">
            <div class="col-sm-3">
                <label for="filtro-role">Função:</label>
                <select id="filtro-role" class="form-control filtro" data-coluna="roles">
                    <option value="">Todos</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
        </div> 
        --}}
    </div>
    <div class="card-body">
        <table id="tabelaUsuarios" class="table table-striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Ações</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {

    var table = $('#tabelaUsuarios').DataTable({
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, 'Todos'],
        ],
        dom: 'lBfrtip',
        buttons: ['csv', 'excel'],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json',
        },
        processing: true,
        serverSide: true,
        ajax: '{{ route('users.data') }}',
        columns: [
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'roles', name: 'roles' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        initComplete: function() {
            $('#export-buttons').append($('.dt-buttons'));
        }
    });

    $('.filtro').on('change', function() {
        table.draw();
    });

});
</script>
@endsection
