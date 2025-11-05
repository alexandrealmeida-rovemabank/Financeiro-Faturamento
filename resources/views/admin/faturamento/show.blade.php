@extends('adminlte::page')

@section('title', 'Painel de Faturamento')

@section('plugins.DataTables', true)
@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@php
    // Define se o cliente é público (Req 4)
    // 1=Estadual, 2=Municipal, 3=Federal, 4=Economia Mista, 5=Privado
    $publico_ids = [1, 2, 3, 4];
    $is_publico = in_array($cliente->organizacao->id, $publico_ids);
@endphp

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

@stop

@section('js')
<script>
$(document).ready(function() {

    // ============================================================
    // VARIÁVEIS GLOBAIS
    // ============================================================
    var tableFaturas = null;
    var tableTransacoes = null;
    var container = $('#faturamento-container');
    var globalData = {
        cliente_id: container.data('cliente-id'),
        periodo: container.data('periodo'),
        ajax_resumo_url: container.data('ajax-url-resumo')
    };

    // Token CSRF para todas as requisições AJAX
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ============================================================
    // ABA 2: FATURAS
    // ============================================================
    $('#tab-faturas-link').on('shown.bs.tab', function () {
        if (tableFaturas === null) {
            tableFaturas = $('#faturas-geradas-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('faturamento.getFaturas') }}',
                    data: function (d) {
                        d.cliente_id = globalData.cliente_id;
                        d.periodo = globalData.periodo;
                    },
                    dataSrc: function (json) {
                        console.log("🔍 Retorno AJAX Faturas:", json);
                        return json.data;
                    }
                },

                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'numero_fatura', name: 'Nº Fatura' },
                    { data: 'nota_fiscal', name: 'nota_fiscal' },
                    { data: 'data_emissao', name: 'data_emissao' },
                    { data: 'data_vencimento', name: 'data_vencimento' },
                    { data: 'valor_total', name: 'valor total' },
                    { data: 'valor_impostos', name: 'valor impostos' },
                    { data: 'valor_descontos', name: 'valor Descontos' },
                    { data: 'valor_liquido', name: 'valor_liquido' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                   url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json'
                }
            });
        }
    });

    // ============================================================
    // BOTÕES DE AÇÃO NAS FATURAS
    // ============================================================

    // EXCLUIR FATURA
    // CORREÇÃO: Usamos 'document' para delegar o clique
    $(document).on('click', '#faturas-geradas-table tbody .btn-excluir', function() {
        // e.preventDefault(); // Não é mais necessário com <button>
        console.log('🔥 Clique detectado no botão excluir!');

        var fatura_id = $(this).data('id');
        var row = $(this).closest('tr');

        // --- INÍCIO DA CORREÇÃO (Sintaxe SweetAlert v8) ---
        Swal.fire({
            title: 'Tem certeza?',
            text: "Isso excluirá a fatura #" + fatura_id + " e reabrirá as transações vinculadas.",
            type: 'warning', // CORREÇÃO: 'icon' -> 'type'
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            console.log('🧩 Resultado do Swal:', result);

            // CORREÇÃO: 'result.isConfirmed' -> 'result.value'
            // O seu log 'Resultado do Swal: {value: true}' confirma que é 'result.value'
            if (result.value) { 
                console.log('🚀 Enviando AJAX para excluir fatura', fatura_id);

                $.ajax({
                    // Usamos a URL helper do Laravel para segurança
                    url: '{{ url('admin/faturamento/faturas') }}/' + fatura_id, 
                    type: 'DELETE',
                    success: function(response) {
                        Swal.fire('Excluído!', response.message, 'success');
                        // Remove a linha da tabela dinamicamente
                        $('#faturas-geradas-table').DataTable().row(row).remove().draw(false);
                        atualizarAbaGeral(); // Atualiza os totais da Aba 1
                    },
                    error: function(xhr) {
                        Swal.fire('Erro!', xhr.responseJSON?.message || 'Falha ao excluir.', 'error');
                    }
                });
            }
        });
        // --- FIM DA CORREÇÃO ---
    });


    // MARCAR COMO RECEBIDA
    $(document).on('click', '#faturas-geradas-table tbody .btn-receber', function() {
        var fatura_id = $(this).data('id');

        $.ajax({
            url: '{{ url('admin/faturamento/faturas') }}/' + fatura_id + '/receber',
            type: 'POST',
            success: function(response) {
                Swal.fire('Atualizado!', response.message, 'success');
                $('#faturas-geradas-table').DataTable().ajax.reload(null, false);
            },
            error: function(xhr) {
                Swal.fire('Erro!', xhr.responseJSON?.message || 'Falha ao atualizar.', 'error');
            }
        });
    });

    // EDITAR FATURA
    $(document).on('click', '#faturas-geradas-table tbody .btn-editar', function() {
        var fatura_id = $(this).data('id');
        Swal.fire('Em breve!', 'A edição de fatura será implementada aqui.', 'info');
    });

    // ============================================================
    // ABA 3: TRANSAÇÕES
    // ============================================================
    $('#tab-transacoes-link').on('shown.bs.tab', function () {
        if (tableTransacoes === null) {
            tableTransacoes = $('#transacoes-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('faturamento.getTransacoes') }}',
                    data: function (d) {
                        d.cliente_id = globalData.cliente_id;
                        d.periodo = globalData.periodo;
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'faturada', name: 'fatura_id' },
                    { data: 'data_transacao', name: 'data_transacao' },
                    { data: 'credenciado_nome', name: 'credenciado.razao_social' },
                    { data: 'grupo_nome', name: 'veiculo.grupo.grupoPai.nome' },
                    { data: 'subgrupo_nome', name: 'veiculo.grupo.nome' },
                    { data: 'produto_nome', name: 'produto.nome' },
                    { data: 'valor_total', name: 'valor_total' },
                    { data: 'aliquota_ir', name: 'aliquota_ir', orderable: false, searchable: false },
                    { data: 'valor_ir', name: 'valor_ir', orderable: false, searchable: false },
                    { data: 'placa', name: 'veiculo.placa' },
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json'
                }
            });
        }
    });

    // ============================================================
    // EVENTOS DE ATUALIZAÇÃO (Recarregar Aba 1)
    // ============================================================
    $(document).on('faturaGerada', function() {
        if (tableFaturas) tableFaturas.ajax.reload(null, false);
        if (tableTransacoes) tableTransacoes.ajax.reload(null, false);
        atualizarAbaGeral();
    });

    // ============================================================
    // FUNÇÃO DE ATUALIZAÇÃO GERAL (Aba 1)
    // ============================================================
    function atualizarAbaGeral() {
        $.get(globalData.ajax_resumo_url, globalData, function(data) {
            $('#total-faturado-box .info-box-number').text(data.faturado);
            $('#total-bruto-box .info-box-number').text(data.bruto);
            $('#total-ir-box .info-box-number').text(data.ir);
            $('#total-liquido-box .info-box-number').text(data.liquido);
        });
    }

    // Inicializa na aba "Geral"
    $('#tab-geral-link').trigger('click');
});
</script>
@stop