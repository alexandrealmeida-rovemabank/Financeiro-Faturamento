<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ProcessamentoLog;
use App\Models\Empresa;
use Carbon\Carbon;
use Throwable;
use Illuminate\Support\Facades\Log;

class ReprocessarPersonalizado extends Command
{
    /**
     * Assinatura atualizada para incluir --escopo, --log_id e --force
     * @var string
     */
    protected $signature = 'faturamento:reprocessar-personalizado
                            {--log_id= : O ID do registro de log a ser atualizado}
                            {--cliente= : ID do cliente (matriz OU unidade) a ser reprocessado}
                            {--data-inicio= : Data de início (YYYY-MM-DD) do período}
                            {--data-fim= : Data de fim (YYYY-MM-DD) do período}
                            {--escopo=todos : O escopo do reprocessamento (todos, so_matriz, so_unidades)}
                            {--force : Força a execução sem confirmação interativa}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'APAGA e recopia transações de faturamento para um cliente (ou unidade) e período específicos';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $logId = $this->option('log_id');
        $clienteId = $this->option('cliente');
        $dataInicioStr = $this->option('data-inicio');
        $dataFimStr = $this->option('data-fim');
        $escopo = $this->option('escopo') ?? 'todos'; // 'todos' é o padrão

        // Validação básica dos parâmetros
        if (!$clienteId || !$dataInicioStr || !$dataFimStr) {
            $this->error('Os parâmetros --cliente, --data-inicio e --data-fim são obrigatórios.');
            $this->logFinal(ProcessamentoLog::find($logId), 'falha', 'Parâmetros ausentes.', 0, 0);
            return Command::INVALID;
        }

        try {
            $dataInicio = Carbon::createFromFormat('Y-m-d', $dataInicioStr)->startOfDay();
            $dataFim = Carbon::createFromFormat('Y-m-d', $dataFimStr)->endOfDay();
        } catch (\Exception $e) {
            $this->error('Formato de data inválido. Use YYYY-MM-DD.');
             $this->logFinal(ProcessamentoLog::find($logId), 'falha', 'Formato de data inválido.', 0, 0);
            return Command::INVALID;
        }

        $cliente = Empresa::find($clienteId);
        if (!$cliente) {
            $this->error("Cliente com ID {$clienteId} não encontrado.");
            $this->logFinal(ProcessamentoLog::find($logId), 'falha', "Cliente ID {$clienteId} não encontrado.", 0, 0);
            return Command::INVALID;
        }

        // Lógica da confirmação (pulada se --force for passado)
        if (!$this->option('force')) {
             $this->warn("ATENÇÃO: Serão apagadas e recopiadas as transações do cliente ID {$clienteId} ({$cliente->nome}) entre {$dataInicio->format('d/m/Y')} e {$dataFim->format('d/m/Y')}.");
            if (!$this->confirm('Deseja continuar?', false)) {
                $this->info('Reprocessamento personalizado cancelado.');
                 $this->logFinal(ProcessamentoLog::find($logId), 'falha', 'Cancelado pelo usuário.', 0, 0);
                return Command::INVALID;
            }
        }

        $this->info("Iniciando reprocessamento personalizado para Cliente ID {$clienteId}...");
        $inicioExecucao = Carbon::now();

        // Encontra ou cria o Log
        $log = $this->getOrCreateLog($logId, $clienteId, $dataInicioStr, $dataFimStr, $escopo);
        Log::info("[ReprocessarPersonalizado] Log ID {$log->id} está sendo processado.");
        
        $contador = 0;
        $ultimoIdProcessadoNestaExecucao = 0;
        $errorMessage = null;
        $statusFinal = 'sucesso';

        DB::beginTransaction();

