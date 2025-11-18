{{-- _modal_gerar_fatura.blade.php --}}

<div class="modal fade" id="modalGerarFatura" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="form-gerar-fatura">
                <div class="modal-header">
                    <h5 class="modal-title">Gerar Nova Fatura</h5>
                    <button type="button" class="close" data-dismiss="modal" onclick="$('#modalGerarFatura').modal('hide');" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                    <input type="hidden" name="periodo" value="{{ $periodo }}">

                    {{-- Alertas de Erro e Aviso --}}
                    <div id="modal-alert-container"></div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de Geração</label>
                                <select name="tipo_geracao" id="tipo_geracao" class="form-control">
                                    <option value="Total" selected>Total (Consolidado)</option>
                                    <option value="Fracionada">Fracionada (por filtro)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nota Fiscal (Opcional)</label>
                                <input type="text" name="nota_fiscal" class="form-control" placeholder="Nº da NF-e">
                            </div>
                        </div>
                    </div>
                    
                    {{-- Filtros Fracionados --}}
                    <div id="filtros-fracionados" style="display:none; border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: #f9f9f9;">

                    {{-- Lógica de Texto (Público/Privado) movida para o JS para consistência --}}
                    <p class="text-muted small" id="filtro-helper-text">
                        Modo Fracionado: Selecione <strong>pelo menos um</strong> filtro de escopo. Os filtros são cumulativos e em cascata.
                    </p>
                    
                        {{-- LINHA 1: FILTROS DE ESCOPO --}}
                        <div class="row">
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filtro: Grupo(s)</label>
                                    <select name="grupo_id[]" id="filtro_grupo_id" class="form-control select2-modal" style="width:100%; background: #413434ff;" multiple="multiple">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filtro: Subgrupo(s)</label>
                                    <select name="subgrupo_id[]" id="filtro_subgrupo_id" class="form-control select2-modal" style="width:100%;" multiple="multiple">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filtro: Empenho(s)</label>
                                    <select name="empenho_id[]" id="filtro_empenho_id" class="form-control select2-modal" style="width:100%;" multiple="multiple">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filtro: Contrato(s) (Opcional)</label>
                                    <select name="contrato_id[]" id="filtro_contrato_id" class="form-control select2-modal" style="width:100%;" multiple="multiple">
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- LINHA 2: CÁLCULO DE VALORES E LIMITES --}}
                        <hr>
                        <div class="row" id="limites-info-box" style="font-size: 0.9rem;">
                            <div class="col-md-12 text-center text-muted" id="limites-loading-spinner">
                                <i class="fas fa-spinner fa-spin"></i> Calculando...
                            </div>
                            
                            {{-- DETALHES DO CÁLCULO --}}
                            <div class="col-6">
                                <strong>Total Pendente (Pool Filtrado):</strong>
                                <span id="display-valor-filtrado" class="d-block font-weight-bold" style="font-size: 1.1rem;">R$ 0,00</span>
                                <small class="text-muted">(Soma das transações que atendem a <strong>TODOS</strong> os filtros acima)</small>
                            </div>
                            
                            <div class="col-6">
                                <strong>Limite Aplicável (Hierarquia):</strong>
                                <span id="display-limite-aplicavel" class="d-block font-weight-bold text-danger" style="font-size: 1.1rem;">R$ 0,00</span>
                                <small class="text-muted">(Saldo pendente do filtro <strong>mais granular</strong>: Empenho > Subgrupo > Grupo)</small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Valor a Faturar</label>
                                <input type="text" id="valor_a_faturar" name="valor_fatura_calculado" class="form-control form-control-lg" 
                                       placeholder="R$ 0,00"
                                       style="font-size: 1.5rem; font-weight: bold;">
                                <small id="valor-calculado-info" class="text-info" style="display: none;">
                                    <i class="fas fa-info-circle"></i> O valor não pode exceder o "Total Pendente" nem o "Limite Aplicável".
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Data de Vencimento</label>
                                <input type="date" name="data_vencimento" id="data_vencimento" class="form-control" 
                                       value="{{ Carbon\Carbon::now()->addDays($parametrosAtivos['dias_vencimento'])->format('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#modalGerarFatura').modal('hide');">Cancelar</button>
                    <button type="button" id="btn-confirmar-geracao" class="btn btn-success" disabled>
                        <i class="fa fa-check"></i> Confirmar e Gerar Fatura
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('css')
<style>
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        
        color: #030202;

    }
    .modal-content{
        width: 1000px;
    }
