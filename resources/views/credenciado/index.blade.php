@extends('adminlte::page')

@section('title', 'Credenciados')

@section('content_header')
    <h1 class="m-0 text-dark">Credenciados</h1>


@stop
@section('content')
@include('layouts.notificacoes')
<div id="export-buttons">

</div>
<br>
<br>
    <div class="card">
        <div class="card-header">
            <a href="{{route('credenciado.create')}}" class="btn btn-success">Adicionar</a>

        </div>

        <div class="card-body">
            <table id="credenciados" class="table table-striped">
                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="filtro-status">Status: </label>
                            <select class="form-control" id="filtro-status">
                                <option class="dropdown-item" value="">Todos</option>
                                <option class="dropdown-item" value="Inativo">Inativo</option>
                                <option class="dropdown-item" value="Ativo">Ativo</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="filtro-cidade">Cidade: </label>
                            <select class="form-control" id="filtro-cidade">
                                <option class="dropdown-item" value="">Todos</option>
                                @foreach ($crend->unique('cidade') as $crendenciado)
                                    <option value="{{ $crendenciado->cidade }}"> <a class="dropdown-item">{{ $crendenciado->cidade }}</a></option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="filtro-estado">Estado: </label>
                            <select class="form-control" id="filtro-estado">
                                <option class="dropdown-item" value="">Todos</option>
                                @foreach ($crend->unique('estado') as $crendenciado)
                                    <option value="{{ $crendenciado->estado }}"> <a class="dropdown-item">{{ $crendenciado->estado }}</a></option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>


                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome Fantasia</th>
                        <th>CNPJ</th>
                        <th>Produto</th>
                        <th>Cidade</th>
                        <th>Estado</th>
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
    setTimeout(function() {
            $('#alert-success, #alert-error, #alert-warning').each(function() {
                $(this).animate({
                    marginRight: '-=1000',
                    opacity: 0
                }, 'slow', function() {
                    $(this).remove(); // Remove o elemento após a animação
                });
            });
        }, 5000);
    var table = $('#credenciados').DataTable({

        lengthMenu: [
            [10, 25, 50, 100, 200, -1],
            [10, 25, 50, 100, 200, 'Todos'],
        ],
        dom: 'lBfrtip',
        buttons: ['csv', 'excel', 'print', 'pdf'],
        className: 'btn btn-success',
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
            { data: 'cidade', name: 'cidade' },
            { data: 'estado', name: 'estado' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        "pageLength": 10,
        initComplete: function() {
            $('.dataTables_filter').css('display', 'block');
            $('.dataTables_filter').css('margin-top', '10px');
            $('#export-buttons').append($('.dt-buttons'));
        }
    });
    $('#filtro-status').on('change', function() {
    var status = this.value;
    if (status) {
        // Pesquisa exata
        table.column(6).search('^' + status + '$', true, false).draw();
    } else {
        // Limpar o filtro se o valor for vazio
        table.column(6).search('').draw();
    }
});
    $('#filtro-estado').on('change', function() {
    var prod = this.value;
    if (prod) {
        // Pesquisa exata
        table.column(5).search('^' + prod + '$', true, false).draw();
    } else {
        // Limpar o filtro se o valor for vazio
        table.column(5).search('').draw();
    }
});
$('#filtro-cidade').on('change', function() {
    var prod = this.value;
    if (prod) {
        // Pesquisa exata
        table.column(4).search('^' + prod + '$', true, false).draw();
    } else {
        // Limpar o filtro se o valor for vazio
        table.column(4).search('').draw();
    }
});
});



</script>
@endsection

