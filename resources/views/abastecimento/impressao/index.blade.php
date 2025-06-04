@extends('adminlte::page')

@section('title', 'Lote de Cartões')

@section('content_header')
    <h1 class="m-0 text-dark">Lote de Cartões</h1>
@stop

@section('content')
@include('layouts.notificacoes')

<div id="export-buttons"></div>
<br><br>

<!-- Card para a tabela de lote de cartões -->
<div class="card">
    <div class="card-header">
        <!-- Botão para importar novo lote -->
        <a href="{{route('abastecimento.impressao.importar')}}" class="btn btn-primary">Importar</a>
    </div>

    <div class="card-body">
        <!-- Filtros para a tabela -->
        <table id="loteCartoes" class="table table-striped">
            <div class="row">
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="filtro-lote">Lote: </label>
                        <select class="form-control" id="filtro-lote">
                            <option class="dropdown-item" value="">Todos</option>
                            @foreach ($lote_impressao as $lote)
                                <option value="{{ $lote->lote }}">{{ $lote->lote }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="filtro-cliente">Cliente: </label>
                        <select class="form-control" id="filtro-cliente">
                            <option class="dropdown-item" value="">Todos</option>
                            @foreach ($lote_impressao->unique('cliente') as $lote)
                                <option value="{{ $lote->cliente }}">{{ $lote->cliente }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="filtro-importacao">Importação: </label>
                        <input type="date" class="form-control" id="filtro-importacao">
                    </div>
                </div>

                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="filtro-impressao">Atualização: </label>
                        <input type="date" class="form-control" id="filtro-impressao">
                    </div>
                </div>

                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="filtro-status">Status: </label>
                        <select class="form-control" id="filtro-status">
                            <option class="dropdown-item" value="">Todos</option>
                            @foreach ($lote_impressao->unique('status_impressao') as $lote)
                                <option value="{{ $lote->status_impressao }}">{{ $lote->status_impressao }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Lote</th>
                    <th>Cliente</th>
                    <th>Data de importação</th>
                    <th>Data de modificação</th>
                    <th>Status</th>
                    <th>Qtd Cartões</th> 
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
                    $(this).remove();
                });
            });
        }, 5000);

    // Inicializar DataTable
    var table = $('#loteCartoes').DataTable({
        lengthMenu: [
            [10, 25, 50, 100, 200, -1],
            [10, 25, 50, 100, 200, 'Todos'],
        ],
        dom: 'lBfrtip',
        buttons: ['csv', 'excel', 'print', 'pdf'],
        className: 'btn btn-primary',
        order: [[0, 'desc']],
        "language": {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json',
        },
        processing: true,
        serverSide: true,
        ajax: '{{ route('abastecimento.impressao.index') }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'lote', name: 'lote' },
            { data: 'cliente', name: 'cliente' },
            {
                data: 'created_at',
                name: 'created_at',
                render: function(data, type, row) {
                    var dataObjeto = new Date(data);
                    return dataObjeto.toLocaleDateString('pt-BR') + ' ' + dataObjeto.toLocaleTimeString('pt-BR');
                }
            },
            {
                data: 'updated_at',
                name: 'updated_at',
                render: function(data, type, row) {
                    var dataObjeto = new Date(data);
                    return dataObjeto.toLocaleDateString('pt-BR') + ' ' + dataObjeto.toLocaleTimeString('pt-BR');
                }
            },
            { data: 'status_impressao', name: 'status_impressao' },
            { data: 'quantidade_cartoes', name: 'quantidade_cartoes' },  // NOVO
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        "pageLength": 10,
        initComplete: function() {
            $('.dataTables_filter').css('display', 'block');
            $('.dataTables_filter').css('margin-top', '10px');
            $('#export-buttons').append($('.dt-buttons'));
        }
    });

    // Filtros para a tabela
    $('#filtro-lote').on('change', function() {
        var lote = this.value;
        if (lote) {
            table.column(1).search('^' + lote + '$', true, false).draw();
        } else {
            table.column(1).search('').draw();
        }
    });

    $('#filtro-cliente').on('change', function() {
        var cliente = this.value;
        if (cliente) {
            table.column(2).search('^' + cliente + '$', true, false).draw();
        } else {
            table.column(2).search('').draw();
        }
    });

    $('#filtro-importacao').on('change', function() {
        table.column(3).search(this.value).draw();
    });

    $('#filtro-impressao').on('change', function() {
        table.column(4).search(this.value).draw();
    });

    $('#filtro-status').on('change', function() {
        var status = this.value;
        if (status) {
            table.column(5).search('^' + status + '$', true, false).draw();
        } else {
            table.column(5).search('').draw();
        }
    });
});
</script>
@endsection
