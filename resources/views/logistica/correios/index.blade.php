@extends('adminlte::page')

@section('title', 'Correios')

@section('content_header')
    <h1 class="m-0 text-dark">Logistica Reversa (Correios)</h1>

@stop

@section('content')

    @include('layouts.notificacoes')
    <div id="export-buttons">

    </div>
    <br>
    <br>
    <div class="card">
        <div class="card-header">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                    @if(auth()->user()->can('criar logistica'))
                        <li><a href="{{route('logistica.correios.create')}}" class="dropdown-item">Solicitar</a></li>
                    @endif
                    @if(auth()->user()->can('criar logistica'))
                        <li><a href="{{route('logistica.correios.consultarPedido')}}" class="dropdown-item">Atualizar Solicitações</a></li>
                    @endif

                    @if(auth()->user()->can('visualizar logistica'))
                        <li><a href="{{route('logistica.correios.rastreio')}}" class="dropdown-item">Rastrear Objeto</a></li>
                    @endif

                </ul>
            </div>
        </div>

         <div class="card-body" style="overflow-x:auto;">
            <table id="correios" class="table table-striped" class="display" style="text-align: center;">
                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="filtro-contrato">Contrato: </label>
                            <select class="form-control" id="filtro-contrato">
                                <option class="dropdown-item" value="">Todos</option>
                                @foreach ($contrato as $logistica)
                                    <option value="{{ $logistica }}"> <a class="dropdown-item">{{  $logistica }}</a></option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="filtro-solicitacao">Tipo de Solicitação: </label>
                            <select class="form-control" id="filtro-solicitacao">
                                <option class="dropdown-item" value="">Todos</option>
                                @foreach ($solicitacao as $logistica)
                                    <option value="{{ $logistica}}"> <a class="dropdown-item">{{ $logistica }}</a></option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="filtro-status">Status da Logistica: </label>
                            <select class="form-control" id="filtro-status">
                                <option class="dropdown-item" value="">Todos</option>
                                @foreach ($status as $logistica)
                                    <option value="{{ $logistica }}"> <a class="dropdown-item">{{ $logistica}}</a></option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="filtro-produto">Produto: </label>
                            <select class="form-control" id="filtro-produto">
                                <option class="dropdown-item" value="">Todos</option>
                                @foreach ($produto as $logistica)
                                    <option value="{{ $logistica }}"> <a class="dropdown-item">{{ $logistica}}</a></option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Contrato</th>
                        <th>Numero Catão</th>
                        <th>Tipo de Solicitação</th>
                        <th>Remetente</th>
                        <th>Destinatario</th>
                        <th>Numero da Coleta</th>
                        <th>Código de Rastreio</th>
                        <th>Status da Logística</th>
                        <th>Produto</th>
                        <th>Data de Solictação</th>
                        <th>Data de Atualização</th>
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
        // Remover alertas após 5 segundos
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
    });
    $(document).ready(function() {
       var table = $('#correios').DataTable({
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
             ajax: '{{ route('logistica.correios.index') }}',
             columns: [
                 { data: 'id', name: 'id' },
                 { data: 'contrato', name: 'contrato' },
                 { data: 'num_cartao', name: 'num_cartao'},
                 {
                   data: 'tipo_coleta',
                   name: 'tipo_coleta',
                   render: function (data, type, full, meta) {
                    if (data === 'CA') {
                        return 'COLETA DOMICILIAR';
                    } else if (data === 'A') {
                        return 'AUTORIZAÇÃO DE POSTAGEM';
                    } else {
                        return data; // Se o valor não for 'CA' ou 'A', retorna o valor original
                    }
                   }
                 },
                 { data: 'nome_fantasia_remetente', name: 'nome_fantasia_remetente'},
                 { data: 'nome_fantasia_destinatario', name: 'nome_fantasia_destinatario' },
                 { data: 'num_coleta', name: 'num_coleta'},
                 { data: 'num_etiqueta', name: 'num_etiqueta'},
                 { data: 'desc_status_objeto', name: 'desc_status_objeto'},
                 { data: 'produto', name: 'produto' },
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
                 { data: 'action', name: 'action', orderable: false, searchable: false },

             ],
            "pageLength": 10,
        initComplete: function() {
            $('.dataTables_filter').css('display', 'block');
            $('.dataTables_filter').css('margin-top', '10px');
            $('#export-buttons').append($('.dt-buttons'));
        }
    });
         $('#filtro-contrato').on('change', function() {
             var contrato = this.value;
             if (contrato) {
                 // Pesquisa exata
                 table.column(1).search('^' + contrato + '$', true, false).draw();
             } else {
                 // Limpar o filtro se o valor for vazio
                 table.column(1).search('').draw();
             }
         });
             $('#filtro-solicitacao').on('change', function() {
             var solicitacao = this.value;
             if (solicitacao) {
                 // Pesquisa exata
                 table.column(3).search('^' + solicitacao + '$', true, false).draw();
             } else {
                 // Limpar o filtro se o valor for vazio
                 table.column(3).search('').draw();
             }
         });
         $('#filtro-status').on('change', function() {
             var status = this.value;
             if (status) {
                 // Pesquisa exata
                 table.column(8).search('^' + status + '$', true, false).draw();
             } else {
                 // Limpar o filtro se o valor for vazio
                 table.column(8).search('').draw();
             }
         });
         $('#filtro-produto').on('change', function() {
             var produto = this.value;
             if (produto) {
                 // Pesquisa exata
                 table.column(9).search('^' + produto + '$', true, false).draw();
             } else {
                 // Limpar o filtro se o valor for vazio
                 table.column(9).search('').draw();
             }
         });

});

</script>

@endsection
