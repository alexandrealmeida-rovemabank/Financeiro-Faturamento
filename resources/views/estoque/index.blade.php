@extends('adminlte::page')

@section('title', 'Estoque')

@section('content_header')
    <h1 class="m-0 text-dark">Estoque</h1>




@stop

@section('content')

    @include('layouts.notificacoes')
    <div id="export-buttons">

    </div>
    <br>
    <br>
    <div class="card">
        <div class="card-header">
            <button data-bs-toggle="modal" data-bs-target="#modalCreate" class="btn btn-primary btn-add">Adicionar </button>
            <a href="{{route('estoque.import')}}" class="btn btn-primary">Importar</a>



        </div>

        <div class="card-body">
            <table id="estoque" class="table table-striped" class="display">
                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="filtro-lote">Lote: </label>
                            <select class="form-control" id="filtro-lote">
                                <option class="dropdown-item" value="">Todos</option>
                                @foreach ($lote->unique('lote') as $estoq)
                                    <option value="{{ $estoq->lote }}"> <a class="dropdown-item">{{ $estoq->lote }}</a></option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="filtro-categoria">Categoria: </label>
                            <select class="form-control" id="filtro-categoria">
                                <option class="dropdown-item" value="">Todos</option>
                                @foreach ($categoriasDistintas as $estoq)
                                    <option value="{{ $estoq}}"> <a class="dropdown-item">{{ $estoq }}</a></option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="filtro-fabricante">Fabricante: </label>
                            <select class="form-control" id="filtro-fabricante">
                                <option class="dropdown-item" value="">Todos</option>
                                @foreach ($fabricantesDistintos as $estoq)
                                    <option value="{{ $estoq }}"> <a class="dropdown-item">{{ $estoq}}</a></option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="filtro-modelo">Modelo: </label>
                            <select class="form-control" id="filtro-modelo">
                                <option class="dropdown-item" value="">Todos</option>
                                @foreach ($modelosDistintos as $estoq)
                                    <option value="{{ $estoq }}"> <a class="dropdown-item">{{ $estoq}}</a></option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="filtro-status">Status: </label>
                            <select class="form-control" id="filtro-status">
                                <option class="dropdown-item" value="">Todos</option>
                                @foreach ($statusDistintos as $estoq)
                                    <option value="{{ $estoq}}"> <a class="dropdown-item">{{ $estoq}}</a></option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Lote</th>
                        <th>Categoria</th>
                        <th>Fabricante</th>
                        <th>Modelo</th>
                        <th>Numero de serie</th>
                        <th>Status</th>
                        <th>Ação</th>
                    </tr>
                </thead>



            </table>
        </div>
    </div>



    {{-- Modal Adicionar --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-labelledby="modalCreateLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="contactModalLabel">Adicionar Ativo</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card card-primary">
                        <div class="card-body">
                            <form action="{{route('estoque.create')}}" method="POST" id="formCreate">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Lote</label>
                                            <select class="form-control" name="id_lote" id="id_lote" >
                                                @foreach ($lote as $estoques)
                                                    <option><a class="dropdown-item">{{ $estoques->lote }}</a></option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Categoria</label>
                                            <select class="form-control" name="categoria" id="categoria" >
                                                    <option><a class="dropdown-item">TERMINAL</a></option>
                                                    <option><a class="dropdown-item">CHIP</a></option>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Fabricante</label>
                                            <input type="text" name="fabricante" id="fabricante" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Modelo</label>
                                            <input type="text" name="modelo" id="modelo" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Numero de série</label>
                                            <input type="text" name="numero_serie" id="numero_serie" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary" id="btnSalvar">Salvar</button>
                                <button type="button" class="modal-close waves-effect waves-green btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                            </form>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

   {{-- Modal Editar --}}
   <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="contactModalLabel">Editar lote</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
            </div>
            <div class="modal-body">
                <div class="card card-primary">
                    <div class="card-body">
                        <form action="{{route('estoque.edit', ':id')}}" method="GET" id="formEditar">
                                @csrf
                                <input type="hidden" name="id" id="id">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select class="form-control" name="status" id="edit-status" >
                                                    <option><a class="dropdown-item">Disponível</a></option>
                                                    <option><a class="dropdown-item">Indisponível</a></option>
                                                    <option><a class="dropdown-item">Perdida</a></option>
                                                    <option><a class="dropdown-item">Operação</a></option>
                                                    <option><a class="dropdown-item">Manutenção</a></option>
                                                    <option><a class="dropdown-item">Defeito</a></option>
                                                    <option><a class="dropdown-item">Roubada</a></option>
                                                    <option><a class="dropdown-item">Outros</a></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Lote</label>
                                             <select class="form-control" name="id_lote" id="edit-id_lote" >
                                                @foreach ($lote as $estoques)
                                                    <option><a class="dropdown-item">{{ $estoques->lote }}</a></option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Categoria</label>
                                            <select class="form-control" name="categoria" id="edit-categoria" >
                                                    <option><a class="dropdown-item">TERMINAL</a></option>
                                                    <option><a class="dropdown-item">CHIP</a></option>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Fabricante</label>
                                            <input type="text" name="fabricante" id="edit-fabricante" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Modelo</label>
                                            <input type="text" name="modelo" id="edit-modelo" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label>Numero de série</label>
                                            <input type="text" name="numero_serie" id="edit-numero_serie" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <label class="form-label">Observação</label>
                                        <textarea class="form-control" name="observacao" id="edit-observacao" rows="4"></textarea>
                                      </div>
                                </div>

                                <button type="submit" class="btn btn-primary" id="btnSalvar">Salvar</button>
                                <button type="button" class="modal-close waves-effect waves-green btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                            </form>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>


    {{-- Modal historico --}}
   <div class="modal fade" id="modalhistorico" tabindex="-1" aria-labelledby="modalhistoricoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" style="display: contents">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historicoModalLabel">Histórico do Terminal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="container">

                    <div class="row align-items-start">
                        <div class="col">
                            <b>Numero de Serie:</b> <a id="hist-numero_serie"></a>
                        </div>
                        <div class="col">
                            <b>Status Atual:</b> <a id="hist-status"></a>
                        </div>
                        <div class="col">
                            <b>Data de Cadastro:</b> <a id="hist-data_cadastro"></a>
                        </div>
                        <div class="col">
                            <b>Metodo de cadastro:</b> <a id="hist-metodo_cadastro"></a>
                        </div>
                    </div>
                </div>
                <h6>Registros</h6>
                <table class="table table-striped" id="historico">
                    <thead>
                        <tr>
                            <th>Credenciado</th>
                            <th>Produto</th>
                            <th>Ação</th>
                            <th>Data</th>
                            <th>Usuário</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
   </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
       var table = $('#estoque').DataTable({
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
            ajax: '{{ route('estoque.index') }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'lote.lote', name: 'lote.lote' },
                { data: 'categoria', name: 'categoria'},
                { data: 'fabricante', name: 'fabricante'},
                { data: 'modelo', name: 'modelo' },
                { data: 'numero_serie', name: 'numero_serie'},
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
        $('#filtro-lote').on('change', function() {
            var lote = this.value;
            if (lote) {
                // Pesquisa exata
                table.column(1).search('^' + lote + '$', true, false).draw();
            } else {
                // Limpar o filtro se o valor for vazio
                table.column(1).search('').draw();
            }
        });
            $('#filtro-categoria').on('change', function() {
            var categ = this.value;
            if (categ) {
                // Pesquisa exata
                table.column(2).search('^' + categ + '$', true, false).draw();
            } else {
                // Limpar o filtro se o valor for vazio
                table.column(2).search('').draw();
            }
        });
        $('#filtro-fabricante').on('change', function() {
            var fab = this.value;
            if (fab) {
                // Pesquisa exata
                table.column(3).search('^' + fab + '$', true, false).draw();
            } else {
                // Limpar o filtro se o valor for vazio
                table.column(3).search('').draw();
            }
        });
        $('#filtro-modelo').on('change', function() {
            var mod = this.value;
            if (mod) {
                // Pesquisa exata
                table.column(4).search('^' + mod + '$', true, false).draw();
            } else {
                // Limpar o filtro se o valor for vazio
                table.column(4).search('').draw();
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
});

</script>


<script>
$(document).ready(function () {
    $('.btn-add').on('click', function () {

        // Mostrar o modal
        $('#modalCreate').modal('show');
    });
});
</script>

<script>
    var myModal = new bootstrap.Modal(document.getElementById('modalEditar'));
</script>

<script>
$(document).ready(function () {
    $(document).on('click', '.btn-editar', function () {
        var id = $(this).attr('data-id');
        var id_lote = $(this).attr('data-id_lote');
        var categoria = $(this).attr('data-categoria');
        var fabricante = $(this).attr('data-fabricante');
        var modelo = $(this).attr('data-modelo');
        var numero_serie = $(this).attr('data-numero_serie');
        var status = $(this).attr('data-status');
        var observacao = $(this).attr('data-observacao');


        console.log('ID:', id);
        console.log('ID do Lote:', id_lote);
        console.log('ID da Categoria:', categoria);
        console.log('Fabricante:', fabricante);
        console.log('Modelo:', modelo);
        console.log('Número de Série:', numero_serie);
        console.log('Status:', status);
        console.log('Observação:', observacao);


        // Preencher os campos do modal
        $('#id').val(id);
        $('#edit-id_lote').val(id_lote);
        $('#edit-categoria').val(categoria);
        $('#edit-fabricante').val(fabricante);
        $('#edit-modelo').val(modelo);
        $('#edit-numero_serie').val(numero_serie);
        $('#edit-status').val(status);
        $('#edit-observacao').val(observacao);


        // Atualizar a ação do formulário com o ID
        var formAction = "{{route('estoque.edit', ':id')}}";
        formAction = formAction.replace(':id', id);
        $('#formEditar').attr('action', formAction);

        // Mostrar o modal
        $('#modalEditar').modal('show');
    });

});

$(document).ready(function () {
    $(document).on('click', '.btn-historico', function () {
        var id = $(this).attr('data-id');
        var numero_serie_his = $(this).attr('data-numero_serie');
        var status_hist = $(this).attr('data-status');
        var data_cadastro_hist = $(this).attr('data-data_cadastro');
        var metodo_cadastro_hist = $(this).attr('data-metodo_cadastro');
        console.log('ID:', data_cadastro_hist);

        // Divida a string da data em partes (data e hora)
        var partesDataHora = data_cadastro_hist.split(' ');

        // Divida a string da data em partes (ano, mês e dia)
        var partesData = partesDataHora[0].split('-');

        // Formate a data no formato 'dd/mm/aaaa hh:mm:ss'
        var dataFormatada = partesData[2] + '/' + partesData[1] + '/' + partesData[0] + ' ' + partesDataHora[1];


        // Preencher os campos do modal
        $('#hist-id').text(id);
        $('#hist-numero_serie').text(numero_serie_his);
        $('#hist-status').text(status_hist);
        $('#hist-data_cadastro').text(dataFormatada);
        $('#hist-metodo_cadastro').text(metodo_cadastro_hist);


        $.ajax({
    url: '/estoque/index/historico',
    type: 'GET',
    data: {
        id: id
    },
    success: function(data) {
        var tbody = $('#historico tbody');
        tbody.empty();

        data.sort(function(a, b) {
            return new Date(b.data) - new Date(a.data);
        });

        data.forEach(function(registro) {
            // Divida a string da data em partes (data e hora)
        // Divida a string da data em partes (data e hora)
        var partesDataHora = registro.data.split(' ');
        console.log();
        // Divida a string da data em partes (ano, mês e dia)
        var partesData = partesDataHora[0].split('-');

        // Formate a data no formato 'dd/mm/aaaa hh:mm:ss'
        var dataFormatada = partesData[2] + '/' + partesData[1] + '/' + partesData[0] + ' ' + partesDataHora[1];

            // Supondo que 'registro.credenciado' seja o ID do credenciado
            $.ajax({
                url: '/estoque/index/historico/credenciado', // Substitua pelo caminho para o endpoint que retorna o nome fantasia
                type: 'GET',
                data: {
                    id: registro.id_credenciado
                },
                success: function(credenciadoData) {
                    // Aqui 'credenciadoData' é a resposta do servidor contendo o nome fantasia do credenciado
                    var row = '<tr>' +
                        '<td>' + credenciadoData.nome_fantasia + '</td>' +
                        '<td>' + registro.produto + '</td>' +
                        '<td>' + registro.acao + '</td>' +
                        '<td>' + dataFormatada + '</td>' +
                        '<td>' + registro.usuario + '</td>' +
                        '</tr>';
                    tbody.append(row);
                }
            });
        });
    }
});

        // Mostrar o modal
        $('#modalhistorico').modal('show');
    });
    });


    </script>

@endsection
