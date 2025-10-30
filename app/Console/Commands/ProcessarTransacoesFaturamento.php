<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\TransacaoFaturamento;
use App\Models\ProcessamentoLog;
use Carbon\Carbon;
use Throwable;
use Illuminate\Support\Facades\Log;

class ProcessarTransacoesFaturamento extends Command
{
    /**
     * Assinatura atualizada para aceitar o ID do log
     * @var string
     */
    protected $signature = 'faturamento:processar-transacoes {--log_id= : O ID do registro de log a ser atualizado}';
    
    protected $description = 'Copia novas transações da tabela public.transacao para contas_receber.transacao_faturamento e registra log';

    public function handle(): int
    {
        $this->info('Iniciando processamento de transações para faturamento...');
        $logId = $this->option('log_id');
        $log = null;
        $inicioExecucao = Carbon::now();

        // Tenta encontrar o log pelo ID passado
        if ($logId) {
            $log = ProcessamentoLog::find($logId);
        }

        // Se não encontrar o log, cria um novo (para execução manual via CLI)
        if (!$log) {
            Log::warning("[ProcessarTransacoes] Nenhum Log ID fornecido ou encontrado. Criando um novo log.");
            $ultimoIdProcessadoAntes = TransacaoFaturamento::max('id') ?? 0;
            $log = ProcessamentoLog::create([
                'comando' => $this->signature,
                'inicio_execucao' => $inicioExecucao,
                'status' => 'iniciado',
                'ultimo_id_processado_antes' => $ultimoIdProcessadoAntes,
            ]);
        } else {
            // Se encontrou o log (veio do Job), atualiza o status
            // Definimos ultimoIdProcessadoAntes com base no log (se existir) ou no max atual
            $ultimoIdProcessadoAntes = $log->ultimo_id_processado_antes ?? (TransacaoFaturamento::max('id') ?? 0);

            $log->update([
                'status' => 'processando', // Atualiza o status de 'iniciado' para 'processando'
                'inicio_execucao' => $inicioExecucao, // Garante o início correto
                'fim_execucao' => null, // Limpa execuções anteriores
                'mensagem_erro' => null,
                // garante que o campo ultimo_id_processado_antes no banco reflita o valor usado
                'ultimo_id_processado_antes' => $ultimoIdProcessadoAntes,
            ]);
        }

        Log::info("[ProcessarTransacoes] Log ID {$log->id} está sendo processado.");
        $this->info("Último ID processado antes: {$ultimoIdProcessadoAntes}");

        $contador = 0;
        $ultimoIdProcessadoNestaExecucao = $ultimoIdProcessadoAntes;
        $errorMessage = null;
        $statusFinal = 'sucesso';

        try {
            $novasTransacoes = DB::connection('pgsql')
                ->table('public.transacao as t_origem')
                ->select(
                    't_origem.id', 't_origem.cartao_id', 't_origem.cliente_id', 't_origem.unidade_id', 't_origem.veiculo_id', 't_origem.motorista_id', 't_origem.credenciado_id', 't_origem.produto_id', 't_origem.empenho_id', 't_origem.faturamento_id_cliente', 't_origem.faturamento_id_credenciado', 't_origem.quantidade', 't_origem.valor_unitario', 't_origem.valor_total', 't_origem.imposto_renda', 't_origem.taxa_administrativa', 't_origem.taxa_administrativa_credenciado', 't_origem.tipo_taxa_administrativa_credenciado', 't_origem.desconto', 't_origem.km_atual', 't_origem.distancia_percorrida', 't_origem.consumo_medio', 't_origem.intervalo', 't_origem.nota_fiscal', 't_origem.terminal', 't_origem.status', 't_origem.justificativa_cancelamento', 't_origem.data_cadastro as data_transacao', 't_origem.data_atualizacao as data_atualizacao_original', 't_origem.usuario_cadastro_id', 't_origem.usuario_atualizacao_id', 't_origem.pos_id', 't_origem.pos_rrn', 't_origem.valor_liquido_cliente', 't_origem.valor_taxa_cliente', 't_origem.contrato_id', 't_origem.informacao', 't_origem.status_localizacao', 't_origem.latitude', 't_origem.longitude', 't_origem.data_sincronizacao_mapa', 't_origem.pos_stan'
                )
                ->where('t_origem.id', '>', $ultimoIdProcessadoAntes)
                ->orderBy('t_origem.id')
                ->lazyById(100);

            $agora = Carbon::now();
            $dadosParaInserir = [];

            // Atualiza status no log para processando (garante visibilidade)
            $log->update(['status' => 'processando']);

            foreach ($novasTransacoes as $transacao) {
                $dados = (array) $transacao;
                $dados['status_faturamento'] = 'pendente';
                $dados['created_at'] = $agora;
                $dados['updated_at'] = $agora;
                $dadosParaInserir[] = $dados;

                $ultimoIdProcessadoNestaExecucao = $transacao->id;
                $contador++;

                if (count($dadosParaInserir) >= 100) {
                    DB::connection('pgsql')
                        ->table('contas_receber.transacao_faturamento')
                        ->insertOrIgnore($dadosParaInserir);
                    $dadosParaInserir = [];
                }
            }

            if (!empty($dadosParaInserir)) {
                DB::connection('pgsql')
                    ->table('contas_receber.transacao_faturamento')
                    ->insertOrIgnore($dadosParaInserir);
            }

            if ($contador > 0) {
                $this->info("Processamento concluído. {$contador} novas transações foram copiadas.");
            } else {
                $this->info("Nenhuma nova transação encontrada para processar.");
            }

        } catch (Throwable $e) {
            $this->error("Erro GERAL durante o processamento: " . $e->getMessage());
            $statusFinal = 'falha';
            $errorMessage = $e->getMessage() . "\n" . $e->getTraceAsString();
            Log::error("[ProcessarTransacoes] Log ID {$log->id}: Erro GERAL: " . $errorMessage);
        }

        // Se não processou nada, mantenha o ultimoIdProcessadoNestaExecucao igual ao antes
        if ($ultimoIdProcessadoNestaExecucao === null) {
            $ultimoIdProcessadoNestaExecucao = $ultimoIdProcessadoAntes;
        }

        // Atualiza o mesmo log no final — grava o intervalo (antes -> depois)
        $log->update([
            'fim_execucao' => Carbon::now(),
            'status' => $statusFinal,
            'ultimo_id_processado_depois' => $ultimoIdProcessadoNestaExecucao,
            'transacoes_copiadas' => $contador,
            'mensagem_erro' => $errorMessage,
        ]);

        // Se você quiser facilitar exibição do intervalo, pode consultar (no front) ultimo_id_processado_antes e depois
        // Ex: se antes = 22 e depois = 30 => intervalo 23-30 (novas inserções)

        return ($statusFinal === 'sucesso') ? Command::SUCCESS : Command::FAILURE;
    }
}
