@extends('adminlte::page')

@section('title', 'Credenciados')

@section('content_header')
    <h1 class="m-0 text-dark">Credenciados</h1>


@stop
@section('content')
@include('layouts.notificacoes')
    <div class="card">
        <div class="card-header">
            <a href="{{route('credenciado.create')}}" class="btn btn-primary">Adicionar</a>
        </div>

        <div class="card-body">
            <table id="credenciados" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome Fantasia</th>
                        <th>CNPJ</th>
                        <th>Produto</th>
                        <th>Status</th>
                        <th>Ação</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop


@section('js')



<script>
$(document).ready(function() {
    $('#credenciados').DataTable({
        "language": {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json',
        },
        processing: true,
        serverSide: true,
        ajax: '{{ route('credenciado.index') }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'nome_fantasia', name: 'nome_fantasia'},
            {
                data: 'cnpj',
                name: 'cnpj',
                render: function(data, type, row) {
                    // Formatar o CNPJ para adicionar a pontuação
                    return data.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5");
                }
            },
            {
                data: 'produto',
                name: 'produto',
                render: function(data, type, row) {
                    // Decodificar a string JSON e juntar os produtos com ' - '
                    var decodedHtml = $('<div>').html(data).text();
                    return JSON.parse(decodedHtml).join(' - ');
                }

            },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        "pageLength": 10,
        "lengthMenu": [10, 25, 50, 100, 200],
    });
});


</script>
@endsection

