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
                                    <label>Filtro: Grupo (Hierarquia 1)</label>
                                    {{-- Este é um filtro em cascata --}}
                                    <select name="grupo_id" id="filtro_grupo_id" class="form-control select2-modal filtro-cascata" style="width:100%;">
                                        <option value="">-- Carregando... --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filtro: Subgrupo (Hierarquia 2)</label>
                                    {{-- Este é um filtro em cascata --}}
                                    <select name="subgrupo_id" id="filtro_subgrupo_id" class="form-control select2-modal filtro-cascata" style="width:100%;">
                                        <option value="">-- Carregando... --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filtro: Empenho (Hierarquia 3)</label>
                                    {{-- Este é um filtro em cascata --}}
                                    <select name="empenho_id" id="filtro_empenho_id" class="form-control select2-modal filtro-cascata" style="width:100%;">
                                        <option value="">-- Carregando... --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filtro: Contrato (Opcional)</label>
                                    {{-- Este é um filtro em cascata --}}
                                    <select name="contrato_id" id="filtro_contrato_id" class="form-control select2-modal filtro-cascata" style="width:100%;">
                                        <option value="">-- Carregando... --</option>
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
                                {{-- CAMPO DE VALOR AGORA É EDITÁVEL --}}
                                <label>Valor a Faturar</label>
                                
                                {{-- ======================================================= --}}
                                {{-- CORREÇÃO 1: Mudar para type="text" para aceitar máscara --}}
                                {{-- ======================================================= --}}
                                <input type="text" id="valor_a_faturar" name="valor_fatura_calculado" class="form-control form-control-lg" 
                                       placeholder="R$ 0,00"
                                       style="font-size: 1.5rem; font-weight: bold;">
                                {{-- FIM DA CORREÇÃO 1 --}}

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
    
    // Dados do Cliente
    var clienteId = container.data('cliente-id');
    var periodo = container.data('periodo');
    var vencimentoDefault = container.data('vencimento-auto');
    var isPublico = container.data('is-publico') === 'true' || container.data('is-publico') === true; // Garante boolean
    
    // Cache de dados AJAX
    var cacheGrupos = null; // Armazena a resposta de getGrupos
    var xhrCalculo = null; // Para abortar requisições de cálculo em andamento
    var xhrEmpenhos = null;
    var xhrGrupos = null;

    // Estado dos Limites (Regra Funcional)
    var valorFiltrado = 0; // Total do Pool (Lógica E)
    var limiteAplicavel = 0; // Limite do Escopo (Lógica Hierárquica)
    var maxValorPermitido = 0; // O menor entre os dois acima
    

    // ============================================================
    // CORREÇÃO 2: Funções Helper de Formatação de Moeda
    // ============================================================

    /**
     * Pega um NÚMERO (ex: 1234.50) e formata para string BRL (ex: "R$ 1.234,50")
     */
    function formatNumberToBR(value) {
        if (isNaN(value) || value === null || value === Infinity) value = 0;
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    }

    /**
     * Pega uma STRING BRL (ex: "R$ 1.234,50") e formata para NÚMERO (ex: 1234.50)
     */
    function parseBRToNumber(value) {
        if (typeof value !== 'string' || value.length === 0) return 0;
        
        let valorNumerico = value.replace('R$', '')     // Remove "R$"
                                 .trim()               // Remove espaços
                                 .replace(/\./g, '')   // Remove pontos (milhar)
                                 .replace(',', '.');   // Troca vírgula por ponto (decimal)
        
        return parseFloat(valorNumerico) || 0;
    }
    
    /**
     * Script de MÁSCARA (enquanto o usuário digita)
     * Baseia-se em centavos. Ex: 1, 2, 3, 4, 5 -> R$ 123,45
     */
    inputValorFatura.on('input', function (e) {
        let valor = $(this).val();
        let digitos = valor.replace(/\D/g, ''); // Apenas dígitos
        
        if (digitos.length === 0) {
            $(this).val(''); // Permite apagar
            validarValorDigitado(); // Revalida (vai desabilitar o botão)
            return;
        }

        // Converte para número (em centavos)
        let valorNum = parseInt(digitos, 10);
        
        // Formata como moeda (dividindo por 100)
        let valorFormatado = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
            minimumFractionDigits: 2
        }).format(valorNum / 100);

        $(this).val(valorFormatado);
    });
    // ============================================================
    // FIM DA CORREÇÃO 2
    // ============================================================


    // Inicializa os Select2 do modal
    $('.select2-modal').select2({
        dropdownParent: modal,
        width: '100%',
        allowClear: true,
        placeholder: '-- Opcional --'
    });

    // --- Lógica de Abertura/Reset do Modal ---
    modal.on('shown.bs.modal', function () {
        form[0].reset();
        $('#tipo_geracao').val('Total').trigger('change.select2'); // Dispara change.select2
        $('#data_vencimento').val(vencimentoDefault);
        inputValorFatura.val('');
        $('#valor-calculado-info').hide();
        
        limparAlertas();
        btnConfirmar.prop('disabled', true); // Começa desabilitado

        // Limpa e recarrega os Select2
        resetarSelect2('#filtro_contrato_id', 'Carregando...', true);
        resetarSelect2('#filtro_empenho_id', 'Carregando...', true);
        resetarSelect2('#filtro_grupo_id', 'Carregando...', true);
        resetarSelect2('#filtro_subgrupo_id', 'Carregando...', true);
        
        // Dispara as chamadas AJAX
        carregarContratos();
        carregarGruposESubgrupos(true); 
        carregarEmpenhos(true);

        // Oculta/Exibe campos de acordo com o tipo de cliente
        var contratoFormGroup = $('#filtro_contrato_id').closest('.form-group');
        var empenhoFormGroup = $('#filtro_empenho_id').closest('.form-group');
        var helperText = $('#filtro-helper-text');

        if (isPublico) {
            // Cliente PÚBLICO: Mostra Contrato e Empenho
            contratoFormGroup.show();
            empenhoFormGroup.show();
            helperText.html('Modo Fracionado(Público): Selecione <strong>pelo menos um</strong> filtro de escopo (Grupo, Subgrupo ou Empenho). Os filtros são cumulativos e em cascata.');
        } else {
            // Cliente PRIVADO: Oculta Contrato e Empenho
            contratoFormGroup.show(); // Contrato aparece para ambos
            empenhoFormGroup.hide();
            helperText.html('Modo Fracionado (Privado): Selecione <strong>pelo menos um</strong> filtro de escopo (Grupo, Subgrupo). Os filtros são cumulativos e em cascata.');
        }
    });

    function resetarSelect2(selector, placeholder, disabled = false) {
        var valorAtual = $(selector).val(); // Pega o valor antes de limpar
        $(selector)
            .empty()
            .append($('<option>', { value: '' }).text(placeholder))
            .val(valorAtual) // Tenta restaurar o valor (para evitar reset desnecessário)
            .prop('disabled', disabled)
            .trigger('change.select2');
    }
    
    // ** Removida a função formatCurrency() - Substituída por formatNumberToBR() **

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

    // --- Lógica de Exibição (Público/Privado) ---
    $('#tipo_geracao').on('change', function() {
        var tipo = $(this).val();
        var filtrosDiv = $('#filtros-fracionados');
        
        if (tipo == 'Fracionada') {
            filtrosDiv.slideDown();
            inputValorFatura.prop('readonly', false); // Permite digitar
            $('#valor-calculado-info').show();
            inputValorFatura.val(''); // Limpa o valor ao mudar para fracionada
        } else {
            filtrosDiv.slideUp();
            limparAlertas(); // Limpa alertas fracionados
            inputValorFatura.prop('readonly', true); // Bloqueia digitação
            $('#valor-calculado-info').hide();
        }
        
        recalcularValores(); // Recalcula ao trocar de modo
    });

    // --- AJAX Loaders (COM CASCATA) ---

    function carregarContratos() {
        $.get('{{ route('faturamento.getContratos') }}', { cliente_id: clienteId }, function(data) {
            var select = $('#filtro_contrato_id');
            var valorSelecionado = select.val(); // Salva o valor
            select.empty().append($('<option>', { value: '' }).text('-- Opcional --'));
            $.each(data, function(i, item) {
                console.log(item);
                select.append($('<option>', { value: item.id }).text(item.numero));
            });
            select.val(valorSelecionado).prop('disabled', false).trigger('change.select2');
        });
    }

    // ** ATUALIZADO: Carrega Empenhos baseado em outros filtros **
    function carregarEmpenhos(isInitialLoad = false) {
        if (xhrEmpenhos) xhrEmpenhos.abort(); // Cancela requisição anterior
        
        var select = $('#filtro_empenho_id');
        var valorSelecionado = select.val();
        resetarSelect2(select, 'Carregando Empenhos...', true);

        xhrEmpenhos = $.get('{{ route('faturamento.getEmpenhos') }}', { 
            cliente_id: clienteId, 
            periodo: periodo, 
            grupo_id: $('#filtro_grupo_id').val(),
            subgrupo_id: $('#filtro_subgrupo_id').val()
        }, function(data) {
            select.empty().append($('<option>', { value: '' }).text('-- Opcional --'));
            $.each(data, function(i, item) {
                var saldoPendente = parseFloat(item.valor_pendente) || 0;
                // =======================================================
                // CORREÇÃO 3: Usar formatNumberToBR
                // =======================================================
                var texto = `Nº ${item.numero_empenho} (Pendente: ${formatNumberToBR(saldoPendente)})`;
                // =======================================================
                select.append($('<option>', { 
                    value: item.id,
                    'data-balance': saldoPendente // Armazena o saldo
                }).text(texto));
            });
            select.val(valorSelecionado).prop('disabled', false).trigger('change.select2');
            
            if (!isInitialLoad) recalcularValores(); 
        });
    }

    // ** ATUALIZADO: Carrega Grupos/Subgrupos baseado em outros filtros **
    function carregarGruposESubgrupos(isInitialLoad = false) {
        if (xhrGrupos) xhrGrupos.abort(); // Cancela requisição anterior

        var selectGrupo = $('#filtro_grupo_id');
        var selectSubgrupo = $('#filtro_subgrupo_id');
        var valorGrupoSel = selectGrupo.val();
        var valorSubgrupoSel = selectSubgrupo.val();

        resetarSelect2(selectGrupo, 'Carregando Grupos...', true);
        resetarSelect2(selectSubgrupo, 'Carregando Subgrupos...', true);
        
        xhrGrupos = $.get('{{ route('faturamento.getGrupos') }}', { 
            cliente_id: clienteId, 
            periodo: periodo, 
            empenho_id: $('#filtro_empenho_id').val()
        }, function(data) {
            cacheGrupos = data; // Salva no cache
            
            // Popula Grupos
            selectGrupo.empty().append($('<option>', { value: '' }).text('-- Opcional --'));
            $.each(data.grupos_pais, function(i, item) {
                var saldoPendente = parseFloat(item.valor_pendente) || 0;
                // =======================================================
                // CORREÇÃO 3: Usar formatNumberToBR
                // =======================================================
                var texto = `${item.text} (Pendente: ${formatNumberToBR(saldoPendente)})`;
                // =======================================================
                selectGrupo.append($('<option>', { 
                    value: item.id,
                    'data-balance': saldoPendente // Armazena o saldo
                }).text(texto));
            });
            
            // Popula Subgrupos (mas filtra baseado no grupo JÁ selecionado)
            filtrarSubgruposDoCache(valorGrupoSel, valorSubgrupoSel);
            
            selectGrupo.val(valorGrupoSel).prop('disabled', false).trigger('change.select2');

            recalcularValores();
        }).fail(function() {
             recalcularValores(); // Roda mesmo se falhar
        });
    }
    
    // ** Helper para filtrar subgrupos do cache (lógica de cascata) **
    function filtrarSubgruposDoCache(grupoId, valorSubgrupoSel) {
         var selectSubgrupo = $('#filtro_subgrupo_id');
         if (!cacheGrupos) { // Se o cache não estiver pronto
             resetarSelect2(selectSubgrupo, 'Carregando...', true);
             return;
         }

        selectSubgrupo.empty().append($('<option>', { value: '' }).text('-- Opcional --'));
        
        // Filtra subgrupos baseado no grupoId
        var subgruposFiltrados = (!grupoId) 
            ? cacheGrupos.subgrupos // Mostra todos se "Opcional" for selecionado
            : cacheGrupos.subgrupos.filter(function(item) { return String(item.grupo_pai_id) == String(grupoId); });

        $.each(subgruposFiltrados, function(i, item) {
            var saldoPendente = parseFloat(item.valor_pendente) || 0;
            // =======================================================
            // CORREÇÃO 3: Usar formatNumberToBR
            // =======================================================
            var texto = `${item.text} (Pendente: ${formatNumberToBR(saldoPendente)})`;
            // =======================================================
            selectSubgrupo.append($('<option>', { 
                value: item.id, 
                'data-pai-id': item.grupo_pai_id,
                'data-balance': saldoPendente
            }).text(texto));
        });
        
        selectSubgrupo.val(valorSubgrupoSel).prop('disabled', false).trigger('change.select2');
    }

    // --- LÓGICA DE CASCATA (EVENT LISTENERS) ---
    
    // Ao mudar Contrato: recarrega Empenhos e Grupos/Subgrupos
    // $('#filtro_contrato_id').on('change', function() {
    //     recalcularValores();
    //     // carregarGruposESubgrupos();
    //     // carregarEmpenhos();
    // });
    
    // Ao mudar Empenho: recarrega Grupos/Subgrupos
    $('#filtro_empenho_id').on('change', function() {
        carregarGruposESubgrupos();
    });

    // Ao mudar Grupo Pai: filtra Subgrupos (via JS) e recarrega Empenhos
    $('#filtro_grupo_id').on('change', function() {
        var paiId = $(this).val();
        var subgrupoSel = $('#filtro_subgrupo_id').val();
        
        var subgrupoAtual = (cacheGrupos && cacheGrupos.subgrupos) ? cacheGrupos.subgrupos.find(s => s.id == subgrupoSel) : null;
        if (subgrupoAtual && subgrupoAtual.grupo_pai_id != paiId) {
            subgrupoSel = null;
        }

        filtrarSubgruposDoCache(paiId, subgrupoSel); 
        carregarEmpenhos();
    });
    
    // Ao mudar Subgrupo: recarrega Empenhos
    $('#filtro_subgrupo_id').on('change', function() {
        carregarEmpenhos();
    });


    // --- LÓGICA DE CÁLCULO DE LIMITE (Regras Funcionais) ---
    
    // Valida o valor digitado
    inputValorFatura.on('keyup change', validarValorDigitado);
    
    // Função principal de cálculo (chamada pelos 'change' e 'carregar')
    function recalcularValores() {
        limparAlertas();
        spinner.show();
        btnConfirmar.prop('disabled', true); // Desabilita por padrão
        
        var tipoGeracao = $('#tipo_geracao').val();
        var grupoId = $('#filtro_grupo_id').val();
        var subgrupoId = $('#filtro_subgrupo_id').val();
        var empenhoId = $('#filtro_empenho_id').val();
        
        // 1. Regra: Validação de Filtro Fracionado
        var filtroSelecionado = grupoId || subgrupoId || (isPublico && empenhoId);

        if (tipoGeracao == 'Fracionada' && !filtroSelecionado) {
            if (isPublico){
                mostrarAlerta('danger', 'Modo Fracionado(Público): Selecione ao menos um filtro (Grupo, Subgrupo ou Empenho).');
            } else {
                mostrarAlerta('danger', 'Modo Fracionado(Privado): Selecione ao menos um filtro (Grupo, Subgrupo).');
            }
            spinner.hide();
            // Reseta displays
            $('#display-valor-filtrado').text('R$ 0,00');
            $('#display-limite-aplicavel').text('R$ 0,00');
            maxValorPermitido = 0; // Zera o limite
            validarValorDigitado(); // Valida o valor (que será 0)
            return;
        }

        if (xhrCalculo) xhrCalculo.abort();

        xhrCalculo = $.get('{{ route('faturamento.getValorFiltrado') }}', {
            cliente_id: clienteId,
            periodo: periodo,
            empenho_id: empenhoId,
            grupo_id: grupoId,
            subgrupo_id: subgrupoId,
            tipo_geracao: tipoGeracao
        }).done(function(data) {
            spinner.hide();
            valorFiltrado = parseFloat(data.valor_filtrado) || 0;
            limiteAplicavel = parseFloat(data.limite_aplicavel) || 0;

            maxValorPermitido = Math.min(valorFiltrado, limiteAplicavel);
            
            // =======================================================
            // CORREÇÃO 4: Usar formatNumberToBR para exibir
            // =======================================================
            $('#display-valor-filtrado').text(formatNumberToBR(valorFiltrado));
            $('#display-limite-aplicavel').text(formatNumberToBR(limiteAplicavel));
            
            // 5. Se for modo Total, preenche o valor automaticamente
            if (tipoGeracao == 'Total') {
                inputValorFatura.val(formatNumberToBR(maxValorPermitido));
            }
            // =======================================================

            // 6. Re-valida o valor digitado
            validarValorDigitado();
            
        }).fail(function(xhr) {
            if (xhr.statusText !== 'abort') {
                spinner.hide();
                mostrarAlerta('danger', 'Erro ao calcular valores. Tente novamente.');
                btnConfirmar.prop('disabled', true);
            }
        });
    }
    
    // Valida o valor que o usuário digita
    function validarValorDigitado() {
        limparAlertas();
        // =======================================================
        // CORREÇÃO 5: Usar parseBRToNumber para ler o valor
        // =======================================================
        var valorDigitado = parseBRToNumber(inputValorFatura.val());
        // =======================================================
        var tipoGeracao = $('#tipo_geracao').val();
        
        // 1. Validação de Escopo (Fracionada)
        var grupoId = $('#filtro_grupo_id').val();
        var subgrupoId = $('#filtro_subgrupo_id').val();
        var empenhoId = $('#filtro_empenho_id').val();
        var filtroSelecionado = grupoId || subgrupoId || (isPublico && empenhoId);

        if (tipoGeracao == 'Fracionada' && !filtroSelecionado) {
            if (isPublico){
                mostrarAlerta('danger', 'Modo Fracionado(Público): Selecione ao menos um filtro (Grupo, Subgrupo ou Empenho).');
            } else {
                mostrarAlerta('danger', 'Modo Fracionado(Privado): Selecione ao menos um filtro (Grupo, Subgrupo).');
            }
            btnConfirmar.prop('disabled', true);
            return;
        }
        
        // 2. Validação de Valor > 0
        if (valorDigitado <= 0) {
            if (tipoGeracao == 'Fracionada') {
                 mostrarAlerta('info', 'Digite um valor a faturar.');
            } else if (maxValorPermitido <= 0) {
                 mostrarAlerta('info', 'Não há transações pendentes para faturar no modo Total.');
            }
            btnConfirmar.prop('disabled', true);
            return;
        }
        
        // 3. Validação de Limites
        if (valorDigitado > (maxValorPermitido + 0.001)) { // Margem de float
            // =======================================================
            // CORREÇÃO 5.1: Usar formatNumberToBR nas mensagens de erro
            // =======================================================
            if (valorDigitado > (valorFiltrado + 0.001)) {
                 mostrarAlerta('danger', `Valor excede o Total Pendente para estes filtros (${formatNumberToBR(valorFiltrado)}).`);
            } else if (valorDigitado > (limiteAplicavel + 0.001)) {
                 mostrarAlerta('danger', `Valor excede o Limite Aplicável do escopo (${formatNumberToBR(limiteAplicavel)}).`);
            }
            // =======================================================
            btnConfirmar.prop('disabled', true);
            return;
        }

        // Se passou em tudo, habilita o botão
        btnConfirmar.prop('disabled', false);
    }

    // --- Ação: Botão Confirmar Geração ---
    btnConfirmar.on('click', function(e) {
        e.preventDefault();
        var btn = $(this);
        
        // Validação final
        validarValorDigitado();
        if (btn.is(':disabled')) {
            return; // A validação falhou
        }
        
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Gerando...');
        limparAlertas();

        var formData = form.serializeArray();
        
        // =======================================================
        // CORREÇÃO 6: Usar parseBRToNumber para enviar o valor numérico
        // =======================================================
        var valorNumerico = parseBRToNumber(inputValorFatura.val());
        // =======================================================
        
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
                    console.log(xhr.responseJSON); 
                } else {
                    Swal.fire('Erro!', 'Falha ao gerar fatura.', 'error');
                }
            },
            complete: function() {
                 btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirmar e Gerar Fatura');
                 // Recarrega os selects para atualizar saldos pendentes
                 carregarEmpenhos();
                 carregarGruposESubgrupos();
            }
        });
    });
});
</script>
@endpush