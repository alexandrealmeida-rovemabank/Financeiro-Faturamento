@extends('adminlte::page')

@section('title', 'Juma')

@section('content_header')
    <h1 class="m-0 text-dark">Logistica Juma</h1>

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
                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="{{route('logistica.correios.create')}}" class="dropdown-item">Adicionar</a></li>

                </ul>
            </div>
        </div>

         <div class="card-body" style="overflow-x:auto;">
            <table id="juma" class="table table-striped" class="display" style="text-align: center;">
                {{-- <div class="row">
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
                    </div> --}}

                </div>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nº Pedido</th>
                        <th>Nº OS</th>
                        <th>Quantidade Destino</th>
                        <th>Valor</th>
                        <th>Produto</th>
                        <th>Retorno</th>
                        <th>Código de Rastreio</th>
                        <th>Observação</th>
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
       var table = $('#juma').DataTable({
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
             ajax: '{{ route('logistica.juma.index') }}',
             columns: [
                 { data: 'id', name: 'id' },
                 { data: 'num_pedido', name: 'num_pedido' },
                 { data: 'num_os', name: 'num_os'},
                 { data: 'qtd_destino', name: 'qtd_destino'},
                 { data: 'valor', name: 'valor'},
                 { data: 'produto', name: 'produto' },
                 { data: 'retorno', name: 'retorno'},
                 { data: 'observacao', name: 'observacao'},
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


});

</script>

@endsection