        try {
            // 1. CONSTRUÇÃO DAS QUERIES DE DELETE E SELECT
            $this->info("Limpando transações existentes para o cliente {$clienteId} no período...");

            // Query base para Delete e Select
            $queryDelete = DB::table('contas_receber.transacao_faturamento')
                            ->whereBetween('data_transacao', [$dataInicio, $dataFim]);
            
            $querySelect = DB::connection('pgsql')
                ->table('public.transacao as t_origem')
                ->whereBetween('t_origem.data_cadastro', [$dataInicio, $dataFim]);

            // Aplica a lógica de filtro baseada no tipo de empresa selecionado
            if ($cliente->empresa_tipo_id == 2) {
                // Se selecionou uma UNIDADE (tipo 2), o escopo é ignorado.
                // A lógica é sempre filtrar pela 'unidade_id'
                $this->info("Alvo: Unidade (ID: {$clienteId}). Escopo '{$escopo}' ignorado.");
                $queryDelete->where('unidade_id', $clienteId);
                $querySelect->where('t_origem.unidade_id', $clienteId);
            
            } else {
                // Se selecionou uma MATRIZ (tipo 1), a lógica de escopo é aplicada.
                $this->info("Alvo: Matriz (ID: {$clienteId}). Aplicando escopo: '{$escopo}'.");
                switch ($escopo) {
                    case 'so_matriz':
                        // Somente transações da matriz (onde unidade_id é nulo)
                        $queryDelete->where('cliente_id', $clienteId)->whereNull('unidade_id');
                        $querySelect->where('t_origem.cliente_id', $clienteId)->whereNull('t_origem.unidade_id');
                        break;
                    case 'so_unidades':
                        // Somente transações das unidades (onde unidade_id NÃO é nulo)
                        $queryDelete->where('cliente_id', $clienteId)->whereNotNull('unidade_id');
                        $querySelect->where('t_origem.cliente_id', $clienteId)->whereNotNull('t_origem.unidade_id');
                        break;
                    case 'todos':
                    default:
                        // Matriz E todas as suas unidades (filtra apenas pelo cliente_id)
                        $queryDelete->where('cliente_id', $clienteId);
                        $querySelect->where('t_origem.cliente_id', $clienteId);
                        break;
                }
            }

            // Executa o Delete
            $deletedCount = $queryDelete->delete();
            $this->info("{$deletedCount} transações antigas removidas.");
            Log::info("[ReprocessarPersonalizado] Log ID {$log->id}: {$deletedCount} transações antigas removidas.");

            // 2. Buscar transações da origem
            $this->info("Buscando transações da origem (public.transacao)...");
            $transacoesOrigem = $querySelect
                ->select( // Lista completa de colunas da public.transacao
                    't_origem.id', 't_origem.cartao_id', 't_origem.cliente_id', 't_origem.unidade_id', 't_origem.veiculo_id', 't_origem.motorista_id', 't_origem.credenciado_id', 't_origem.produto_id', 't_origem.empenho_id', 't_origem.faturamento_id_cliente', 't_origem.faturamento_id_credenciado', 't_origem.quantidade', 't_origem.valor_unitario', 't_origem.valor_total', 't_origem.imposto_renda', 't_origem.taxa_administrativa', 't_origem.taxa_administrativa_credenciado', 't_origem.tipo_taxa_administrativa_credenciado', 't_origem.desconto', 't_origem.km_atual', 't_origem.distancia_percorrida', 't_origem.consumo_medio', 't_origem.intervalo', 't_origem.nota_fiscal', 't_origem.terminal', 't_origem.status', 't_origem.justificativa_cancelamento', 't_origem.data_cadastro as data_transacao', 't_origem.data_atualizacao as data_atualizacao_original', 't_origem.usuario_cadastro_id', 't_origem.usuario_atualizacao_id', 't_origem.pos_id', 't_origem.pos_rrn', 't_origem.valor_liquido_cliente', 't_origem.valor_taxa_cliente', 't_origem.contrato_id', 't_origem.informacao', 't_origem.status_localizacao', 't_origem.latitude', 't_origem.longitude', 't_origem.data_sincronizacao_mapa', 't_origem.pos_stan'
                )
                ->orderBy('t_origem.id')
                ->lazyById(200);

            $agora = Carbon::now();
            $dadosParaInserir = [];

            // 3. Inserir as transações na tabela de destino
             $this->info('Copiando transações para contas_receber.transacao_faturamento...');
            foreach ($transacoesOrigem as $transacao) {
                $dados = (array) $transacao;
                $dados['status_faturamento'] = 'pendente';
                $dados['created_at'] = $agora;
                $dados['updated_at'] = $agora;
                $dadosParaInserir[] = $dados;

                $ultimoIdProcessadoNestaExecucao = max($ultimoIdProcessadoNestaExecucao, $transacao->id);
                $contador++;

                if (count($dadosParaInserir) >= 200) {
                     DB::table('contas_receber.transacao_faturamento')->insert($dadosParaInserir);
                    $dadosParaInserir = [];
                     // $this->comment("Processado chunk até ID: {$ultimoIdProcessadoNestaExecucao}");
                }
            }
             if (!empty($dadosParaInserir)) {
                 DB::table('contas_receber.transacao_faturamento')->insert($dadosParaInserir);
            }

            DB::commit();
            $this->info("Reprocessamento personalizado concluído. {$contador} transações foram copiadas.");
            Log::info("[ReprocessarPersonalizado] Log ID {$log->id}: Transação DB commitada. {$contador} transações copiadas.");

        } catch (Throwable $e) {
            DB::rollBack();
            Log::warning("[ReprocessarPersonalizado] Log ID {$log->id}: Transação DB revertida (rollback).");
            $this->error("Erro GERAL durante o reprocessamento personalizado: " . $e->getMessage());
            $statusFinal = 'falha';
            $errorMessage = $e->getMessage() . "\n" . $e->getTraceAsString();
             \Log::error("Erro REPROCES. PERSON.: Cliente {$clienteId}, Período {$dataInicioStr}-{$dataFimStr}. Erro: " . $errorMessage);
        }

