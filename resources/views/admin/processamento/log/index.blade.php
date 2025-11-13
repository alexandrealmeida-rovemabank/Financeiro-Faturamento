@extends('adminlte::page')

@section('title', 'Log de Processamento')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Log de Processamento de Transações</h1>
        {{-- Botão para Acionar Manualmente --}}
        @php
            $temPermissaoRun = collect(Auth::user()->getAllPermissions())
                ->pluck('name')
                ->contains(fn($p) => str_contains($p, 'run'));
        @endphp

        @if($temPermissaoRun)
            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalProcessamentoManual">
                <i class="fas fa-play-circle mr-1"></i> Acionar Processamento Manual
            </button>
        @endif

    </div>
@stop

@section('content')
    @include('partials.session-messages') {{-- Inclui mensagens --}}

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Histórico de Execuções</h3>
        </div>
        <div class="card-body">
            <!-- Filtros -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="filtro-status">Status:</label>
                    <select id="filtro-status" class="form-control">
                        <option value="">Todos</option>
                        <option value="sucesso">Sucesso</option>
                        <option value="falha">Falha</option>
                        <option value="iniciado">Iniciado</option>
                        <option value="processando">Processando</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtro-data-inicio">Data Início:</label>
                    <input type="date" id="filtro-data-inicio" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="filtro-data-fim">Data Fim:</label>
                    <input type="date" id="filtro-data-fim" class="form-control">
                </div>
                <div class="col-md-3 align-self-end">
                    <button id="btn-filtrar" class="btn btn-primary mr-1">Filtrar</button>
                    <button id="btn-limpar" class="btn btn-secondary">Limpar</button>
                </div>
            </div>

            <table id="logs-table" class="table table-bordered table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Comando</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Duração</th>
                        <th>Status</th>
                        <th>IDs/Parâmetros Processados</th>
                        <th>Copiadas</th>
                        <th>Erro</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Modal para Acionamento Manual --}}
    <div class="modal fade" id="modalProcessamentoManual" tabindex="-1" role="dialog" aria-labelledby="modalProcessamentoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modalProcessamentoLabel"><i class="fas fa-play-circle mr-1"></i> Acionar Processamento Manual de Transações</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.processamento.acionar') }}" method="POST" id="form-processamento-manual">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted">Selecione o tipo de processamento que deseja executar. Tarefas longas serão enviadas para a fila e executadas em segundo plano.</p>
                        @can('run reprocessamento ultimas transações')
                            {{-- Opção 1: Últimas Transações --}}
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="tipo_processamento" id="tipo_ultimas" value="ultimas" checked>
                                <label class="form-check-label font-weight-bold" for="tipo_ultimas">
                                    Processar Últimas Transações
                                </label>
                                <small class="form-text text-muted">Copia apenas as transações que ainda não foram processadas.</small>
                            </div>
                        @endcan
                        
                        <hr>

                        {{-- Opção 2: Geral (Reprocessar Tudo) --}}
                        @can('run reprocessamento geral')

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="tipo_processamento" id="tipo_geral" value="geral">
                                <label class="form-check-label font-weight-bold text-danger" for="tipo_geral">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Reprocessar Todas as Transações (Geral)
                                </label>
                                <small class="form-text text-danger"><b>Atenção:</b> Esta opção apagará todas as transações da tabela de faturamento.</small>
                            </div>
                        @endcan

                        <hr>

                        {{-- Opção 3: Personalizado --}}
                        @can('run reprocessamento personalizado')
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="tipo_processamento" id="tipo_personalizado" value="personalizado">
                                <label class="form-check-label font-weight-bold" for="tipo_personalizado">
                                    Reprocessar por Cliente e Período (Personalizado)
                                </label>
                                <small class="form-text text-muted">Apaga e recopia as transações para um cliente específico ou suas unidades.</small>
                            </div>

                            {{-- Campos Condicionais para Personalizado --}}
                            <div id="campos-personalizado" class="mt-3 pl-4 border-left" style="display: none;">
                                <div class="form-group">
                                    <label for="cliente_id_personalizado">Cliente (Matriz ou Unidade):</label>
                                    <select name="cliente_id" id="cliente_id_personalizado" class="form-control select2-modal" style="width: 100%;">
                                        <option value="">Selecione um cliente...</option>
                                        @if(isset($clientes))
                                            @foreach($clientes as $cliente)
                                                <option value="{{ $cliente->id }}" data-tipo-id="{{ $cliente->empresa_tipo_id }}">
                                                    {{ $cliente->razao_social }} (CNPJ: {{ $cliente->cnpj ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('cliente_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- NOVO: Opções de Escopo (só aparece se Matriz for selecionada) --}}
                                <div class="form-group" id="opcoes-matriz-escopo" style="display: none;">
                                    <label>Escopo do Reprocessamento:</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="escopo_matriz" id="escopo_todos" value="todos" checked>
                                        <label class="form-check-label" for="escopo_todos">Matriz e Todas as Unidades</label>
                                        <small class="form-text text-muted">Query: `WHERE cliente_id = [ID_Matriz]`</small>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="escopo_matriz" id="escopo_so_matriz" value="so_matriz">
                                        <label class="form-check-label" for="escopo_so_matriz">Somente a Matriz</label>
                                        <small class="form-text text-muted">Query: `WHERE cliente_id = [ID_Matriz] AND unidade_id IS NULL`</small>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="escopo_matriz" id="escopo_so_unidades" value="so_unidades">
                                        <label class="form-check-label" for="escopo_so_unidades">Somente as Unidades</label>
                                        <small class="form-text text-muted">Query: `WHERE cliente_id = [ID_Matriz] AND unidade_id IS NOT NULL`</small>
                                    </div>
                                    <small class="form-text text-info mt-2"><b>Nota:</b> Se você selecionar uma Unidade, a lógica será sempre `WHERE unidade_id = [ID_Unidade]`.</small>
                                </div>
                                {{-- FIM NOVO --}}

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="data_inicio_personalizado">Data Início:</label>
                                            <input type="date" name="data_inicio" id="data_inicio_personalizado" class="form-control @error('data_inicio') is-invalid @enderror">
                                            @error('data_inicio')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="data_fim_personalizado">Data Fim:</label>
                                            <input type="date" name="data_fim" id="data_fim_personalizado" class="form-control @error('data_fim') is-invalid @enderror">
                                            @error('data_fim')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <small class="form-text text-warning"><b>Atenção:</b> Todas as transações do cliente selecionado dentro deste período serão removidas e recopiadas.</small>
                            </div>
                        @endcan
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btn-confirmar-processamento">Confirmar Acionamento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal de Confirmação Crítica (para Reprocessar Geral) --}}
    <div class="modal fade" id="modalConfirmacaoGeral" tabindex="-1" role="dialog" aria-labelledby="modalConfirmacaoGeralLabel" aria-hidden="true">
        {{-- ... (conteúdo do modal de confirmação) ... --}}
        <div class="modal-dialog" role="document">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalConfirmacaoGeralLabel"><i class="fas fa-exclamation-triangle mr-1"></i> Confirmação Crítica Necessária</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Você selecionou a opção <strong>"Reprocessar Todas as Transações (Geral)"</strong>.</p>
                    <p class="text-danger"><strong>Esta ação é irreversível e apagará TODOS os dados da tabela `contas_receber.transacao_faturamento`.</strong></p>
                    <p>Tem certeza absoluta que deseja continuar?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btn-confirmar-geral-final">Sim, Reprocessar Tudo</button>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.5.2/select2-bootstrap4.min.css" rel="stylesheet">
@endpush

@push('js')
    {{-- Select2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializa DataTable
        var table = $('#logs-table').DataTable({
            // ... (configuração do DataTable) ...
             processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.processamento.logs.index') }}',
                data: function (d) {
                   d.status = $('#filtro-status').val();
                   d.data_inicio = $('#filtro-data-inicio').val();
                   d.data_fim = $('#filtro-data-fim').val();
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'comando', name: 'comando' },
                { data: 'inicio_execucao', name: 'inicio_execucao' },
                { data: 'fim_execucao', name: 'fim_execucao' },
                { data: 'duracao', name: 'duracao', orderable: false, searchable: false },
                { data: 'status', name: 'status' },
                { data: 'intervalo_ids', name: 'intervalo_ids', orderable: false, searchable: false },
                { data: 'transacoes_copiadas', name: 'transacoes_copiadas' },
                { data: 'mensagem_erro_formatada', name: 'mensagem_erro', orderable: false, searchable: false }
            ],
            order: [[ 0, 'desc' ]],
            language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json' },
            pageLength: 25,
            responsive: true,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        });
        $('#btn-filtrar').on('click', function() { table.ajax.reload(); });
        $('#btn-limpar').on('click', function() {
            $('#filtro-status').val('');
            $('#filtro-data-inicio').val('');
            $('#filtro-data-fim').val('');
            table.ajax.reload();
         });

        // --- Lógica do Modal de Processamento ---
        $('#modalProcessamentoManual').on('shown.bs.modal', function () {
             $('.select2-modal').select2({
                theme: 'bootstrap4',
                placeholder: "Selecione um cliente...",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalProcessamentoManual')
            });
        });

        // --- NOVA LÓGICA PARA ESCOPO ---
        $('#cliente_id_personalizado').on('change', function() {
            // Pega o 'data-tipo-id' da opção selecionada
            var tipoId = $(this).find('option:selected').data('tipo-id');
            
            if (tipoId == 1) { // 1 = Matriz
                $('#opcoes-matriz-escopo').slideDown();
            } else { // 2 = Unidade ou se estiver vazio
                $('#opcoes-matriz-escopo').slideUp();
            }
        });

        // Mostra/Esconde campos personalizados (baseado no tipo de processamento)
        $('input[name="tipo_processamento"]').on('change', function() {
            if ($(this).val() === 'personalizado') {
                $('#campos-personalizado').slideDown();
                $('#cliente_id_personalizado').prop('required', true);
                $('#data_inicio_personalizado').prop('required', true);
                $('#data_fim_personalizado').prop('required', true);
            } else {
                $('#campos-personalizado').slideUp();
                $('#opcoes-matriz-escopo').slideUp(); // Esconde escopo se mudar para Geral ou Ultimas
                $('#cliente_id_personalizado').prop('required', false);
                $('#data_inicio_personalizado').prop('required', false);
                $('#data_fim_personalizado').prop('required', false);
            }
        });

        // Lógica de Submissão e Confirmação Crítica
        $('#form-processamento-manual').on('submit', function(e) {
            var tipo = $('input[name="tipo_processamento"]:checked').val();
            if (tipo === 'geral') {
                e.preventDefault();
                $('#modalConfirmacaoGeral').modal('show');
            }
        });

        // Botão final de confirmação para "Geral"
        $('#btn-confirmar-geral-final').on('click', function() {
             $('#modalConfirmacaoGeral').modal('hide');
             $('#form-processamento-manual').off('submit').submit();
        });

        // Atualização automática da tabela de logs
        setInterval(() => {
            table.ajax.reload(null, false); 
        }, 10000);

    });
    </script>
@endpush

