@extends('adminlte::page')

@section('title', 'Credenciados')

@section('content_header')
    <h1 class="m-0 text-dark">Editar Lote</h1>
@stop

@section('content')
@include('layouts.notificacoes')
<div class="card">
    <div class="card-body">
        <table class="table table-striped" id="impressoes"  class="display">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Placa</th>
                    <th>Modelo</th>
                    <th>Combustivel</th>
                    <th>Trilha</th>
                    <th>Numero Cartão</th>
                    <th>Cliente</th>
                    <th>Grupo e Subgrupo</th>
                    <th>Ação</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="contactModalLabel">Editar cartão</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card card-primary">
                    <div class="card-body">
                        <form action="{{ route('abastecimento.impressao.edit.cartao', ':id') }}" method="GET" id="formEditar">
                            {{-- @method('PUT') --}}

                            @csrf
                            <input type="hidden" name="id" id="modal-id">
                            <input type="hidden" name="idlote" id="idlote">

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Placa</label>
                                        <input type="text" name="placa" id="placa" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Modelo</label>
                                        <input type="text" name="modelo" id="modelo" class="form-control">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Combustivel</label>
                                        <input type="text" name="combustivel" id="combustivel" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Cliente</label>
                                        <input type="text" name="cliente" id="cliente" class="form-control">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Grupo e Subgrupo</label>
                                        <input type="text" name="gruposubgrupo" id="gruposubgrupo" class="form-control">
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
         var idLote = "{{ $lote->id }}";
         var url = '/abastecimento/impressao/edit/' + idLote;
         console.log(url);


        $(document).ready(function() {
            $('#impressoes').DataTable({
                "language": {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json',
                },
                processing: true,
                serverSide: true,
                ajax: url,
                columns: [
                        { data: 'id', name: 'id' },
                        { data: 'placa', name: 'placa' },
                        { data: 'modelo', name: 'modelo'},
                        { data: 'combustivel', name: 'combustivel'},
                        { data: 'trilha', name: 'trilha'},
                        { data: 'numero_cartao', name: 'numero_cartao'},
                        { data: 'cliente', name: 'cliente'},
                        { data: 'gruposubgrupo', name: 'gruposubgrupo'},
                        { data: 'action', name: 'action', orderable: false, searchable: false},
                ],
                "pageLength": 10,
                "lengthMenu": [10, 25, 50, 100, 200],

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
            var placa = $(this).data('placa');
            var modelo = $(this).data('modelo');
            var combustivel = $(this).data('combustivel');
            var cliente = $(this).data('cliente');
            var gruposubgrupo = $(this).data('gruposubgrupo');
            var idlote = $(this).data('idlote');

            console.log(placa);

            // Preencher os campos do modal
            $('#modal-id').val(id);
            $('#idlote').val(idlote);
            $('#placa').val(placa);
            $('#modelo').val(modelo);
            $('#combustivel').val(combustivel);
            $('#cliente').val(cliente);
            $('#gruposubgrupo').val(gruposubgrupo);


            // Atualizar a ação do formulário com o ID
            var formAction = "{{ route('abastecimento.impressao.edit.cartao', ':id') }}";
            formAction = formAction.replace(':id', id);
            $('#formEditar').attr('action', formAction);

            // Mostrar o modal
            $('#modalEditar').modal('show');
        });


    });
</script>



@endsection





{{--@section('js')
@parent

    <script>src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"</script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>

    {{-- esse que dá o estilo <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">


    <script>
        new DataTable('#importação');
    </script>

<script>
    $(document).ready(function () {
        $('#tabela-editavel').DataTable();
    });
</script>

<!-- resources/views/sua/view/editar.blade.php -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var myModal = new bootstrap.Modal(document.getElementById('modalEditar'));
</script>



    <script>
        $(document).ready(function () {
            $('.btn-editar').on('click', function () {
                var id = $(this).data('id');
                var placa = $(this).data('placa');
                var modelo = $(this).data('modelo');
                var combustivel = $(this).data('combustivel');
                var cliente = $(this).data('cliente');
                var gruposubgrupo = $(this).data('gruposubgrupo');

                // Preencher os campos do modal
                $('#modal-id').val(id);
                $('#modal-placa').val(placa);
                $('#modal-modelo').val(modelo);
                $('#modal-combustivel').val(combustivel);
                $('#modal-cliente').val(cliente);
                $('#modal-gruposubgrupo').val(gruposubgrupo);

                // Mostrar o modal
                $('#modalEditar').modal('show');
            });
        });
    </script>
@endsection--}}
