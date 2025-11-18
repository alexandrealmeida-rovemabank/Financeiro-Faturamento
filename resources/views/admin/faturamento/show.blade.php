@extends('adminlte::page')

@section('title', 'Painel de Faturamento')

@section('plugins.DataTables', true)
@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <h1>
        Painel de Faturamento - {{ $periodo }}
        <small class="float-right">Cliente: {{ $cliente->razao_social }}</small>
    </h1>
@stop

@section('content')

    {{-- Dados globais para o JavaScript --}}
    <div id="faturamento-container" 
         data-cliente-id="{{ $cliente->id }}" 
         data-periodo="{{ $periodo }}"
         data-is-publico="{{ $is_publico ? 'true' : 'false' }}"
         data-vencimento-auto="{{ Carbon\Carbon::now()->addDays($parametrosAtivos['dias_vencimento'])->format('Y-m-d') }}"
         data-ajax-url-resumo="{{ route('faturamento.getResumoAbaGeral') }}">
    </div>

    {{-- Navegação das ABAS --}}
    <div class="card card-primary card-tabs">
        <div class="card-header p-0 pt-1">
            <ul class="nav nav-tabs" id="abas-faturamento" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-geral-link" data-toggle="pill" href="#tab-geral" role="tab">
                        <i class="fa fa-info-circle"></i> Geral
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-faturas-link" data-toggle="pill" href="#tab-faturas" role="tab">
                        <i class="fa fa-file-invoice-dollar"></i> Faturas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-transacoes-link" data-toggle="pill" href="#tab-transacoes" role="tab">
                        <i class="fa fa-gas-pump"></i> Transações
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="abas-faturamento-content">
                
                {{-- ABA 1: GERAL --}}
                <div class="tab-pane fade show active" id="tab-geral" role="tabpanel">
                    @include('admin.faturamento._tab_geral')
                </div>

                {{-- ABA 2: FATURAS --}}
                <div class="tab-pane fade" id="tab-faturas" role="tabpanel">
                    @include('admin.faturamento._tab_faturas')
                </div>

                {{-- ABA 3: TRANSAÇÕES --}}
                <div class="tab-pane fade" id="tab-transacoes" role="tabpanel">
                    @include('admin.faturamento._tab_transacoes')
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para Gerar Fatura --}}
    @include('admin.faturamento._modal_gerar_fatura', [
        'cliente' => $cliente,
        'periodo' => $periodo,
        'parametrosAtivos' => $parametrosAtivos,
        'is_publico' => $is_publico
    ])

    {{-- Modal para Editar Observação --}}
    @include('admin.faturamento._modal_editar_observacao')

    {{-- Modais de Gestão de Fatura --}}
    @include('admin.faturamento._modal_editar_fatura')
    @include('admin.faturamento._modal_registrar_pagamento')
    @include('admin.faturamento._modal_aplicar_desconto')
    @include('admin.faturamento._modal_ver_comprovantes')

@stop

@section(section: 'js')

<script>

