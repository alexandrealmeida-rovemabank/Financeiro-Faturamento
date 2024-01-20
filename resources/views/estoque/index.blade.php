@extends('adminlte::page')

@section('title', 'Estoque')

@section('content_header')
    <h1 class="m-0 text-dark">Estoque</h1>




@stop

@section('content')

    @include('layouts.notificacoes')

    <div class="card">
        <div class="card-header">
            <button data-bs-toggle="modal" data-bs-target="#modalCreate" class="btn btn-primary btn-add">Adicionar </button>
            <a href="{{route('estoque.import')}}" class="btn btn-primary">Importar</a>



        </div>

        <div class="card-body">
            <table id="estoque" class="table table-striped" class="display">
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
                <tbody>
                    @foreach($estoque as $estoques)

                    <tr>
                        <td>{{ $estoques->id }}</td>
                        <td>{{ $estoques->lote->lote }}</td>
                        <td>{{ $estoques->categoria }}</td>
                        <td>{{ $estoques->fabricante }}</td>
                        <td>{{ $estoques->modelo }}</td>
                        <td>{{ $estoques->numero_serie }}</td>
                        <td>{{ $estoques->status }}</td>
                        <td style="vertical-align: middle">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary dropdown-toggle " data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="{{route('estoque.historico', $estoques->id)}}" class="dropdown-item">Histórico</a></li>
                                    <li><a type="button" class="dropdown-item btn-editar"  data-bs-toggle="modal" data-bs-target="#modalEditar"
                                        data-id="{{ $estoques->id }}" data-categoria="{{ $estoques->categoria }}" data-id_lote="{{ $estoques->lote->lote }}" data-fabricante="{{ $estoques->fabricante }}" data-modelo="{{ $estoques->modelo }}"
                                        data-numero_serie="{{ $estoques->numero_serie }}" data-status="{{ $estoques->status }}" data-observacao="{{ $estoques->observacao }}" >Editar</a></li>
                                    <li><a href="{{route('estoque.excluir', $estoques->id)}}" class="dropdown-item">Excluir</a></li>
                                </ul>
                              </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

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
@stop

@section('js')


<script>
    $(document).ready(function () {
        $('#estoque').DataTable({
                "language": {
                "search": "Pesquisar:",
            },});
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
    $('.btn-editar').on('click', function () {
        var id = $(this).data('id');
        var id_lote = $(this).data('id_lote');
        var categoria = $(this).data('categoria');
        var fabricante = $(this).data('fabricante');
        var modelo = $(this).data('modelo');
        var numero_serie = $(this).data('numero_serie');
        var status = $(this).data('status');
        var observacao = $(this).data('observacao');

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
</script>

@endsection


