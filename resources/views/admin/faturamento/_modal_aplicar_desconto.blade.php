{{-- Modal para Aplicar Desconto Manual --}}
<div class="modal fade" id="modalAplicarDesconto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="form-aplicar-desconto" onsubmit="return false;">
                @csrf
                <input type="hidden" id="desconto-fatura-id">

                <div class="modal-header">
                    <h5 class="modal-title">Aplicar Desconto <span id="desconto-fatura-titulo-id"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        onclick="$('#modalAplicarDesconto').modal('hide');">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="desconto-fatura-alert-container"></div>

                    <div class="alert alert-info" style="top: 145px;">
                        <strong>Valor Líquido Atual:</strong> <span id=" desconto-valor-liquido">R$ 0,00</span><br>
                        <strong class="text-danger">Saldo Pendente Atual:</strong> <span id="desconto-saldo-pendente" class="font-weight-bold">R$ 0,00</span>
                    </div>

                    {{-- Formulário de Adição --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="desconto-tipo">Tipo de Desconto</label>
                                <select id="desconto-tipo" name="tipo" class="form-control">
                                    <option value="fixo" selected>Valor Fixo (R$)</option>
                                    <option value="percentual">Percentual (%)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="desconto-valor">Valor</label>
                                <input type="number" id="desconto-valor" name="valor" class="form-control" step="0.01" min="0.01" placeholder="0,00" required>
                                <small class="text-muted" id="desconto-valor-helper">Valor em R$ (Ex: 100.50)</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="desconto-justificativa">Justificativa (Opcional)</label>
                                <input type="text" id="desconto-justificativa" name="justificativa" class="form-control" placeholder="Ex: Bônus">
                            </div>
                        </div>
                    </div>
                    <div class="text-right mb-3">
                        @can('addDesconto faturamento')
                            <button type="button" id="btn-salvar-desconto" class="btn btn-success">
                                <i class="fa fa-plus"></i> Adicionar Desconto
                            </button>
                        @endcan
                    </div>

                    {{-- Container da Lista de Descontos --}}
                    <div id="lista-descontos-container">
                        <div class="text-center p-3">
                            <i class="fa fa-spinner fa-spin"></i> Carregando descontos...
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#modalAplicarDesconto').modal('hide');">Fechar</button>
                </div>
            </form>
        </div>
    </div>
</div>