</style>
@endpush
@push('js')
<script>
$(document).ready(function() {
    
    // --- Variáveis Globais do Modal ---
    var modal = $('#modalGerarFatura');
    var form = $('#form-gerar-fatura');
    var container = $('#faturamento-container');
    var btnConfirmar = $('#btn-confirmar-geracao');
    var alertContainer = $('#modal-alert-container');
    var spinner = $('#limites-loading-spinner');
    var inputValorFatura = $('#valor_a_faturar');
    
    // Selects
    var selectGrupo = $('#filtro_grupo_id');
    var selectSubgrupo = $('#filtro_subgrupo_id');
    var selectEmpenho = $('#filtro_empenho_id');
    var selectContrato = $('#filtro_contrato_id');
    
    var clienteId = container.data('cliente-id');
    var periodo = container.data('periodo');
    var vencimentoDefault = container.data('vencimento-auto');
    var isPublico = container.data('is-publico') === true; // Garante boolean
    
    // --- Flags e Controles de AJAX ---
    var xhrCalculo = null;
    var xhrGrupos = null;
    var xhrEmpenhos = null;
    var isUpdatingSelects = false; // Flag para evitar loops de 'change'
    
    var valorFiltrado = 0;
    var limiteAplicavel = 0;
    var maxValorPermitido = 0;
    
    // ============================================================
    // Funções Helper de Formatação de Moeda
    // ============================================================
    function formatNumberToBR(value) {
        if (isNaN(value) || value === null || value === Infinity) value = 0;
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    }

    function parseBRToNumber(value) {
        if (typeof value !== 'string' || value.length === 0) return 0;
        let valorNumerico = value.replace('R$', '').trim().replace(/\./g, '').replace(',', '.');
        return parseFloat(valorNumerico) || 0;
    }
    
    inputValorFatura.on('input', function (e) {
        let valor = $(this).val();
        let digitos = valor.replace(/\D/g, '');
        
        if (digitos.length === 0) {
            $(this).val('');
            validarValorDigitado();
            return;
        }
        let valorNum = parseInt(digitos, 10);
        let valorFormatado = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2
        }).format(valorNum / 100);

        $(this).val(valorFormatado);
    });

    // ============================================================
    // Funções de Abertura/Reset e UI
    // ============================================================
    $('.select2-modal').select2({
        dropdownParent: modal,
        width: '100%',
        placeholder: '-- Opcional --',
        closeOnSelect: false 
    });

    modal.on('shown.bs.modal', function () {
        form[0].reset();
        $('#tipo_geracao').val('Total').trigger('change.select2');
        $('#data_vencimento').val(vencimentoDefault);
        inputValorFatura.val('');
        $('#valor-calculado-info').hide();
        limparAlertas();
        btnConfirmar.prop('disabled', true);

        // Reseta todos os selects
        resetarSelect2(selectContrato, 'Carregando...', true);
        resetarSelect2(selectEmpenho, 'Carregando...', true);
        resetarSelect2(selectGrupo, 'Carregando...', true);
        resetarSelect2(selectSubgrupo, 'Carregando...', true);
        
        // Carrega contratos (não depende de nada)
        carregarContratos();
        
        // Carrega todos os filtros em cascata e, quando terminar, calcula o valor total
        atualizarFiltrosCascata(true); // true = é a carga inicial

        var contratoFormGroup = selectContrato.closest('.form-group');
        var empenhoFormGroup = selectEmpenho.closest('.form-group');
        var helperText = $('#filtro-helper-text');

        if (isPublico) {
            contratoFormGroup.show();
            empenhoFormGroup.show();
            helperText.html('Modo Fracionado(Público): Selecione <strong>pelo menos um</strong> filtro de escopo (Grupo, Subgrupo ou Empenho). Os filtros são cumulativos e em cascata.');
        } else {
            contratoFormGroup.show();
            empenhoFormGroup.hide();
            helperText.html('Modo Fracionado (Privado): Selecione <strong>pelo menos um</strong> filtro de escopo (Grupo, Subgrupo). Os filtros são cumulativos e em cascata.');
        }
    });

    function resetarSelect2(selector, placeholder, disabled = false) {
        $(selector)
            .empty()
            .val(null) // Limpa seleção
            .prop('disabled', disabled)
            .trigger('change.select2');
    }

    function mostrarAlerta(tipo, mensagem) {
        var icone = (tipo == 'danger') ? 'fa-exclamation-triangle' : 'fa-info-circle';
        var alerta = `<div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
                        <i class="fas ${icone} mr-2"></i> ${mensagem}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                      </div>`;
        alertContainer.append(alerta);
    }
    
    function limparAlertas() {
        alertContainer.empty();
    }

    $('#tipo_geracao').on('change', function() {
        var tipo = $(this).val();
        var filtrosDiv = $('#filtros-fracionados');
        
        if (tipo == 'Fracionada') {
            filtrosDiv.slideDown();
            inputValorFatura.prop('readonly', false);
            $('#valor-calculado-info').show();
            inputValorFatura.val('');
        } else {
            filtrosDiv.slideUp();
            limparAlertas();
            inputValorFatura.prop('readonly', true);
            $('#valor-calculado-info').hide();
        }
        
        recalcularValores();
    });

    // ============================================================
    // AJAX Loaders (COM CASCATA) - ROBUSTO
    // ============================================================

    function carregarContratos() {
        $.get('{{ route('faturamento.getContratos') }}', { cliente_id: clienteId }, function(data) {
            selectContrato.empty(); // Limpa
            $.each(data, function(i, item) {
                selectContrato.append($('<option>', { value: item.id }).text(item.numero));
            });
            selectContrato.val(null).prop('disabled', false).trigger('change.select2');
        });
    }

    function atualizarFiltrosCascata(isInitialLoad = false) {
        if (xhrGrupos) xhrGrupos.abort();
        if (xhrEmpenhos) xhrEmpenhos.abort();
        
        isUpdatingSelects = true;

        // 1. Armazena as seleções atuais
        var valGrupo = selectGrupo.val();
        var valSubgrupo = selectSubgrupo.val();
        var valEmpenho = selectEmpenho.val();

        // 2. Coloca spinners (desabilita)
        selectGrupo.prop('disabled', true);
        selectSubgrupo.prop('disabled', true);
        selectEmpenho.prop('disabled', true);

        var requestsPending = 2; // Duas chamadas AJAX
        
        // 3. Função de conclusão (chamada 2x)
        function onComplete() {
            requestsPending--;
            if (requestsPending === 0) {
                // Restaura seleções e reabilita
                selectGrupo.val(valGrupo).prop('disabled', false).trigger('change.select2');
                selectSubgrupo.val(valSubgrupo).prop('disabled', false).trigger('change.select2');
                selectEmpenho.val(valEmpenho).prop('disabled', false).trigger('change.select2');
                
                isUpdatingSelects = false;
                
                // --- INÍCIO DA CORREÇÃO ---
                // O bug anterior estava aqui. A lógica `if (isInitialLoad)` impedia
                // o recálculo quando um filtro era alterado.
                // Agora, ele recalcula SEMPRE que os filtros terminam de carregar.
                recalcularValores();
                // --- FIM DA CORREÇÃO ---
            }
        }

        // 4. Request 1: Grupos/Subgrupos
        xhrGrupos = $.get('{{ route('faturamento.getGrupos') }}', { 
            cliente_id: clienteId, 
            periodo: periodo, 
            empenho_id: valEmpenho,
            grupo_id: valGrupo,
            subgrupo_id: valSubgrupo
        }).done(function(gruposResponse) {
            selectGrupo.empty();
            if (gruposResponse && gruposResponse.grupos_pais) {
                $.each(gruposResponse.grupos_pais, function(i, item) {
                    var saldoPendente = parseFloat(item.valor_pendente) || 0;
                    var texto = `${item.text} (Pendente: ${formatNumberToBR(saldoPendente)})`;
                    selectGrupo.append($('<option>', { 
                        value: item.id,
                        'data-balance': saldoPendente
                    }).text(texto));
                });
            }
            
            selectSubgrupo.empty();
            if (gruposResponse && gruposResponse.subgrupos) {
                $.each(gruposResponse.subgrupos, function(i, item) {
                    var saldoPendente = parseFloat(item.valor_pendente) || 0;
                    var texto = `${item.text} (Pendente: ${formatNumberToBR(saldoPendente)})`;
                    selectSubgrupo.append($('<option>', { 
                        value: item.id, 
                        'data-pai-id': item.grupo_pai_id,
                        'data-balance': saldoPendente
                    }).text(texto));
                });
            }

        }).fail(function(xhr) {
            if (xhr.statusText !== 'abort') {
                mostrarAlerta('danger', 'Erro ao carregar Grupos/Subgrupos.');
                selectGrupo.empty();
                selectSubgrupo.empty();
            }
        }).always(onComplete);
        
        // 5. Request 2: Empenhos
        xhrEmpenhos = $.get('{{ route('faturamento.getEmpenhos') }}', { 
            cliente_id: clienteId, 
            periodo: periodo, 
            grupo_id: valGrupo,
            subgrupo_id: valSubgrupo
        }).done(function(empenhosResponse) {
            selectEmpenho.empty();
            if (Array.isArray(empenhosResponse)) {
                $.each(empenhosResponse, function(i, item) {
                    var saldoPendente = parseFloat(item.total_pendente) || 0;
                    var texto = `Nº ${item.numero_empenho} (Pendente: ${formatNumberToBR(saldoPendente)})`;
                    selectEmpenho.append($('<option>', { 
                        value: item.id,
                        'data-balance': saldoPendente
                    }).text(texto));
                });
            }
        }).fail(function(xhr) {
            if (xhr.statusText !== 'abort') {
                mostrarAlerta('danger', 'Erro ao carregar Empenhos.');
                selectEmpenho.empty();
            }
        }).always(onComplete);
    }
    // --- FIM DA NOVA FUNÇÃO ---


    // ============================================================
    // LÓGICA DE CASCATA (EVENT LISTENERS) - ATUALIZADO
    // ============================================================
    
    $('#filtro_grupo_id, #filtro_subgrupo_id, #filtro_empenho_id').on('change', function() {
        if (isUpdatingSelects) return; // Previne loop
        atualizarFiltrosCascata(false); // false = não é a carga inicial
    });
    // --- FIM DOS NOVOS HANDLERS ---


    // ============================================================
    // LÓGICA DE CÁLCULO DE LIMITE (Regras Funcionais)
    // ============================================================
    
    inputValorFatura.on('keyup change', validarValorDigitado);
    
    function recalcularValores() {
        limparAlertas();
        spinner.show();
        btnConfirmar.prop('disabled', true);
        
        var tipoGeracao = $('#tipo_geracao').val();
        
        var grupoIds = selectGrupo.val();
        var subgrupoIds = selectSubgrupo.val();
        var empenhoIds = selectEmpenho.val();
        
        var filtroSelecionado = (grupoIds && grupoIds.length > 0) || 
                                (subgrupoIds && subgrupoIds.length > 0) || 
                                (isPublico && empenhoIds && empenhoIds.length > 0);

        if (tipoGeracao == 'Fracionada' && !filtroSelecionado) {
            var msg = isPublico 
                ? 'Modo Fracionado(Público): Selecione ao menos um filtro.'
                : 'Modo Fracionado(Privado): Selecione ao menos um filtro.';
            mostrarAlerta('danger', msg);
            spinner.hide();
            $('#display-valor-filtrado').text('R$ 0,00');
            $('#display-limite-aplicavel').text('R$ 0,00');
            maxValorPermitido = 0;
            validarValorDigitado();
            return;
        }

        if (xhrCalculo) xhrCalculo.abort();

        xhrCalculo = $.get('{{ route('faturamento.getValorFiltrado') }}', {
            cliente_id: clienteId,
            periodo: periodo,
            empenho_id: empenhoIds,
            grupo_id: grupoIds,
            subgrupo_id: subgrupoIds,
            tipo_geracao: tipoGeracao
        }).done(function(data) {
            spinner.hide();
            
            valorFiltrado = parseFloat(data.valor_filtrado) || 0;
            limiteAplicavel = parseFloat(data.limite_aplicavel) || 0;
            
            // O valor máximo permitido é o "Pool Filtrado"
            maxValorPermitido = valorFiltrado; 
            
            $('#display-valor-filtrado').text(formatNumberToBR(valorFiltrado));
            $('#display-limite-aplicavel').text(formatNumberToBR(limiteAplicavel));
            
            if (tipoGeracao == 'Total') {
                inputValorFatura.val(formatNumberToBR(maxValorPermitido));
            }
            validarValorDigitado();
            
        }).fail(function(xhr) {
            if (xhr.statusText !== 'abort') {
                spinner.hide();
                mostrarAlerta('danger', 'Erro ao calcular valores. Tente novamente.');
                btnConfirmar.prop('disabled', true);
            }
        });
    }
    
    function validarValorDigitado() {
        limparAlertas();
        var valorDigitado = parseBRToNumber(inputValorFatura.val());
        var tipoGeracao = $('#tipo_geracao').val();
        
        var grupoIds = selectGrupo.val();
        var subgrupoIds = selectSubgrupo.val();
        var empenhoIds = selectEmpenho.val();
        var filtroSelecionado = (grupoIds && grupoIds.length > 0) || 
                                (subgrupoIds && subgrupoIds.length > 0) || 
                                (isPublico && empenhoIds && empenhoIds.length > 0);

        if (tipoGeracao == 'Fracionada' && !filtroSelecionado) {
            var msg = isPublico ? 'Selecione ao menos um filtro.' : 'Selecione ao menos um filtro.';
            mostrarAlerta('danger', msg);
            btnConfirmar.prop('disabled', true);
            return;
        }
        
        if (valorDigitado <= 0) {
            if (tipoGeracao == 'Fracionada') {
                 mostrarAlerta('info', 'Digite um valor a faturar.');
            } else if (maxValorPermitido <= 0) {
                 mostrarAlerta('info', 'Não há transações pendentes para faturar no modo Total.');
            }
            btnConfirmar.prop('disabled', true);
            return;
        }
        
        if (valorDigitado > (maxValorPermitido + 0.001)) {
            mostrarAlerta('danger', `Valor excede o Total Pendente para estes filtros (${formatNumberToBR(maxValorPermitido)}).`);
            btnConfirmar.prop('disabled', true);
            return;
        }
        
        // Validação de Limite Aplicável (Hierarquia)
        if (tipoGeracao == 'Fracionada' && valorDigitado > (limiteAplicavel + 0.001)) {
             mostrarAlerta('warning', `Atenção: O valor excede o Limite Aplicável da hierarquia (${formatNumberToBR(limiteAplicavel)}).`);
             // Não desabilita o botão, mas avisa o usuário.
        }

        btnConfirmar.prop('disabled', false);
    }
  // ============================================================
    // Ação: Botão Confirmar Geração
    // ============================================================
    btnConfirmar.on('click', function(e) {
        e.preventDefault();
        var btn = $(this);
        
        validarValorDigitado();
        if (btn.is(':disabled')) {
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Gerando...');
        limparAlertas();

        var formData = form.serializeArray();
        var valorNumerico = parseBRToNumber(inputValorFatura.val());
        
        var valorInput = formData.find(item => item.name === 'valor_fatura_calculado');
        if (valorInput) {
            valorInput.value = valorNumerico;
        } else {
            formData.push({name: "valor_fatura_calculado", value: valorNumerico});
        }

        $.ajax({
            url: '{{ route('faturamento.gerarFatura') }}',
            type: 'POST',
            data: $.param(formData), 
            success: function(response) {
                modal.modal('hide');
                Swal.fire('Sucesso!', response.message, 'success');
                $(document).trigger('faturaGerada'); 
            },
            error: function(xhr) {
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    let msg = xhr.responseJSON.message;
                    Swal.fire('Erro!', msg, 'error');
                } else {
                    Swal.fire('Erro!', 'Falha ao gerar fatura.', 'error');
                }
            },
            complete: function() {
                 btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirmar e Gerar Fatura');
                 
                 // Recarrega os filtros para refletir os novos pendentes
                 atualizarFiltrosCascata(false);
            }
        });
    });
});
</script>
@endpush