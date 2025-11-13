{{-- resources/views/admin/faturamento/_modal_registrar_pagamento.blade.php --}}

<div class="modal fade" id="modalRegistrarPagamento" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            {{-- Usamos 'multipart/form-data' para o upload --}}
            <form id="form-registrar-pagamento" onsubmit="return false;" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="pay-fatura-id">

                <div class="modal-header">
                    <h5 class="modal-title">Registrar Pagamento <span id="pay-fatura-titulo-id"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        onclick="$('#modalRegistrarPagamento').modal('hide');">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="pay-fatura-alert-container"></div>

                    {{-- Info Box Saldo --}}
                    <div class="alert alert-info">
                        <strong>Valor Líquido da Fatura:</strong> <span id="pay-valor-liquido">R$ 0,00</span><br>
                        <strong class="text-danger">Saldo Pendente:</strong> <span id="pay-saldo-pendente" class="font-weight-bold">R$ 0,00</span>
                    </div>

                    {{-- Formulário de Adição --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pay-data-pagamento">Data do Pagamento</label>
                                <input type="date" id="pay-data-pagamento" name="data_pagamento" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pay-valor-pago">Valor Pago</label>
                                <input type="number" id="pay-valor-pago" name="valor_pago" class="form-control" step="0.01" min="0.01" placeholder="0,00" required>
                                <small class="text-muted">O valor máximo é o saldo pendente.</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pay-comprovante">Comprovante (Opcional - PDF, JPG, PNG)</label>
                        <input type="file" id="pay-comprovante" name="comprovante" class="form-control-file">
                    </div>

                    <div class="text-right mb-3">
                        <button type="button" id="btn-salvar-pagamento" class="btn btn-success">
                            <i class="fa fa-plus"></i> Adicionar Pagamento
                        </button>
                    </div>


                    <div id="lista-pagamentos-container">
                        <div class="text-center p-3">
                            <i class="fa fa-spinner fa-spin"></i> Carregando pagamentos...
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#modalRegistrarPagamento').modal('hide');">Fechar</button>
                </div>
            </form>
        </div>
    </div>
</div>