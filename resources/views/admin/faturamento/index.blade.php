@extends('adminlte::page')

@section('title', 'Faturamento')

@section('plugins.DataTables', true)
@section('plugins.Select2', true)

@section('content_header')
    <h1 class="fw-bold text-primary mb-3">
        <i class="fas fa-file-invoice-dollar"></i> Resumo de Faturamento
    </h1>
@stop

@section('content')
<div class="container-fluid">

    <div class="card card-filter mb-4 shadow-lg border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>Filtros de Pesquisa
            </h3>
        </div>

        <div id="filtrosCollapse" class="collapse show">
            <div class="card-body bg-light">
                <div class="row g-3">

                    <div class="col-md-3">
                        <label for="periodo" class="form-label fw-bold text-secondary">Período</label>
                        <input type="month" id="periodo" name="periodo" class="form-control filter-select" 
                               value="{{ date('Y-m', strtotime('-1 month')) }}">
                    </div>

                    <div class="col-md-3">
                        <label for="cnpj" class="form-label fw-bold text-secondary">CNPJ</label>
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
                        <label for="organizacao" class="form-label fw-bold text-secondary">Organização</label>
                        <select id="organizacao" name="organizacao" class="form-control select2 filter-select">
                            <option value="">Todas as Organizações</option>
                            @foreach($organizacoes as $organizacao_item)
                                <option value="{{ $organizacao_item->id }}">{{ $organizacao_item->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- FILTRO TIPO ORGANIZAÇÃO (PÚBLICO/PRIVADO) --}}
                    <div class="col-md-3">
                        <label for="tipo_organizacao" class="form-label fw-bold text-secondary">Tipo Organização</label>
                        <select id="tipo_organizacao" name="tipo_organizacao" class="form-control select2 filter-select">
                            <option value="">Todas</option>
                            <option value="publica">Pública</option>
                            <option value="privada">Privada</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="status" class="form-label fw-bold text-secondary">Status</label>
                        <select id="status" name="status" class="form-control select2 filter-select">
                            <option value="">Todos os Status</option>
                            @foreach($statusOptions as $status_item)
                                <option value="{{ $status_item }}">{{ $status_item }}</option>
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
    <div class="card card-filter mb-4 shadow-lg border-0">
        <div class="card-header bg-secondary text-white">
            <i class="fas fa-list"></i> Resultados
        </div>

        <div class="card-body">
            <table id="faturamento-table" class="table table-hover align-middle text-sm" style="width:100%;">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>CNPJ</th>
                        <th>Mês</th>
                        <th>Ano</th>
                        <th>Valor Bruto Total</th> {{-- Coluna mostra o valor bruto total, como solicitado --}}
                        <th>Status</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@stop

{{-- ESTILOS CSS --}}
@push('css')
<style>
    .card-filter {
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        border: none;
        overflow: hidden;
        background-color: #fff;
    }
    .filter-select, .form-control {
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
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
</style>
@endpush

{{-- JAVASCRIPT --}}
@push('js')
<script>
$(function () {
    // 1. Inicializa todos os Select2
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    // 2. Inicializa o DataTable
    const table = $('#faturamento-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('faturamento.index') }}",
            data: function (d) {
                // 3. Envia todos os valores dos filtros para o controller
                d.periodo = $('#periodo').val();
                d.cnpj = $('#cnpj').val();
                d.razao_social = $('#razao_social').val();
                d.municipio_id = $('#municipio_id').val();
                d.estado = $('#estado').val();
                d.status = $('#status').val();
                d.organizacao = $('#organizacao').val();
                d.tipo_organizacao = $('#tipo_organizacao').val(); // Filtro Público/Privado
            }
        },
        // 4. Define as colunas
        columns: [
            { data: 'id', name: 'id' },
            { data: 'razao_social', name: 'razao_social' },
            { data: 'cnpj', name: 'cnpj' },
            { data: 'mes', name: 'mes', orderable: false, searchable: false },
            { data: 'ano', name: 'ano', orderable: false, searchable: false },
            { data: 'valor_bruto_total', name: 'valor_bruto_total', searchable: false }, // Coluna de Valor Bruto
            { data: 'status', name: 'status', searchable: false }, // Nova lógica de status
            { data: 'action', name: 'action', orderable: false, searchable: false, className: "text-center" }
        ],
        order: [[1, 'asc']], // Ordenar por Razão Social
        language: { url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json' }
    });

    // 5. Ação do botão Filtrar
    $('#filter').click(() => table.draw());

    // 6. Ação do botão Limpar
    $('#reset').click(() => {
        // Reseta os selects para o valor padrão
        $('.select2').val('').trigger('change');
        // Reseta o período para o padrão
        $('#periodo').val('{{ date('Y-m', strtotime('-1 month')) }}');
        // Redesenha a tabela
        table.draw();
    });
});
</script>
@endpush