        // Atualiza o registro de log final
        $this->logFinal($log, $statusFinal, $errorMessage, $contador, $ultimoIdProcessadoNestaExecucao);

        return ($statusFinal === 'sucesso') ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Busca ou cria o registro de log.
     */
    private function getOrCreateLog($logId, $clienteId, $dataInicioStr, $dataFimStr, $escopo)
    {
        $parametrosJson = json_encode([
            'cliente_id' => $clienteId,
            'data_inicio' => $dataInicioStr,
            'data_fim' => $dataFimStr,
            'escopo' => $escopo
        ]);

        if ($logId) {
            $log = ProcessamentoLog::find($logId);
            if ($log) {
                // Atualiza o log existente (criado pelo controller) para garantir que está 'processando'
                $log->update([
                    'status' => 'processando',
                    'inicio_execucao' => now(), // Garante o início correto
                    'fim_execucao' => null, // Limpa execuções anteriores
                    'mensagem_erro' => null,
                    'parametros' => $parametrosJson,
                ]);
                return $log;
            }
        }
        
        Log::warning("[ReprocessarPersonalizado] Nenhum Log ID fornecido ou válido. Criando novo log.");
        // Cria um novo log se nenhum ID foi passado (ex: execução manual direta no CLI)
        return ProcessamentoLog::create([
            'comando' => $this->signature,
            'status' => 'iniciado',
            'inicio_execucao' => now(),
            'parametros' => $parametrosJson,
        ]);
    }

    /**
     * Atualiza o log final.
     */
    private function logFinal($log, $status, $errorMessage, $contador, $ultimoId)
    {
        if (!$log) {
            Log::error("[ReprocessarPersonalizado] Tentativa de atualizar log final, mas \$log é nulo.");
            return;
        }

        // Tenta encontrar o log novamente para garantir que a instância está atualizada
        $logFinal = ProcessamentoLog::find($log->id);
        if ($logFinal) {
            $logFinal->update([
                'fim_execucao' => Carbon::now(),
                'status' => $status,
                'ultimo_id_processado_depois' => $contador > 0 ? $ultimoId : $log->ultimo_id_processado_antes, // Mantém o ID anterior se nada foi copiado
                'transacoes_copiadas' => $contador,
                'mensagem_erro' => $errorMessage ? $e->getMessage() : null, // Salva só a msg principal
            ]);
            Log::info("[ReprocessarPersonalizado] Log ID {$log->id}: Log final atualizado para '{$status}'.");
        } else {
            Log::error("[ReprocessarPersonalizado] Log ID {$log->id}: Não foi possível encontrar o log para atualização final.");
        }
    }
}

