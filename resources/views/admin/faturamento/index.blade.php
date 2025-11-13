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

        {{-- 1. Filtros de Pesquisa --}}
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

        {{-- 2. [NOVO] Cards de Resumo (KPIs) --}}
        <div class="row mb-4">
            <div class="col-md-2 col-sm-6 col-12">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pendente Geração</span>
                        <span class="info-box-number card-value" id="card-pendente-geracao">Carregando...</span>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 col-12">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-file-invoice"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Qtd Faturas</span>
                        <span class="info-box-number card-value" id="card-qtd-faturas">Carregando...</span>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 col-12">
                <div class="info-box bg-primary">
                    <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Valor Gerado</span>
                        <span class="info-box-number card-value" id="card-valor-gerado">Carregando...</span>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 col-12">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Valor Pago</span>
                        <span class="info-box-number card-value" id="card-valor-pago">Carregando...</span>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 col-12">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pendente Pagto</span>
                        <span class="info-box-number card-value" id="card-pendente-pagamento">Carregando...</span>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 col-12">
                <div class="info-box bg-secondary">
                    <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total IR</span>
                        <span class="info-box-number card-value" id="card-valor-ir">Carregando...</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Tabela de Resultados --}}
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
                            <th>Valor Bruto Total</th>
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
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        border: none;
        overflow: hidden;
        background-color: #fff;
    }

    .filter-select,
    .form-control,
    .select2-container .select2-selection--single {
        height: 42px !important;
        border-radius: 8px !important;
        border: 1px solid #ccc !important;
        background-color: #fff !important;
    }

    .select2-container .select2-selection--single {
        line-height: 42px;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: 40px !important;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
    
    /* Estilo para os info-boxes */
    .info-box {
        min-height: 80px;
        display: flex;
        margin-bottom: 1rem;
        padding: .5rem;
        border-radius: .25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }
    .info-box-icon {
        border-radius: .25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        width: 70px;
    }
    .info-box-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        line-height: 120%;
        flex: 1;
        padding: 0 10px;
    }
    .info-box-text {
        display: block;
        font-size: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-transform: uppercase;
    }
    .info-box-number {
        display: block;
        font-weight: 700;
        font-size: 14px; /* Tamanho da fonte ajustado */
    }

    .badge-light {
        color: #ffffffff;
        background-color: #3498db;
    }
</style>
@endpush

{{-- JAVASCRIPT --}}
@push('js')
<script>
    // Função global para atualizar os cards
    function updateCards() {
        // Pega todos os filtros atuais do formulário
        let filters = {
            periodo: $('#periodo').val(),
            cnpj: $('#cnpj').val(),
            razao_social: $('#razao_social').val(),
            municipio_id: $('#municipio_id').val(),
            estado: $('#estado').val(),
            organizacao: $('#organizacao').val(),
            tipo_organizacao: $('#tipo_organizacao').val(),
            status: $('#status').val()
        };

        // Exibe loading nos cards
        $('.card-value').text('Carregando...');

        $.ajax({
            url: "{{ route('faturamento.stats') }}",
            method: 'GET',
            data: filters,
            success: function(response) {
                // Atualiza os valores
                $('#card-pendente-geracao').text(response.pendente_geracao);
                $('#card-qtd-faturas').text(response.qtd_faturas);
                $('#card-valor-gerado').text(response.valor_gerado);
                $('#card-valor-pago').text(response.valor_pago);
                $('#card-pendente-pagamento').text(response.pendente_pagamento);
                $('#card-valor-ir').text(response.valor_ir);
            },
            error: function() {
                console.error('Erro ao carregar estatísticas');
                $('.card-value').text('Erro');
            }
        });
    }

    $(function() {
        // 1. Inicializa todos os Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        // 2. Inicializa o DataTable
        const table = $('#faturamento-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('faturamento.index') }}",
                data: function(d) {
                    // 3. Envia todos os valores dos filtros para o controller
                    d.periodo = $('#periodo').val();
                    d.cnpj = $('#cnpj').val();
                    d.razao_social = $('#razao_social').val();
                    d.municipio_id = $('#municipio_id').val();
                    d.estado = $('#estado').val();
                    d.status = $('#status').val();
                    d.organizacao = $('#organizacao').val();
                    d.tipo_organizacao = $('#tipo_organizacao').val();
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'razao_social', name: 'razao_social' },
                { data: 'cnpj', name: 'cnpj' },
                { data: 'mes', name: 'mes', orderable: false, searchable: false },
                { data: 'ano', name: 'ano', orderable: false, searchable: false },
                { data: 'valor_bruto_total', name: 'valor_bruto_total', searchable: false },
                { data: 'status', name: 'status', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: "text-center" }
            ],
            order: [ [1, 'asc'] ], // Ordenar por Razão Social
            language: {
               url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json'
            }
        });

        // 4. Chama a atualização dos cards na carga inicial
        updateCards();

        // 5. Ação do botão Filtrar: Redesenha tabela E atualiza cards
        $('#filter').click(() => {
            table.draw();
            updateCards(); // <--- ADICIONADO
        });

        // 6. Ação do botão Limpar
        $('#reset').click(() => {
            $('.select2').val('').trigger('change');
            $('#periodo').val('{{ date('Y-m', strtotime('-1 month')) }}');
            table.draw();
            updateCards(); // <--- ADICIONADO
        });
    });
</script>
@endpush