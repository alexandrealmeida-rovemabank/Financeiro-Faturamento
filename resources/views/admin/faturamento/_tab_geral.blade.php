@php
// Variáveis para o JavaScript (para não poluir o HTML)
$ajaxUrl = route('faturamento.getSubgrupos');
$clienteId = $cliente->id;
$periodo = $periodo;
@endphp

<div class="row">
    {{-- COLUNA ESQUERDA --}}
    <div class="col-md-4">
        {{-- PARÂMETROS ATIVOS --}}
        <div class="card card-outline card-info mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    Parâmetros Ativos
                    <span class="text-muted">({{ $parametrosAtivos['fonte'] }})</span>
                </h3>
            </div>
            <div class="card-body">
                {{-- Exibe o valor correto que veio do controller --}}
                <p class="mb-1"><strong>Dias p/ Vencimento:</strong> {{ $parametrosAtivos['dias_vencimento'] }} dias</p>
                <p class="mb-1"><strong>Isento de IR:</strong> {{ $parametrosAtivos['isento_ir'] ? 'Sim' : 'Não' }}</p>
                <p class="mb-0"><strong>Descontar IR na Fatura:</strong> {{ $parametrosAtivos['descontar_ir_fatura'] ? 'Sim' : 'Não' }}</p>
                <small class="text-muted d-block mt-2">
                    (O desconto de IR é calculado por transação, com base nas alíquotas por Organização x Categoria de Produto.)
                </small>
            </div>
        </div>

        {{-- OBSERVAÇÕES GERAIS --}}
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">Observações Gerais</h3>
            </div>
            <form id="form-observacoes">
                <div class="card-body">
                    <input type="hidden" name="faturamento_periodo_id" value="{{ $faturamentoPeriodo->id }}">
                    <textarea name="observacoes" class="form-control" rows="6" placeholder="Digite observações sobre o faturamento...">{{ $faturamentoPeriodo->observacoes }}</textarea>
                </div>
                <div class="card-footer text-right">
                    <button type="button" id="btn-salvar-obs" class="btn btn-success btn-sm">
                        <i class="fas fa-save"></i> Salvar Observações
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- COLUNA DIREITA --}}
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Resumo de Transações</h3>
            </div>

            <div class="card-body">
                {{-- TOTAIS (PENDENTES E FATURADOS) --}}
                <div class="row text-center mb-4">
                    {{-- Valor Já Faturado --}}
                    <div class="col-md-6">
                        <div class="info-box bg-secondary" id="total-faturado-box">
                            <span class="info-box-icon"><i class="fas fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Valor Já Faturado</span>
                                <span class="info-box-number">R$ {{ number_format($totaisPendentes['faturado'], 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-info" id="total-bruto-box">
                            <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Valor Bruto Pendente</span>
                                <span class="info-box-number">R$ {{ number_format($totaisPendentes['bruto'], 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-danger" id="total-ir-box">
                            <span class="info-box-icon"><i class="fas fa-receipt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Desconto IR Pendente</span>
                                <span class="info-box-number">R$ {{ number_format($totaisPendentes['ir'], 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-success" id="total-liquido-box">
                            <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Valor Líquido Pendente</span>
                                <span class="info-box-number">R$ {{ number_format($totaisPendentes['liquido'], 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Título alterado para refletir que são os totais do período --}}
                <h4 class="mb-3">Agrupamentos Totais do Período</h4>
                <div class="row">
                    {{-- Por Unidade (Mostra totais do período) --}}
                    <div class="col-md-6">
                        <h5>Por Unidade</h5>
                        <table class="table table-sm table-striped">
                            @forelse($totaisPorUnidade as $item)
                                <tr>
                                    <td>{{ $item->nome }}</td>
                                    <td class="text-right">R$ {{ number_format($item->valor_bruto, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">Nenhum dado.</td></tr>
                            @endforelse
                        </table>
                    </div>

                    {{-- Por Empenho (Mostra totais do período) --}}
                    <div class="col-md-6">
                        <h5>Por Empenho</h5>
                        <table class="table table-sm table-striped">
                            @forelse($totaisPorEmpenho as $item)
                                <tr>
                                    <td>{{ $item->nome }}</td>
                                    <td class="text-right">R$ {{ number_format($item->valor_bruto, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">Nenhum dado.</td></tr>
                            @endforelse
                        </table>
                    </div>

                    {{-- Por Grupo (Pai) - DATATABLES (Mostra totais do período) --}}
                    <div class="col-md-12 mt-3">
                        <h5>Por Grupo (Pai)</h5>
                        <table id="grupos-pai-table" class="table table-sm table-hover" style="width:100%;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 30px;"></th>
                                    <th>Grupo Pai</th>
                                    <th class="text-right">Valor Bruto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($totaisPorGrupo as $item)
                                    <tr data-id="{{ $item->grupo_pai_id }}">
                                        <td class="details-control text-center">
                                            @if($item->subgrupos_count > 0)
                                                <i class="fas fa-chevron-right text-primary"></i>
                                            @endif
                                        </td>
                                        <td>{{ $item->nome }}</td>
                                        <td class="text-right">R$ {{ number_format($item->valor_bruto, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">Nenhum dado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<style>
    td.details-control { cursor: pointer; }
    tr.details td.details-control i { transform: rotate(90deg); transition: transform 0.2s ease; }
    tr.details-row > td { padding: 0 !important; border-top: none !important; }
</style>
<script>
$(document).ready(function() {
    // Este script é re-anexado pela view 'show.blade.php'
    // Mas para o carregamento inicial, ele é necessário aqui.
    if ($('#grupos-pai-table').length && !$.fn.DataTable.isDataTable('#grupos-pai-table')) {
        
        $('#btn-salvar-obs').on('click', function() {
            var data = $('#form-observacoes').serialize();
            $.ajax({
                url: '{{ route('faturamento.updateObservacoes') }}',
                type: 'POST',
                data: data,
                success: function(response) { Swal.fire('Sucesso!', response.message, 'success'); },
                error: function(xhr) { Swal.fire('Erro!', xhr.responseJSON.message, 'error'); }
            });
        });

        var tableGrupos = $('#grupos-pai-table').DataTable({
            "paging": false, "lengthChange": false, "searching": false, "ordering": false,
            "info": false, "autoWidth": false, "responsive": true,
            "language": { "emptyTable": "Nenhum dado." }
        });

        function format(html) { return html; }

        $('#grupos-pai-table tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = tableGrupos.row(tr);
            var icon = $(this).find('i');
            var grupo_pai_id = tr.data('id');
            if (!icon.length) return;

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('details');
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
            } else {
                icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
                tr.addClass('details');
                row.child('<div class="p-3 text-center"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>').show();
                tr.next().addClass('details-row');

                $.ajax({
                    url: '{{ $ajaxUrl }}', // Rota definida no topo do blade
                    type: 'GET',
                    data: {
                        cliente_id: '{{ $clienteId }}',
                        periodo: '{{ $periodo }}',
                        grupo_pai_id: grupo_pai_id
                    },
                    success: function(response) {
                        if (row.child.isShown()) { row.child(format(response)).show(); tr.next().addClass('details-row'); }
                    },
                    error: function() { if (row.child.isShown()) { row.child('<div class="p-3 text-center text-danger">Erro ao carregar dados.</div>').show(); tr.next().addClass('details-row'); } }
                });
            }
        });
    }
});
</script>
@endpush