$(document).ready(function() {

    // ============================================================
    // 1. VARIÁVEIS GLOBAIS
    // ============================================================
    var tableFaturas = null;
    var tableTransacoes = null;
    var modalObs = $('#modalEditarObservacao');
    var modalDesconto = $('#modalAplicarDesconto');
    var modalEditar = $('#modalEditarFatura');
    var modalPagar = $('#modalRegistrarPagamento');
    var modalVerComprovantes = $('#modalVerComprovantes');

    var container = $('#faturamento-container');
    var globalData = {
        cliente_id: container.data('cliente-id'),
        periodo: container.data('periodo'),
        ajax_resumo_url: container.data('ajax-url-resumo')
    };

    // Token CSRF global
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // ============================================================
    // 2. FUNÇÕES HELPER DE ATUALIZAÇÃO
    // ============================================================

    /**
     * Atualiza os cards de resumo da Aba Faturas (Aba 2)
     */
    function atualizarCardsFaturas() {
        $('#card-qtd-faturas, #card-valor-gerado, #card-valor-pago, #card-valor-pendente').text('...');
        var cardWrapper = $('#card-pendente-geracao-wrapper');
        
        $.get('{{ route('faturamento.getFaturasSummary') }}', globalData, function(data) {
            $('#card-qtd-faturas').text(data.qtd_faturas);
            $('#card-valor-gerado').text(data.valor_gerado);
            $('#card-valor-pago').text(data.valor_pago);
            $('#card-valor-pendente').text(data.valor_pendente);
            $('#card-valor-pendente-geracao').text(data.valor_pendente_geracao);

            // Lógica de exibição condicional
            if (data.valor_pendente_geracao && data.valor_pendente_geracao !== 'R$ 0,00') {
                cardWrapper.slideDown();
            } else {
                cardWrapper.slideUp();
            }
        }).fail(function() {
            $('#card-qtd-faturas, #card-valor-gerado, #card-valor-pago, #card-valor-pendente').text('Erro!');
            cardWrapper.hide(); 
        });
    }

    /**
     * Atualiza os cards de resumo da Aba Geral (Aba 1)
     */
    function atualizarAbaGeral() {
        // Verifica se a função 'atualizarResumoGeral' existe (definida em _tab_geral.blade.php)
        if (typeof atualizarResumoGeral === 'function') {
            atualizarResumoGeral();
        } else {
            // Fallback simples
            $.get(globalData.ajax_resumo_url, globalData, function(data) {
                $('#total-faturado-box .info-box-number').text(data.faturado);
                $('#total-bruto-box .info-box-number').text(data.bruto);
                $('#total-ir-box .info-box-number').text(data.ir);
                $('#total-liquido-box .info-box-number').text(data.liquido);
            });
        }
    }

    /**
     * Helper principal: Atualiza todas as tabelas e cards
     */
    function atualizarTudo() {
        if (tableFaturas) {
            tableFaturas.ajax.reload(null, false); // Recarrega tabela Faturas
        }
        atualizarAbaGeral(); // Recarrega cards da Aba 1
        atualizarCardsFaturas(); // Recarrega cards da Aba 2
    }
    
    // ============================================================
    // 3. LISTENERS DE EVENTOS GLOBAIS
    // ============================================================

    // Disparado pelo _modal_gerar_fatura.blade.php
    $(document).on('faturaGerada', function() {
        setTimeout(atualizarTudo, 500); 
    });

    // ============================================================
    // 4. INICIALIZAÇÃO DAS ABAS (TABS)
    // ============================================================

    // Aba Geral (Inicial)
    $('#tab-geral-link').on('shown.bs.tab', function () {
        atualizarAbaGeral();
    });
    // Carrega Aba Geral na primeira vez
    if ($('#tab-geral').hasClass('active')) {
        atualizarAbaGeral();
    }

    // Aba Faturas (DataTables)
    $('#tab-faturas-link').on('shown.bs.tab', function () {
        if ($('#card-qtd-faturas').text() === '...') {
             atualizarCardsFaturas();
        }
        
        if (tableFaturas === null) {
            tableFaturas = $('#faturas-geradas-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('faturamento.getFaturas') }}',
                    data: function (d) {
                        d.cliente_id = globalData.cliente_id;
                        d.periodo = globalData.periodo;
                    }
                },
                columns: [
                    { data: 'checkbox', orderable: false, searchable: false, className: "text-center" },
                    { data: 'id' },
                    { data: 'numero_fatura' },
                    { data: 'nota_fiscal' },
                    { data: 'data_emissao' },
                    { data: 'data_vencimento' },
                    { data: 'valor_total' },
                    { data: 'valor_impostos' },
                    { data: 'valor_descontos' },
                    { data: 'desconto_manual', name: 'desconto_manual', orderable: false, searchable: false }, 
                    { data: 'taxa_adm', name: 'taxa_adm', orderable: false, searchable: false },
                    { data: 'tipo_taxa', name: 'tipo_taxa', orderable: false, searchable: false },
                    { data: 'valor_taxa', name: 'valor_taxa', orderable: false, searchable: false },
                    // --- FIM DA CORREÇÃO ---

                    { data: 'valor_liquido' },
                    { data: 'valor_recebido', name: 'valor_recebido', orderable: false, searchable: false },
                    { data: 'saldo_pendente', name: 'saldo_pendente', orderable: false, searchable: false },
                    { data: 'status' },
                    { data: 'action', orderable: false, searchable: false, className: "text-center" }
                ],
                language: {
                     url: "/storage/traducao_datatables_pt_br.json"
                }
            });
        }
    });

    // Aba Transações (DataTables)
    $('#tab-transacoes-link').on('shown.bs.tab', function () {
        if (tableTransacoes === null) {
            tableTransacoes = $('#transacoes-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('faturamento.getTransacoes') }}',
                    data: d => {
                        d.cliente_id = globalData.cliente_id;
                        d.periodo = globalData.periodo;
                    }
                },
                columns: [
                    { data: 'id' },
                    { data: 'faturada' },
                    {
                        data: 'data_transacao',
                        render: function (data, type, row) {
                            if (!data) return '';
                            const date = new Date(data);
                            const dia = String(date.getDate()).padStart(2, '0');
                            const mes = String(date.getMonth() + 1).padStart(2, '0');
                            const ano = date.getFullYear();
                            const hora = String(date.getHours()).padStart(2, '0');
                            const minuto = String(date.getMinutes()).padStart(2, '0');
                            const segundo = String(date.getSeconds()).padStart(2, '0');
                            return `${dia}-${mes}-${ano} ${hora}:${minuto}:${segundo}`;
                        }
                    },
                    { data: 'credenciado_nome' },
                    { data: 'grupo_nome' },
                    { data: 'subgrupo_nome' },
                    { data: 'produto_nome' },
                    { data: 'valor_total' },
                    { data: 'aliquota_ir' },
                    { data: 'valor_ir' },
                    { data: 'placa' },
                ],
                language: {
                    url: "/storage/traducao_datatables_pt_br.json"
                }
            });
        }
    });

    // ============================================================
    // 5. AÇÕES DIRETAS (Linha da Tabela)
    // ============================================================

    // Excluir Fatura
    $(document).on('click', '.btn-excluir', function() {
        const fatura_id = $(this).data('id');
        Swal.fire({
            title: 'Tem certeza?',
            text: `Isso excluirá a fatura #${fatura_id} e reabrirá as transações. Pagamentos e descontos serão perdidos.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: '{{ url('admin/faturamento/faturas') }}/' + fatura_id,
                    type: 'DELETE',
                    success: function(resp) {
                        Swal.fire('Excluída!', resp.message, 'success');
                        atualizarTudo();
                    },
                    error: function(xhr) {
                        Swal.fire('Erro!', xhr.responseJSON?.message || 'Falha ao excluir.', 'error');
                    }
                });
            }
        });
    });

    // Marcar Recebida (Rápido) - (Lógica antiga mantida)
    $(document).on('click', '.btn-receber', function() {
        const fatura_id = $(this).data('id');
        Swal.fire({
            title: 'Marcar como Recebida?',
            text: "Esta ação quitará o saldo pendente da fatura #" + fatura_id + ".",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, quitar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: '{{ url('admin/faturamento/faturas') }}/' + fatura_id + '/receber',
                    type: 'POST',
                    success: function(resp) {
                        Swal.fire('Atualizado!', resp.message, 'success');
                        atualizarTudo();
                    },
                    error: function(xhr) {
                        Swal.fire('Erro!', xhr.responseJSON?.message || 'Falha ao atualizar.', 'error');
                    }
                });
            }
        });
    });

    // ============================================================
    // 6. LÓGICA DOS MODAIS (Agrupados por Modal)
    // ============================================================

    // --- Modal: Editar Fatura / Refaturar ---
    $(document).on('click', '.btn-editar-fatura', function() {
        const fatura_id = $(this).data('id');
        const url = '{{ route('faturamento.getDetalhes', ['fatura' => ':id']) }}'.replace(':id', fatura_id);

        modalEditar.find('form')[0].reset();
        $('#edit-fatura-alert-container').empty();
        $('#secao-refaturamento').hide();
        $('#refaturar-motivo-erro').hide();
        $('#edit-fatura-id').val(fatura_id);
        $('#edit-fatura-titulo-id').text(`(#${fatura_id})`);
        $('#btn-salvar-edicao-fatura, #btn-confirmar-reabertura').prop('disabled', true);
        modalEditar.modal('show');

        $.get(url, function(data) {
            $('#edit-data-vencimento').val(data.data_vencimento);
            $('#edit-nota-fiscal').val(data.nota_fiscal);
            if (data.status === 'recebida') {
                $('#secao-refaturamento').slideDown();
                $('#btn-confirmar-reabertura').prop('disabled', false);
            }
            $('#btn-salvar-edicao-fatura').prop('disabled', false);
        }).fail(function() {
            $('#edit-fatura-alert-container').html('<div class="alert alert-danger">Erro ao carregar dados.</div>');
        });
    });

    $('#btn-salvar-edicao-fatura').on('click', function() {
        const btn = $(this);
        const fatura_id = $('#edit-fatura-id').val();
        const url = '{{ route('faturamento.update', ['fatura' => ':id']) }}'.replace(':id', fatura_id);
        const form = $('#form-editar-fatura');
        const alertContainer = $('#edit-fatura-alert-container');

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Salvando...');
        alertContainer.empty();

        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            success: function(resp) {
                modalEditar.modal('hide');
                Swal.fire('Sucesso!', resp.message, 'success');
                atualizarTudo();
            },
            error: function(xhr) {
                let msg = 'Erro ao salvar.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).join('<br>');
                } else if (xhr.responseJSON) {
                    msg = xhr.responseJSON.message;
                }
                alertContainer.html(`<div class="alert alert-danger">${msg}</div>`);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Salvar Alterações');
            }
        });
    });

    $('#btn-confirmar-reabertura').on('click', function() {
        const btn = $(this);
        const fatura_id = $('#edit-fatura-id').val();
        const url = '{{ route('faturamento.reabrir', ['fatura' => ':id']) }}'.replace(':id', fatura_id);
        const motivo = $('#refaturar-motivo').val();
        const novo_status = $('#refaturar-novo-status').val();
        const alertContainer = $('#edit-fatura-alert-container');
        
        if (!motivo || motivo.length < 10) {
            $('#refaturar-motivo-erro').show().text('O motivo é obrigatório (mín. 10 caracteres).');
            $('#refaturar-motivo').focus();
            return;
        }
        $('#refaturar-motivo-erro').hide();
        
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Reabrindo...');
        alertContainer.empty();

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                motivo_reabertura: motivo,
                novo_status: novo_status,
                _token: '{{ csrf_token() }}'
            },
            success: function(resp) {
                modalEditar.modal('hide');
                Swal.fire('Sucesso!', resp.message, 'success');
                atualizarTudo();
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Erro ao reabrir a fatura.';
                alertContainer.html(`<div class="alert alert-danger">${msg}</div>`);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fa fa-undo"></i> Confirmar Reabertura');
            }
        });
    });

    // --- Modal: Registrar Pagamento ---
    function carregarListaPagamentos(fatura_id) {
        var container = $('#lista-pagamentos-container');
        var url_lista = '{{ route('faturamento.getPagamentosLista', ['fatura' => ':id']) }}'.replace(':id', fatura_id);
        
        container.html('<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>');
        
        $.get(url_lista, function(data) {
            container.html(data);
        }).fail(function() {
            container.html('<div class="alert alert-danger">Erro ao carregar lista de pagamentos.</div>');
        });
    }

    $(document).on('click', '.btn-registrar-pagamento', function() {
        const fatura_id = $(this).data('id');
        const url_detalhes = '{{ route('faturamento.getDetalhes', ['fatura' => ':id']) }}'.replace(':id', fatura_id);

        modalPagar.find('form')[0].reset();
        $('#pay-fatura-alert-container').empty();
        $('#pay-fatura-id').val(fatura_id);
        $('#pay-fatura-titulo-id').text(`(#${fatura_id})`);
        $('#pay-valor-liquido, #pay-saldo-pendente').text('Carregando...');
        $('#btn-salvar-pagamento').prop('disabled', true);
        modalPagar.modal('show');

        $.get(url_detalhes, function(data) {
            $('#pay-valor-liquido').text(data.valor_liquido_formatado);
            $('#pay-saldo-pendente').text(data.saldo_pendente_formatado);
            let saldo = data.saldo_pendente || 0.0;
            $('#pay-valor-pago').val(saldo.toFixed(2));
            $('#btn-salvar-pagamento').prop('disabled', false);
        }).fail(function() {
            $('#pay-fatura-alert-container').html('<div class="alert alert-danger">Erro ao carregar dados.</div>');
        });

        carregarListaPagamentos(fatura_id);
    });

    $('#btn-salvar-pagamento').on('click', function() {
        const btn = $(this);
        const fatura_id = $('#pay-fatura-id').val();
        const url_salvar = '{{ route('faturamento.addPagamento', ['fatura' => ':id']) }}'.replace(':id', fatura_id);
        const form = $('#form-registrar-pagamento');
        const alertContainer = $('#pay-fatura-alert-container');
        const formData = new FormData(form[0]);
        
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Salvando...');
        alertContainer.empty();

        $.ajax({
            url: url_salvar,
            type: 'POST',
            data: formData,
            processData: false, 
            contentType: false, 
            success: function(resp) {
                form[0].reset();
                $('#pay-data-pagamento').val(new Date().toISOString().split('T')[0]);
                carregarListaPagamentos(fatura_id);
                atualizarTudo();
                
                $.get('{{ route('faturamento.getDetalhes', ['fatura' => ':id']) }}'.replace(':id', fatura_id), function(data) {
                    $('#pay-valor-liquido').text(data.valor_liquido_formatado);
                    $('#pay-saldo-pendente').text(data.saldo_pendente_formatado);
                    let saldo = data.saldo_pendente || 0.0;
                    $('#pay-valor-pago').val(saldo.toFixed(2));
                });
            },
            error: function(xhr) {
                let msg = 'Erro ao salvar. Verifique os campos.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).map(function(err) {
                        return `<li>${err[0]}</li>`; 
                    }).join('');
                    msg = `<ul>${msg}</ul>`;
                } else if (xhr.responseJSON) {
                    msg = xhr.responseJSON.message;
                }
                alertContainer.html(`<div class="alert alert-danger">${msg}</div>`);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Adicionar Pagamento');
            }
        });
    });

    $(document).on('click', '.btn-remover-pagamento', function() {
        const btn = $(this);
        const url_remover = btn.data('url');
        const fatura_id = $('#pay-fatura-id').val();
        
        if (!confirm('Tem certeza? O comprovante será excluído permanentemente.')) {
            return;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: url_remover,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(resp) {
                carregarListaPagamentos(fatura_id);
                atualizarTudo();
                
                $.get('{{ route('faturamento.getDetalhes', ['fatura' => ':id']) }}'.replace(':id', fatura_id), function(data) {
                    $('#pay-valor-liquido').text(data.valor_liquido_formatado);
                    $('#pay-saldo-pendente').text(data.saldo_pendente_formatado);
                    let saldo = data.saldo_pendente || 0.0;
                    $('#pay-valor-pago').val(saldo.toFixed(2));
                });
            },
            error: function(xhr) {
                Swal.fire('Erro!', xhr.responseJSON?.message || 'Falha ao remover.', 'error');
                btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
            }
        });
    });

    // --- Modal: Aplicar Desconto ---
    $('#desconto-tipo').on('change', function() {
        var helper = $('#desconto-valor-helper');
        if ($(this).val() === 'percentual') {
            helper.text('Valor em % (calculado sobre o Saldo Pendente)');
        } else {
            helper.text('Valor em R$ (Ex: 100.50)');
        }
    });

    function carregarListaDescontos(fatura_id) {
        var container = $('#lista-descontos-container');
        var url_lista = '{{ route('faturamento.getDescontosLista', ['fatura' => ':id']) }}'.replace(':id', fatura_id);
        
        container.html('<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>');
        
        $.get(url_lista, function(data) {
            container.html(data);
        }).fail(function() {
            container.html('<div class="alert alert-danger">Erro ao carregar lista.</div>');
        });
    }

    $(document).on('click', '.btn-aplicar-desconto', function() {
        const fatura_id = $(this).data('id');
        const url_detalhes = '{{ route('faturamento.getDetalhes', ['fatura' => ':id']) }}'.replace(':id', fatura_id);

        modalDesconto.find('form')[0].reset();
        $('#desconto-tipo').val('fixo').trigger('change');
        $('#desconto-fatura-alert-container').empty();
        $('#desconto-fatura-id').val(fatura_id);
        $('#desconto-fatura-titulo-id').text(`(#${fatura_id})`);
        $('#desconto-valor-liquido, #desconto-saldo-pendente').text('Carregando...');
        $('#btn-salvar-desconto').prop('disabled', true);
        modalDesconto.modal('show');

        $.get(url_detalhes, function(data) {
            $('#desconto-valor-liquido').text(data.valor_liquido_formatado);
            $('#desconto-saldo-pendente').text(data.saldo_pendente_formatado);
            $('#btn-salvar-desconto').prop('disabled', false);
        }).fail(function() {
            $('#desconto-fatura-alert-container').html('<div class="alert alert-danger">Erro ao carregar dados.</div>');
        });

        carregarListaDescontos(fatura_id);
    });

    $('#btn-salvar-desconto').on('click', function() {
        const btn = $(this);
        const fatura_id = $('#desconto-fatura-id').val();
        const url_salvar = '{{ route('faturamento.addDesconto', ['fatura' => ':id']) }}'.replace(':id', fatura_id);
        const form = $('#form-aplicar-desconto');
        const alertContainer = $('#desconto-fatura-alert-container');
        let formData = form.serialize();

        function sendAjax(formData) {
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Salvando...');
            alertContainer.empty();

            $.ajax({
                url: url_salvar,
                type: 'POST',
                data: formData,
                success: function(resp) {
                    $('#desconto-valor').val('');
                    $('#desconto-justificativa').val('');
                    carregarListaDescontos(fatura_id);
                    atualizarTudo();
                    
                    $.get('{{ route('faturamento.getDetalhes', ['fatura' => ':id']) }}'.replace(':id', fatura_id), function(data) {
                        $('#desconto-valor-liquido').text(data.valor_liquido_formatado);
                        $('#desconto-saldo-pendente').text(data.saldo_pendente_formatado);
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON.needs_confirmation) {
                        Swal.fire({
                            title: 'Quitar Fatura?',
                            text: xhr.responseJSON.message,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sim, quitar!',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.value) {
                                let newFormData = formData + '&force_quit=1';
                                sendAjax(newFormData);
                            }
                        });
                    } else {
                        let msg = 'Erro ao salvar.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).map(function(err) {
                                return `<li>${err[0]}</li>`;
                            }).join('');
                            msg = `<ul>${msg}</ul>`;
                        } else if (xhr.responseJSON) {
                            msg = xhr.responseJSON.message;
                        }
                        alertContainer.html(`<div class="alert alert-danger">${msg}</div>`);
                    }
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Adicionar Desconto');
                }
            });
        }
        sendAjax(formData);
    });
    
    $(document).on('click', '.btn-remover-desconto', function() {
        const btn = $(this);
        const url_remover = btn.data('url');
        const fatura_id = $('#desconto-fatura-id').val();
        
        if (!confirm('Tem certeza que deseja remover este desconto?')) {
            return;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: url_remover,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(resp) {
                carregarListaDescontos(fatura_id);
                atualizarTudo();
                
                $.get('{{ route('faturamento.getDetalhes', ['fatura' => ':id']) }}'.replace(':id', fatura_id), function(data) {
                    $('#desconto-valor-liquido').text(data.valor_liquido_formatado);
                    $('#desconto-saldo-pendente').text(data.saldo_pendente_formatado);
                });
            },
            error: function(xhr) {
                Swal.fire('Erro!', xhr.responseJSON?.message || 'Falha ao remover.', 'error');
                btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
            }
        });
    });

    // --- Modal: Ver Comprovantes ---
    $(document).on('click', '.btn-ver-comprovantes', function() {
        const fatura_id = $(this).data('id');
        $('#comprovante-fatura-titulo-id').text(`(#${fatura_id})`);
        var container = $('#lista-comprovantes-container');
        var url_lista = '{{ route('faturamento.getPagamentosLista', ['fatura' => ':id']) }}'.replace(':id', fatura_id);
        
        container.html('<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>');
        
        $.get(url_lista, function(data) {
            container.html(data);
        }).fail(function() {
            container.html('<div class="alert alert-danger">Erro ao carregar lista de comprovantes.</div>');
        });

        modalVerComprovantes.modal('show');
    });

    $(document).on('click', '.btn-editar-observacao', function() {
        abrirModalObservacao($(this).data('id'));
    });
    

    // --- Modal: Editar Observação (Individual) ---
    function abrirModalObservacao(fatura_id) {
        $('#edit-obs-fatura-id').val(fatura_id);
        $('#edit-obs-textarea').val('Carregando...');
        $('#modal-obs-alert-container').empty();
        modalObs.modal('show');

        const url = '{{ route('faturamento.faturas.getObservacao', ['fatura' => ':id']) }}'.replace(':id', fatura_id);
        $.get(url, function(data) {
            $('#edit-obs-textarea').val(data.observacoes);
        }).fail(() => $('#edit-obs-textarea').val('Erro ao carregar.'));
    }

    $('#btn-salvar-observacao').on('click', function() {
        const btn = $(this);
        const form = $('#form-edit-observacao');
        const fatura_id = $('#edit-obs-fatura-id').val();
        const url = '{{ route('faturamento.faturas.updateObservacao', ['fatura' => ':id']) }}'.replace(':id', fatura_id);

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Salvando...');

        $.ajax({
            url: url,
            type: 'PUT',
            data: form.serialize(),
            success: function(resp) {
                modalObs.modal('hide');
                Swal.fire('Sucesso!', resp.message, 'success');
                atualizarTudo();
            },
            error: function(xhr) {
                $('#modal-obs-alert-container').html(`<div class="alert alert-danger">${xhr.responseJSON?.message || 'Erro ao salvar.'}</div>`);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Salvar Alterações');
            }
        });
    });

    $(document).on('click', '#modalEditarObservacao [data-dismiss="modal"]', function(){
        modalObs.modal('hide');
    });

    // ============================================================
    // 7. AÇÕES EM MASSA (Seleção e Botões)
    // ============================================================

    // --- Helpers de Seleção ---
    function getSelectedFaturaIds() {
        return $('.fatura-checkbox:checked').map(function() {
            return $(this).data('id');
        }).get();
    }

    function toggleMassaButtons() {
        const qtd = getSelectedFaturaIds().length;
        $('#btn-marcar-recebidas-massa').prop('disabled', qtd === 0);
        $('#btn-excluir-selecionadas-massa').prop('disabled', qtd === 0);
        $('#btn-editar-observacao-massa').prop('disabled', qtd !== 1);
    }

    // --- Listeners dos Checkboxes ---
    $(document).on('change', '#fatura-select-all', function() {
        $('.fatura-checkbox:not(:disabled)').prop('checked', $(this).is(':checked'));
        toggleMassaButtons();
    });

    $(document).on('change', '.fatura-checkbox', function() {
        if (!this.checked) $('#fatura-select-all').prop('checked', false);
        toggleMassaButtons();
    });

    $('#faturas-geradas-table').on('draw.dt', toggleMassaButtons);

    // --- Listeners dos Botões de Ação em Massa ---
    $('#btn-editar-observacao-massa').on('click', function() {
        var ids = getSelectedFaturaIds();
        if (ids.length !== 1) {
            Swal.fire('Atenção', 'Selecione exatamente UMA fatura para editar a observação.', 'info');
            return;
        }
        abrirModalObservacao(ids[0]); // Reutiliza a função de edição individual
    });

    $('#btn-marcar-recebidas-massa').on('click', function() {
        var ids = getSelectedFaturaIds();
        if (ids.length === 0) return;

        Swal.fire({
            title: 'Marcar Recebidas?',
            text: "Quitar o saldo de " + ids.length + " fatura(s) selecionada(s)?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, quitar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                $.post('{{ route('faturamento.faturas.bulkReceber') }}', { ids: ids }, function(response) {
                    Swal.fire('Sucesso!', response.message, 'success');
                    atualizarTudo();
                }).fail(function(xhr) {
                    Swal.fire('Erro!', xhr.responseJSON?.message || 'Não foi possível atualizar.', 'error');
                });
            }
        });
    });

    $('#btn-excluir-selecionadas-massa').on('click', function() {
        var ids = getSelectedFaturaIds();
        if (ids.length === 0) return;

        Swal.fire({
            title: 'Excluir Faturas?',
            text: "Excluir " + ids.length + " fatura(s)? (Faturas 'Recebidas' serão ignoradas).",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                $.post('{{ route('faturamento.faturas.bulkDestroy') }}', { ids: ids }, function(response) {
                    Swal.fire('Sucesso!', response.message, 'success');
                    atualizarTudo();
                }).fail(function(xhr) {
                    Swal.fire('Erro!', xhr.responseJSON?.message || 'Não foi possível excluir.', 'error');
                });
            }
        });
    });

    // ============================================================
    // 8. UTILITÁRIOS (Ex: Copiar Observação)
    // ============================================================
    
    $(document).on('click', '#btn-copiar-observacao', function(e) {
        e.preventDefault();
        const $ta = $('#edit-obs-textarea');
        if (!$ta.length) return;
        const texto = ($ta.val() || '').trim();
        if (!texto) return;
        
        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            navigator.clipboard.writeText(texto)
                .then(() => {
                    Swal.fire({ icon: 'success', title: 'Copiado!', timer: 1200, showConfirmButton: false });
                })
                .catch(() => {
                    fallbackCopyTextToClipboard(texto);
                });
        } else {
            fallbackCopyTextToClipboard(texto);
        }
    });

    function fallbackCopyTextToClipboard(text) {
        try {
            const $temp = $('<textarea>');
            $temp.css({ position: 'absolute', left: '-9999px', top: '0' }).val(text);
            $('body').append($temp);
            $temp.focus().select();
            document.execCommand('copy');
            $temp.remove();
            Swal.fire({ icon: 'success', title: 'Copiado!', timer: 1200, showConfirmButton: false });
        } catch (err) {
            Swal.fire('Erro', 'Não foi possível copiar.', 'error');
        }
    }


    $(document).on('click', '.btn-gerar-pdf', function() {
        var btn = $(this);
        var url = btn.data('url');
        
        // Desabilita o botão e mostra o spinner
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin text-primary mr-2"></i> Gerando...');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                // Mostra um alerta de sucesso (toast)
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: response.message,
                    showConfirmButton: false,
                    timer: 4000
                });
            },
            error: function(xhr) {
                Swal.fire('Erro!', xhr.responseJSON?.message || 'Falha ao iniciar a geração.', 'error');
            },
            complete: function() {
                // Reabilita o botão
                btn.prop('disabled', false).html('<i class="fa fa-print text-primary mr-2"></i> Imprimir Fatura');
            }
        });
    });

});
</script>
@stop