{{-- resources/views/admin/faturamento/_modal_editar_observacao.blade.php --}}
<div class="modal fade" id="modalEditarObservacao" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="form-edit-observacao" onsubmit="return false;">
                @csrf
                @method('PUT')
                <input type="hidden" name="fatura_id" id="edit-obs-fatura-id">

                <div class="modal-header">
                    <h5 class="modal-title">Editar Observações da Fatura</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        onclick="$('#modalEditarObservacao').modal('hide');">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="modal-obs-alert-container"></div>

                    <div class="form-group">
                        <label for="edit-obs-textarea">Texto da Observação</label>

                        <!-- Agrupando textarea + botão copiar em linha -->
                        <div class="d-flex">
                            <textarea class="form-control" name="observacoes" id="edit-obs-textarea" rows="10" placeholder="Digite aqui..."></textarea>

                            <!-- botão copiar (tipo button evita submit) -->
                            <div class="ms-2 d-flex align-items-start">
                                <button type="button" id="btn-copiar-observacao" class="btn btn-outline-secondary btn-sm" title="Copiar para área de transferência">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <!-- botão Cancelar: data-dismiss e onclick para forçar fechamento -->
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        onclick="$('#modalEditarObservacao').modal('hide');">
                        Cancelar
                    </button>

                    <!-- botão Salvar permanece com comportamento AJAX (handler já existe no seu JS) -->
                    @can('edit faturamento')
                        <button type="button" id="btn-salvar-observacao" class="btn btn-success">
                            <i class="fa fa-save"></i> Salvar Alterações
                        </button>
                    @endcan
                </div>
            </form>
        </div>
    </div>
</div>