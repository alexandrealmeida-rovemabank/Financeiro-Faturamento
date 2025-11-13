<div class="modal fade" id="modalVerComprovantes" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Comprovantes da Fatura <span id="comprovante-fatura-titulo-id"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                    onclick="$('#modalVerComprovantes').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                {{-- Container da Lista de Pagamentos (será carregado via AJAX) --}}
                <div id="lista-comprovantes-container">
                    <div class="text-center p-3">
                        <i class="fa fa-spinner fa-spin"></i> Carregando...
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#modalVerComprovantes').modal('hide');">Fechar</button>
            </div>
        </div>
    </div>
</div>