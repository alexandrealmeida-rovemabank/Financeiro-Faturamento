@extends('adminlte::page')

@section('title', 'Lote')

@section('content_header')
    <h1 class="m-0 text-dark">Lote</h1>




@stop
@section('content')
@include('layouts.notificacoes')
    <div class="card">
        <div class="card-header">
            <button data-bs-toggle="modal" data-bs-target="#modalCreate" class="btn btn-primary btn-add">Adicionar </button>


        </div>

        <div class="card-body">
            <table id="lote" class="table table-striped" class="display">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Lote</th>
                        <th>NF</th>
                        <th>Quantidade</th>
                        <th>Status</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                     @foreach($lote as $lotes)
                    <tr>
                        <td>{{ $lotes->id }}</td>
                        <td>{{ $lotes->lote }}</td>
                        <td>{{ $lotes->nf }}</td>
                        <td>{{ $lotes->quantidade }}</td>
                        <td>{{ $lotes->status }}</td>
                        <td style="vertical-align: middle">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary dropdown-toggle " data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a type="button" class="dropdown-item btn-editar"  data-bs-toggle="modal" data-bs-target="#modalEditar"
                                        data-id="{{ $lotes->id }}" data-lote="{{ $lotes->lote }}" data-nf="{{ $lotes->nf }}" data-quantidade="{{ $lotes->quantidade }}"
                                        data-status="{{ $lotes->status }}">Editar</a></li>
                                    <li><a href="{{ route('estoque.lote.excluir', $lotes->id) }}" class="dropdown-item">Excluir</a></li>
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
                    <h1 class="modal-title fs-5" id="contactModalLabel">Adicionar Lote</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card card-primary">
                        <div class="card-body">
                            <form action="{{route('estoque.lote.create')}}" method="POST" id="formCreate">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Lote</label>
                                            <input type="number" name="lote" id="lote" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>NF</label>
                                            <input type="text" name="nf" id="nf" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Quantidade</label>
                                            <input type="number" name="quantidade" id="quantidade" class="form-control">
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
                            <form action="{{route('estoque.lote.edit', ':id')}}" method="GET" id="formEditar">
                                @csrf
                                <input type="hidden" name="id" id="id">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                                <label>Status</label>
                                                <select class="form-control" name="status" id="edit-status"  value="edit-status">
                                                    <option><a class="dropdown-item">Ativo</a></option>
                                                    <option><a class="dropdown-item">Inativo</a></option>
                                                </select>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Lote</label>
                                            <input type="number" name="lote" id="edit-lote" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>NF</label>
                                            <input type="text" name="nf" id="edit-nf" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Quantidade</label>
                                            <input type="number" name="quantidade" id="edit-quantidade" class="form-control">
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

@stop


@section('js')


<script>
    $(document).ready(function () {
        $('#lote').DataTable({
                "language": {
                "search": "Pesquisar:",
            },
            drawCallback: function() {
            $('.dropdown-toggle').dropdown();
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
        var id = $(this).data('id');
        var lote = $(this).data('lote');
        var nf = $(this).data('nf');
        var quantidade = $(this).data('quantidade');
        var status = $(this).data('status');


        // Preencher os campos do modal
        $('#id').val(id);
        $('#edit-lote').val(lote);
        $('#edit-nf').val(nf);
        $('#edit-quantidade').val(quantidade);
        $('#edit-status').val(status);

        console.log('ID:', id);
    console.log('Lote:', lote);
    console.log('NF:', nf);
    console.log('Quantidade:', quantidade);
    console.log('Status:', status);

        // Atualizar a ação do formulário com o ID
        var formAction = "{{route('estoque.lote.edit', ':id') }}";
        formAction = formAction.replace(':id', id);
        $('#formEditar').attr('action', formAction);

        // Mostrar o modal
        $('#modalEditar').modal('show');
    });

    $('.dropdown-toggle').dropdown({
    autoClose: true
});
});
</script>
@endsection

