<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ProcessamentoLog;
use Carbon\Carbon;
use Throwable;
use Illuminate\Support\Facades\Log; // Adicionar Log facade

class ReprocessarGeral extends Command
{
    /**
     * Assinatura do comando, adicionando a opção --force e --log_id.
     * @var string
     */
    protected $signature = 'faturamento:reprocessar-geral 
                            {--log_id= : O ID do registro de log a ser atualizado} 
                            {--force : Força a execução sem confirmação interativa}';

    /**
     * Descrição do comando.
     * @var string
     */
    protected $description = 'APAGA TODAS as transações de faturamento e copia novamente da origem (public.transacao)';

    /**
     * Executa o comando.
     */
    public function handle(): int
    {
        // Pula a confirmação se a opção --force for passada
        if (!$this->option('force')) {
            $this->warn('ATENÇÃO: Este comando apagará TODAS as transações da tabela contas_receber.transacao_faturamento.');
            if (!$this->confirm('Tem certeza absoluta que deseja continuar?', false)) {
                $this->info('Reprocessamento geral cancelado.');
                return Command::INVALID;
            }
        } else {
             $this->warn('Executando reprocessamento geral em modo --force (sem confirmação)...');
        }

        $this->info('Iniciando reprocessamento geral de transações...');
        Log::info('[ReprocessarGeral] Iniciando...');
        $inicioExecucao = Carbon::now();
        $logId = $this->option('log_id');
        $log = null;

        // Tenta encontrar o log pelo ID passado; se não houver, cria um novo (para execução manual via CLI)
        if ($logId) {
            $log = ProcessamentoLog::find($logId);
        }
        
        if (!$log) {
             Log::warning("[ReprocessarGeral] Nenhum Log ID fornecido ou encontrado. Criando um novo log para execução manual.");
             $log = ProcessamentoLog::create([
                'comando' => $this->signature,
                'status' => 'iniciado',
                'inicio_execucao' => $inicioExecucao,
                'parametros' => json_encode(['force' => $this->option('force'), 'log_id' => null]),
             ]);
        } else {
            // Atualiza o log existente (criado pelo controller) para garantir que está 'iniciado'
             $log->update([
                'status' => 'iniciado',
                'inicio_execucao' => $inicioExecucao, // Garante o início correto
                'fim_execucao' => null, // Limpa execuções anteriores
                'mensagem_erro' => null,
                'parametros' => json_encode(['force' => $this->option('force'), 'log_id' => $logId]),
            ]);
        }
        
        Log::info("[ReprocessarGeral] Log ID {$log->id} está sendo processado.");

        $contador = 0;
        $ultimoIdProcessadoNestaExecucao = 0;
        $errorMessage = null;
        $statusFinal = 'sucesso';

         try {
            // ============================================================
            // 1️⃣  LIMPAR TABELAS DE DESTINO (em cascata)
            // ============================================================
            $this->info('🧹 Limpando tabelas de destino (fatura_itens e transacao_faturamento)...');
            Log::info("[ReprocessarGeral] Log ID {$log->id}: Limpando tabelas com TRUNCATE CASCADE...");

            // Aqui truncamos as tabelas relacionadas de forma segura
            DB::statement('
                TRUNCATE TABLE 
                    contas_receber.fatura_itens,
                    contas_receber.faturas,
                    contas_receber.transacao_faturamento
                RESTART IDENTITY CASCADE
            ');
            

            $this->info('✅ Tabelas limpas com sucesso.');
            Log::info("[ReprocessarGeral] Log ID {$log->id}: Tabelas truncadas com sucesso.");

            
            // 2. Inicia a transação APENAS para as inserções
            DB::beginTransaction();
            Log::info("[ReprocessarGeral] Log ID {$log->id}: Iniciando Transação DB para inserts.");

            // 3. Buscar TODAS as transações da origem
            $this->info('Buscando transações da origem (public.transacao)...');
            Log::info("[ReprocessarGeral] Log ID {$log->id}: Buscando transações da origem...");
            $transacoesOrigem = DB::connection('pgsql')
                ->table('public.transacao as t_origem')
                ->select( // Lista completa de colunas da public.transacao
                     't_origem.id', 't_origem.cartao_id', 't_origem.cliente_id', 't_origem.unidade_id', 't_origem.veiculo_id', 't_origem.motorista_id', 't_origem.credenciado_id', 't_origem.produto_id', 't_origem.empenho_id', 't_origem.faturamento_id_cliente', 't_origem.faturamento_id_credenciado', 't_origem.quantidade', 't_origem.valor_unitario', 't_origem.valor_total', 't_origem.imposto_renda', 't_origem.taxa_administrativa', 't_origem.taxa_administrativa_credenciado', 't_origem.tipo_taxa_administrativa_credenciado', 't_origem.desconto', 't_origem.km_atual', 't_origem.distancia_percorrida', 't_origem.consumo_medio', 't_origem.intervalo', 't_origem.nota_fiscal', 't_origem.terminal', 't_origem.status', 't_origem.justificativa_cancelamento', 't_origem.data_cadastro as data_transacao', 't_origem.data_atualizacao as data_atualizacao_original', 't_origem.usuario_cadastro_id', 't_origem.usuario_atualizacao_id', 't_origem.pos_id', 't_origem.pos_rrn', 't_origem.valor_liquido_cliente', 't_origem.valor_taxa_cliente', 't_origem.contrato_id', 't_origem.informacao', 't_origem.status_localizacao', 't_origem.latitude', 't_origem.longitude', 't_origem.data_sincronizacao_mapa', 't_origem.pos_stan'
                )
                ->orderBy('t_origem.id')
                ->lazyById(500); // Aumenta o chunk para acelerar

            $agora = Carbon::now();
            $dadosParaInserir = [];

            // 🟡 Atualiza o status do log para "processando" antes de iniciar o loop
            $log->update(['status' => 'processando']);
            Log::info("[ReprocessarGeral] Log ID {$log->id}: Status atualizado para 'processando'.");

            // 4. Inserir as transações
            $this->info('Copiando transações para contas_receber.transacao_faturamento...');
            Log::info("[ReprocessarGeral] Log ID {$log->id}: Iniciando cópia...");
            foreach ($transacoesOrigem as $transacao) {
                $dados = (array) $transacao;
                $dados['status_faturamento'] = 'pendente';
                $dados['created_at'] = $agora;
                $dados['updated_at'] = $agora;
                $dadosParaInserir[] = $dados;

                $ultimoIdProcessadoNestaExecucao = $transacao->id;
                $contador++;

                // Insere em batches
                if (count($dadosParaInserir) >= 500) {
                     DB::table('contas_receber.transacao_faturamento')->insert($dadosParaInserir);
                    $dadosParaInserir = [];
                    Log::info("[ReprocessarGeral] Log ID {$log->id}: Inserido chunk até ID: {$ultimoIdProcessadoNestaExecucao}");
                }
            }

            // Insere o restante
            if (!empty($dadosParaInserir)) {
                 DB::table('contas_receber.transacao_faturamento')->insert($dadosParaInserir);
                 Log::info("[ReprocessarGeral] Log ID {$log->id}: Inserido último chunk com " . count($dadosParaInserir) . " registros.");
            }

            DB::commit();
            Log::info("[ReprocessarGeral] Log ID {$log->id}: Transação DB commitada.");
            $this->info("Reprocessamento geral concluído. {$contador} transações foram copiadas.");

        } catch (Throwable $e) {
            // Tenta reverter a transação de insert (o truncate não pode ser revertido)
            DB::rollBack();
            Log::warning("[ReprocessarGeral] Log ID {$log->id}: Transação DB revertida (rollback).");
            $this->error("Erro GERAL durante o reprocessamento: " . $e->getMessage());
            $statusFinal = 'falha';
            $errorMessage = $e->getMessage() . "\n" . $e->getTraceAsString();
             Log::error("[ReprocessarGeral] Log ID {$log->id}: Erro GERAL: " . $errorMessage);
        }

        // Atualiza o registro de log final
        // Busca novamente para garantir que não haja conflito de instância
        $logFinal = ProcessamentoLog::find($log->id);
        if($logFinal){
            $logFinal->update([
                'fim_execucao' => Carbon::now(),
                'status' => $statusFinal,
                'ultimo_id_processado_depois' => $ultimoIdProcessadoNestaExecucao,
                'transacoes_copiadas' => $contador,
                'mensagem_erro' => $errorMessage, // <-- CORRIGIDO: Salva a stack trace completa
            ]);
             Log::info("[ReprocessarGeral] Log ID {$log->id}: Log final atualizado com status '{$statusFinal}'.");
        } else {
             Log::error("[ReprocessarGeral] Log ID {$log->id}: Não foi possível encontrar o log para atualização final.");
        }

        return ($statusFinal === 'sucesso') ? Command::SUCCESS : Command::FAILURE;
    }
}
