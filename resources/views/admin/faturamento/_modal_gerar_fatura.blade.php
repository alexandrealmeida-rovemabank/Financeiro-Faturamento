{{-- _modal_gerar_fatura.blade.php --}}

<div class="modal fade" id="modalGerarFatura" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="form-gerar-fatura">
                <div class="modal-header">
                    <h5 class="modal-title">Gerar Nova Fatura</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                    <input type="hidden" name="periodo" value="{{ $periodo }}">
                    
                    {{-- Flag para o JavaScript saber o tipo de cliente --}}
                    <input type="hidden" id="is_publico_flag" value="{{ $is_publico ? 'true' : 'false' }}">


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
                        
                        <p class="text-muted small" id="info-modo-fracionado">
                            Modo Fracionado: Selecione filtros para limitar as transações.
                        </p>

                        {{-- LINHA 1: FILTROS DE ESCOPO --}}
                        <div class="row">
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filtro: Grupo (Hierarquia 1)</label>
                                    <select name="grupo_id" id="filtro_grupo_id" class="form-control select2-modal filtro-cascata" style="width:100%;">
                                        <option value="">-- Carregando... --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filtro: Subgrupo (Hierarquia 2)</label>
                                    <select name="subgrupo_id" id="filtro_subgrupo_id" class="form-control select2-modal filtro-cascata" style="width:100%;">
                                        <option value="">-- Carregando... --</option>
                                    </select>
                                </div>
                            </div>
                            
                            {{-- CORREÇÃO: Campo Empenho só aparece se for público --}}
                            @if($is_publico)
                            <div class="col-md-6" id="empenho-field-wrapper">
                                <div class="form-group">
                                    <label>Filtro: Empenho (Hierarquia 3)</label>
                                    <select name="empenho_id" id="filtro_empenho_id" class="form-control select2-modal filtro-cascata" style="width:100%;">
                                        <option value="">-- Carregando... --</option>
                                    </select>
                                </div>
                            </div>
                            @else
                                {{-- Adiciona um input hidden para não quebrar o JS que procura o ID --}}
                                <input type="hidden" id="filtro_empenho_id" value="">
                            @endif

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Filtro: Contrato (Opcional)</label>
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
                            
                            <div class="col-6" id="limite-aplicavel-box">
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
                                <input type="number" step="0.01" id="valor_a_faturar" name="valor_fatura_calculado" class="form-control form-control-lg" 
                                       placeholder="0,00"
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
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
    
    // --- CORREÇÃO: Lê a flag do tipo de cliente ---
    var isPublico = $('#is_publico_flag').val() === 'true';

    // Cache de dados AJAX
    var cacheGrupos = null; // Armazena a resposta de getGrupos
    var xhrCalculo = null; // Para abortar requisições de cálculo em andamento
    var xhrEmpenhos = null;
    var xhrGrupos = null;

    // Estado dos Limites (Regra Funcional)
    var valorFiltrado = 0; // Total do Pool (Lógica E)
    var limiteAplicavel = 0; // Limite do Escopo (Lógica Hierárquica)
    var maxValorPermitido = 0; // O menor entre os dois acima
    
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
        resetarSelect2('#filtro_grupo_id', 'Carregando...', true);
        resetarSelect2('#filtro_subgrupo_id', 'Carregando...', true);

        // --- CORREÇÃO: Só carrega empenho se for público ---
        if(isPublico) {
            resetarSelect2('#filtro_empenho_id', 'Carregando...', true);
            carregarEmpenhos(true);
            $('#limite-aplicavel-box').show();
            $('#info-modo-fracionado').text('Modo Fracionado: Selecione pelo menos um filtro de escopo (Grupo, Subgrupo ou Empenho). Os filtros são cumulativos e em cascata.');
        } else {
            $('#limite-aplicavel-box').hide();
            $('#info-modo-fracionado').text('Modo Fracionado: Selecione filtros (Grupo ou Subgrupo) para limitar as transações.');
        }
        
        // Dispara as chamadas AJAX
        carregarContratos();
        // A lógica de cascata agora recarrega os outros
        // O `true` indica que é a carga inicial, para evitar recálculos múltiplos
        carregarGruposESubgrupos(true); 
    });

    function resetarSelect2(selector, placeholder, disabled = false) {
        var el = $(selector);
        if(!el.length) return; // Não faz nada se o seletor não existir (caso do empenho)
        
        var valorAtual = el.val(); // Pega o valor antes de limpar
        el
            .empty()
            .append($('<option>', { value: '' }).text(placeholder))
            .val(valorAtual) // Tenta restaurar o valor (para evitar reset desnecessário)
            .prop('disabled', disabled)
            .trigger('change.select2');
    }
    
    function formatCurrency(value) {
        if (isNaN(value) || value === null || value === Infinity) value = 0;
        return 'R$ ' + value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

    // --- Lógica de Exibição (Público/Privado) ---
    $('#tipo_geracao').on('change', function() {
        var tipo = $(this).val();
        var filtrosDiv = $('#filtros-fracionados');
        
        if (tipo == 'Fracionada') {
            filtrosDiv.slideDown();
            inputValorFatura.prop('readonly', false); // Permite digitar
            $('#valor-calculado-info').show();
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
                select.append($('<option>', { value: item.id }).text(item.numero));
            });
            select.val(valorSelecionado).prop('disabled', false).trigger('change.select2');
        });
    }

    // ** ATUALIZADO: Carrega Empenhos baseado em outros filtros **
    function carregarEmpenhos(isInitialLoad = false) {
        if (!isPublico) { // Não carrega empenhos para privados
             // Se esta for a carga inicial, precisamos acionar o recálculo
             if (isInitialLoad) {
                recalcularValores();
             }
            return;
        }

        if (xhrEmpenhos) xhrEmpenhos.abort(); // Cancela requisição anterior
        
        var select = $('#filtro_empenho_id');
        var valorSelecionado = select.val();
        resetarSelect2(select, 'Carregando Empenhos...', true);

        xhrEmpenhos = $.get('{{ route('faturamento.getEmpenhos') }}', { 
            cliente_id: clienteId, 
            periodo: periodo, 
            contrato_id: $('#filtro_contrato_id').val(),
            grupo_id: $('#filtro_grupo_id').val(),
            subgrupo_id: $('#filtro_subgrupo_id').val()
        }, function(data) {
            select.empty().append($('<option>', { value: '' }).text('-- Opcional --'));
            $.each(data, function(i, item) {
                var saldoPendente = parseFloat(item.valor_pendente) || 0;
                var texto = `Nº ${item.numero_empenho} (Pendente: ${formatCurrency(saldoPendente)})`;
                select.append($('<option>', { 
                    value: item.id,
                    'data-balance': saldoPendente // Armazena o saldo
                }).text(texto));
            });
            select.val(valorSelecionado).prop('disabled', false).trigger('change.select2');
            
            // Na carga inicial, não recalcula (espera o carregarGrupos)
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
            contrato_id: $('#filtro_contrato_id').val(),
            empenho_id: isPublico ? $('#filtro_empenho_id').val() : '' // Só envia empenho se for público
        }, function(data) {
            cacheGrupos = data; // Salva no cache
            
            // Popula Grupos
            selectGrupo.empty().append($('<option>', { value: '' }).text('-- Opcional --'));
            $.each(data.grupos_pais, function(i, item) {
                var saldoPendente = parseFloat(item.valor_pendente) || 0;
                var texto = `${item.text} (Pendente: ${formatCurrency(saldoPendente)})`;
                selectGrupo.append($('<option>', { 
                    value: item.id,
                    'data-balance': saldoPendente // Armazena o saldo
                }).text(texto));
            });
            
            // Popula Subgrupos (mas filtra baseado no grupo JÁ selecionado)
            filtrarSubgruposDoCache(valorGrupoSel, valorSubgrupoSel);
            
            selectGrupo.val(valorGrupoSel).prop('disabled', false).trigger('change.select2');

            // Na carga inicial, esta é a última função a rodar, então ela dispara o recálculo
            if(isInitialLoad) {
                // Se for público, espera carregarEmpenhos. Se for privado, recalcula agora.
                if(!isPublico) recalcularValores();
            } else {
                 recalcularValores();
            }
           
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
           var texto = `${item.text} (Pendente: ${formatCurrency(saldoPendente)})`;
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
    $('#filtro_contrato_id').on('change', function() {
        carregarGruposESubgrupos();
        if(isPublico) carregarEmpenhos();
        // recalcularValores() é chamado dentro do 'done' das funções de carregar
    });
    
    // Ao mudar Empenho: recarrega Grupos/Subgrupos
    $('#filtro_empenho_id').on('change', function() {
        carregarGruposESubgrupos();
        // recalcularValores() é chamado dentro do 'done' do carregarGrupos
    });

    // Ao mudar Grupo Pai: filtra Subgrupos (via JS) e recarrega Empenhos
    $('#filtro_grupo_id').on('change', function() {
        var paiId = $(this).val();
        var subgrupoSel = $('#filtro_subgrupo_id').val();
        
        // Se o subgrupo selecionado não pertence ao novo pai, reseta-o
        var subgrupoAtual = (cacheGrupos && cacheGrupos.subgrupos) ? cacheGrupos.subgrupos.find(s => s.id == subgrupoSel) : null;
        if (subgrupoAtual && subgrupoAtual.grupo_pai_id != paiId) {
            subgrupoSel = null;
        }

        filtrarSubgruposDoCache(paiId, subgrupoSel); 
        if(isPublico) carregarEmpenhos();
        else recalcularValores(); // Privado recalcula aqui
        // recalcularValores() (público) é chamado dentro do 'done' do carregarEmpenhos
    });
    
    // Ao mudar Subgrupo: recarrega Empenhos
    $('#filtro_subgrupo_id').on('change', function() {
        if(isPublico) carregarEmpenhos();
        else recalcularValores(); // Privado recalcula aqui
        // recalcularValores() (público) é chamado dentro do 'done' do carregarEmpenhos
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
        var empenhoId = isPublico ? $('#filtro_empenho_id').val() : '';
        
        // --- CORREÇÃO: Validação de Filtro Fracionado (só para públicos) ---
        if (isPublico && tipoGeracao == 'Fracionada' && !grupoId && !subgrupoId && !empenhoId) {
            mostrarAlerta('danger', 'Modo Fracionado: Selecione ao menos um filtro (Grupo, Subgrupo ou Empenho).');
            spinner.hide();
            // Reseta displays
            $('#display-valor-filtrado').text('R$ 0,00');
            $('#display-limite-aplicavel').text('R$ 0,00');
            maxValorPermitido = 0; // Zera o limite
            validarValorDigitado(); // Valida o valor (que será 0)
            return;
        }
        
        // --- CORREÇÃO: Validação para privados (só grupo/subgrupo) ---
         if (!isPublico && tipoGeracao == 'Fracionada' && !grupoId && !subgrupoId) {
             // Se for privado e fracionado, mas sem filtros, age como "Total" (mas permite edição)
             // Não mostra erro, apenas calcula o total
         }

        // 2. Regra: Calcular Limite Aplicável (Hierarquia de Limites)
        // (A chamada AJAX agora retorna ambos os valores)

        // 3. Regra: Calcular Valor Filtrado (Pool Lógica "E")
        if (xhrCalculo) xhrCalculo.abort();

        xhrCalculo = $.get('{{ route('faturamento.getValorFiltrado') }}', {
            cliente_id: clienteId,
            periodo: periodo,
            contrato_id: $('#filtro_contrato_id').val(),
            empenho_id: empenhoId,
            grupo_id: grupoId,
            subgrupo_id: subgrupoId,
            tipo_geracao: tipoGeracao
        }).done(function(data) {
            spinner.hide();
            valorFiltrado = parseFloat(data.valor_filtrado) || 0;
            limiteAplicavel = parseFloat(data.limite_aplicavel) || 0;

            // O máximo que o usuário pode digitar é o MENOR entre o pool E o limite
            // Para privados, o limite hierárquico (limiteAplicavel) pode não fazer sentido, então usamos o valorFiltrado
            maxValorPermitido = isPublico ? Math.min(valorFiltrado, limiteAplicavel) : valorFiltrado;
            
            // 4. Atualizar UI
            $('#display-valor-filtrado').text(formatCurrency(valorFiltrado));
            $('#display-limite-aplicavel').text(formatCurrency(limiteAplicavel));
            
            // 5. Se for modo Total, preenche o valor automaticamente
            if (tipoGeracao == 'Total') {
                inputValorFatura.val(maxValorPermitido.toFixed(2));
            }

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
        var valorDigitado = parseFloat(inputValorFatura.val()) || 0;
        var tipoGeracao = $('#tipo_geracao').val();
        
        // --- CORREÇÃO: Validação de Escopo (Fracionada) só para públicos ---
        if (isPublico && tipoGeracao == 'Fracionada' && 
            !$('#filtro_grupo_id').val() && 
            !$('#filtro_subgrupo_id').val() && 
            !$('#filtro_empenho_id').val()
        ) {
            mostrarAlerta('danger', 'Modo Fracionado: Selecione ao menos um filtro (Grupo, Subgrupo ou Empenho).');
            btnConfirmar.prop('disabled', true);
            return;
        }
        
        // --- CORREÇÃO: Validação para privados (pelo menos grupo ou subgrupo) ---
         if (!isPublico && tipoGeracao == 'Fracionada' && 
            !$('#filtro_grupo_id').val() && 
            !$('#filtro_subgrupo_id').val()
        ) {
            // Para privados, se não houver filtro, o valor MÁXIMO é o total pendente
             maxValorPermitido = valorFiltrado;
             // Não mostramos erro, permitimos faturar o total (ou menos)
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
        // Para privados, o maxValorPermitido já é o 'valorFiltrado'
        if (valorDigitado > (maxValorPermitido + 0.001)) { // Margem de float
            if (isPublico && valorDigitado > (limiteAplicavel + 0.001)) {
                 mostrarAlerta('danger', `Valor excede o Limite Aplicável do escopo (${formatCurrency(limiteAplicavel)}).`);
            } else {
                 mostrarAlerta('danger', `Valor excede o Total Pendente para estes filtros (${formatCurrency(valorFiltrado)}).`);
            }
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
        var valorNumerico = parseFloat(inputValorFatura.val()) || 0;
        
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
                 btn.prop('disabled', false).html('Confirmar e Gerar Fatura');
                 // Recarrega os selects para atualizar saldos pendentes
                 if(isPublico) carregarEmpenhos();
                 carregarGruposESubgrupos();
            }
        });
    });
});
</script>
@endpush
