@extends('adminlte::page')

@section('title', 'Listagem de Clientes')

@section('content_header')
    <h1 class="fw-bold text-primary mb-3">
        <i class="fas fa-users"></i> Listagem de Clientes
    </h1>
@stop

@section('content')
<div class="container-fluid">

    <!-- ===== Card de Filtros ===== -->
    <div class="card card-filter mb-4 shadow-lg border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>Filtros de Pesquisa
            </h3>
            <button class="btn btn-sm btn-light text-primary ms-auto" type="button"
                    data-bs-toggle="collapse" data-bs-target="#filtrosCollapse" aria-expanded="true"
                    style="border-radius: 6px;">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>

        <div id="filtrosCollapse" class="collapse show">
            <div class="card-body bg-light">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="cnpj" class="form-label fw-bold text-secondary">CNPJ (Matriz ou Unidade)</label>
                        <select id="cnpj" name="cnpj" class="form-control select2 filter-select">
                            <option value="">Todos os CNPJs</option>
                            @foreach($cnpjs as $cnpj)
                                <option value="{{ $cnpj }}">{{ $cnpj }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="razao_social" class="form-label fw-bold text-secondary">Razão Social</label>
                        <select id="razao_social" name="razao_social" class="form-control select2 filter-select">
                            <option value="">Todas as Razões Sociais</option>
                            @foreach($razoesSociais as $razao)
                                <option value="{{ $razao }}">{{ $razao }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="municipio_id" class="form-label fw-bold text-secondary">Cidade</label>
                        <select id="municipio_id" name="municipio_id" class="form-control select2 filter-select">
                            <option value="">Todas as Cidades</option>
                            @foreach($municipios as $municipio)
                                <option value="{{ $municipio->id }}">{{ $municipio->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="estado" class="form-label fw-bold text-secondary">Estado (UF)</label>
                        <select id="estado" name="estado" class="form-control select2 filter-select">
                            <option value="">Todos os Estados</option>
                            @foreach($estados as $estado_item)
                                <option value="{{ $estado_item }}">{{ $estado_item }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="organizacao_id" class="form-label fw-bold text-secondary">Organização</label>
                        <select id="organizacao_id" name="organizacao_id" class="form-control select2 filter-select">
                            <option value="">Todas as Organizações</option>
                            @foreach($organizacoes as $organizacao)
                                <option value="{{ $organizacao->id }}">{{ $organizacao->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="empresa_tipo_id" class="form-label fw-bold text-secondary">Tipo</label>
                        <select id="empresa_tipo_id" name="empresa_tipo_id" class="form-control select2 filter-select">
                            <option value="">Todos os Tipos</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-top d-flex justify-content-end gap-2">
                <button type="button" id="filter" class="btn btn-primary px-4 shadow-sm">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
                <button type="button" id="reset" class="btn btn-outline-secondary px-4 shadow-sm">
                    <i class="fas fa-undo me-1"></i>Limpar
                </button>
            </div>
        </div>
    </div>
    <!-- ===== Fim do Card de Filtros ===== -->

    <!-- ===== Tabela de Clientes ===== -->
    <div class="card card-filter mb-4 shadow-lg border-0">
        <div class="card-header bg-secondary text-white">
            <i class="fas fa-list"></i> Resultados
        </div>

        <div class="card-body">
            <table id="clientes-table" class="table table-hover align-middle text-sm" style="width:100%;">
                <thead class="table-light">
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Nome Fantasia</th>
                        <th>Razão Social</th>
                        <th>CNPJ</th>
                        <th>Cidade</th>
                        <th>UF</th>
                        <th>Status</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@stop

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .card-filter {
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        border: none;
        overflow: hidden;
        background-color: #fff;
    }

    .card-filter .card-header {
        background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
        border-bottom: none;
    }

    .filter-select {
        background-color: #fff !important;
        border: 1px solid #ccc !important;
        border-radius: 8px !important;
        height: 42px !important;
    }

    .select2-container .select2-selection--single {
        height: 42px !important;
        line-height: 42px;
        border-radius: 8px !important;
        background-color: #fff !important;
        border: 1px solid #ccc !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px;
    }

    td.details-control {
        cursor: pointer;
        text-align: center;
    }

    td.details-control i {
        color: #007bff;
        font-size: 1.1rem;
        transition: transform 0.3s ease;
    }

    tr.details td.details-control i {
        transform: rotate(180deg);
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function () {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    const table = $('#clientes-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('clientes.index') }}",
            data: function (d) {
                d.cnpj = $('#cnpj').val();
                d.razao_social = $('#razao_social').val();
                d.municipio_id = $('#municipio_id').val();
                d.estado = $('#estado').val();
                d.organizacao_id = $('#organizacao_id').val();
                d.empresa_tipo_id = $('#empresa_tipo_id').val();
            }
        },
        columns: [
            { 
                data: 'unidades_count', 
                orderable: false, 
                searchable: false, 
                render: function (data) {
                    return data > 0 ? '<i class="fas fa-chevron-down"></i>' : '';
                } 
            },
            { data: 'id' },
            { data: 'nome' },
            { data: 'razao_social' },
            { data: 'cnpj' },
            { data: 'municipio_nome' },
            { data: 'estado' },
            {
                data: 'ativo',
                render: function(data) {
                    return data == 1
                        ? '<span class="badge bg-success">Ativo</span>'
                        : '<span class="badge bg-danger">Inativo</span>';
                }
            },
            { data: 'action', orderable: false, searchable: false, className: "text-center" }
        ],
        order: [[1, 'asc']],
       language: {
                    // <<<--- CORREÇÃO AQUI (protocolo-relativo e versão 1.10.25)
                   url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json'
                }
    });

    $('#filter').click(() => table.draw());
    $('#reset').click(() => {
        $('.select2').val('').trigger('change');
        table.draw();
    });

    // Função para carregar unidades
    function format (data) {
        const subTableHtml = `<div class="p-3" id="unidades-for-${data.id}">Carregando unidades...</div>`;
        $.ajax({
            url: `/clientes/${data.id}/unidades`,
            success: response => $(`#unidades-for-${data.id}`).html(response),
            error: () => $(`#unidades-for-${data.id}`).html('<div class="p-3 text-danger">Erro ao carregar unidades.</div>')
        });
        return subTableHtml;
    }

    $('#clientes-table tbody').on('click', 'td:has(i.fa-chevron-down)', function () {
        const tr = $(this).closest('tr');
        const row = table.row(tr);

        if (row.data()?.unidades_count > 0) {
            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('details');
            } else {
                row.child(format(row.data())).show();
                tr.addClass('details');
            }
        }
    });
});
</script>
@endpush
