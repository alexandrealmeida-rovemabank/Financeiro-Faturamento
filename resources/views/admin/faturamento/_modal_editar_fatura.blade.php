{{-- Modal para Edição de Fatura e Refaturamento --}}
<div class="modal fade" id="modalEditarFatura" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="form-editar-fatura" onsubmit="return false;">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-fatura-id">
                
                <div class="modal-header">
                    <h5 class="modal-title">Editar Fatura <span id="edit-fatura-titulo-id"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            onclick="$('#modalEditarFatura').modal('hide');">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div id="edit-fatura-alert-container"></div>
                    
                    {{-- Seção de Edição Principal --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-data-vencimento">Data de Vencimento</label>
                                <input type="date" id="edit-data-vencimento" name="data_vencimento" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit-nota-fiscal">Número da Nota Fiscal (NF-e)</label>
                                <input type="text" id="edit-nota-fiscal" name="nota_fiscal" class="form-control" placeholder="Nº da NF-e">
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        @can('edit faturamento')
                            <button type="button" id="btn-salvar-edicao-fatura" class="btn btn-success">
                                <i class="fa fa-save"></i> Salvar Alterações
                            </button>
                        @endcan
                    </div>

                    {{-- Seção de Refaturamento (só aparece se paga) --}}
                    <div id="secao-refaturamento" class="mt-4" style="display:none; border-top: 2px dashed #ccc; padding-top: 20px;">
                        <h5 class="text-danger"><i class="fa fa-exclamation-triangle"></i> Zona de Risco: Refaturamento</h5>
                        <p class="text-muted">A fatura está marcada como 'Recebida'. Se precisar reabri-la para correções financeiras, use o formulário abaixo.</p>
                        
                        <div class="form-group">
                            <label for="refaturar-motivo">Motivo da Reabertura (Obrigatório)</label>
                            <textarea id="refaturar-motivo" class="form-control" rows="3" placeholder="Ex: Pagamento estornado pelo banco..." minlength="10"></textarea>
                            <small class="text-danger" id="refaturar-motivo-erro" style="display:none;">O motivo é obrigatório (mín. 10 caracteres).</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="refaturar-novo-status">Definir Status como:</label>
                            <select id="refaturar-novo-status" class="form-control">
                                <option value="aguardando_pagamento">Aguardando Pagamento (Reabertura total)</option>
                                <option value="recebida_parcial">Recebida Parcial (Se já houver pagamentos)</option>
                            </select>
                        </div>
                        
                        @can('edit faturamento')
                            <button type="button" id="btn-confirmar-reabertura" class="btn btn-danger">
                                <i class="fa fa-undo"></i> Confirmar Reabertura da Fatura
                            </button>
                        @endcan
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>