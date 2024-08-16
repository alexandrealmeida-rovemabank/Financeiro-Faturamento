@extends('adminlte::page')

@section('title', 'Cartões' )

@section('content_header')
    <h1 class="m-0 text-dark">Editar Lote</h1>
@stop

@section('content')
@include('layouts.notificacoes')
<div id="export-buttons"></div>

<br><br>

<div class="card">
    <div class="card-body">
        <table class="table table-striped display" id="impressoes">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Placa</th>
                    <th>Modelo</th>
                    <th>Combustível</th>
                    <th>Trilha</th>
                    <th>Número Cartão</th>
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
                <div class="card card-success">
                    <div class="card-body">
                        <!-- Formulário para editar o cartão -->
                        <form action="{{ route('abastecimento.impressao.edit.cartao', ':id') }}" method="GET" id="formEditar">
                            @csrf
                            <input type="hidden" name="id" id="modal-id">
                            <input type="hidden" name="idlote" id="idlote">

                            <!-- Campo para editar a placa -->
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Placa</label>
                                        <input type="text" name="placa" id="placa" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <!-- Campos para editar modelo e combustível -->
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Modelo</label>
                                        <input type="text" name="modelo" id="modelo" class="form-control">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Combustível</label>
                                        <input type="text" name="combustivel" id="combustivel" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <!-- Campos para editar cliente e grupo/subgrupo -->
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

                            <!-- Botões de ação do formulário -->
                            <button type="submit" class="btn btn-primary" id="btnSalvar">Salvar</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
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

        var idLote = "{{ $lote->id }}";
        var url = '/abastecimento/impressao/edit/' + idLote;

        // Inicializar DataTable com as configurações desejadas
        var table = $('#impressoes').DataTable({
            lengthMenu: [
                [10, 25, 50, 100, 200, -1],
                [10, 25, 50, 100, 200, 'Todos'],
            ],
            dom: 'lBfrtip',
            buttons: ['csv', 'excel', 'print', 'pdf'],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json',
            },
            processing: true,
            serverSide: true,
            ajax: url,
            columns: [
                { data: 'id', name: 'id' },
                { data: 'placa', name: 'placa' },
                { data: 'modelo', name: 'modelo' },
                { data: 'combustivel', name: 'combustivel' },
                { data: 'trilha', name: 'trilha' },
                { data: 'numero_cartao', name: 'numero_cartao' },
                { data: 'cliente', name: 'cliente' },
                { data: 'gruposubgrupo', name: 'gruposubgrupo' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            pageLength: 10,
            initComplete: function() {
                $('.dataTables_filter').css('display', 'block');
                $('.dataTables_filter').css('margin-top', '10px');
                $('#export-buttons').append($('.dt-buttons'));
            }
        });

        var myModal = new bootstrap.Modal(document.getElementById('modalEditar'));

        // Evento de clique para editar o cartão
        $(document).on('click', '.btn-editar', function() {
            var id = $(this).data('id');
            var placa = $(this).data('placa');
            var modelo = $(this).data('modelo');
            var combustivel = $(this).data('combustivel');
            var cliente = $(this).data('cliente');
            var gruposubgrupo = $(this).data('gruposubgrupo');
            var idlote = $(this).data('idlote');

            // Preencher os campos do modal com os dados do cartão
            $('#modal-id').val(id);
            $('#idlote').val(idlote);
            $('#placa').val(placa);
            $('#modelo').val(modelo);
            $('#combustivel').val(combustivel);
            $('#cliente').val(cliente);
            $('#gruposubgrupo').val(gruposubgrupo);

            // Atualizar a ação do formulário com o ID do cartão
            var formAction = "{{ route('abastecimento.impressao.edit.cartao', ':id') }}";
            formAction = formAction.replace(':id', id);
            $('#formEditar').attr('action', formAction);

            // Mostrar o modal de edição
            myModal.show();
        });
    });
</script>
@stop
