<?php

namespace App\Jobs;

use App\Models\ProcessamentoLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Console\Command;

class ExecutarComandoArtisanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $comando;
    protected $parametros;
    protected $logId;

    /**
     * Número de vezes que o job pode ser tentado.
     * @var int
     */
    public $tries = 1; // Tenta apenas uma vez

    /**
     * Tempo máximo de execução do job em segundos.
     * Definido para 2 horas (7200 segundos) para processos longos.
     * @var int
     */
    public $timeout = 7200; // 2 HORAS DE TIMEOUT

    public function __construct(string $comando, array $parametros = [], int $logId)
    {
        $this->comando = $comando;
        $this->parametros = $parametros;
        $this->logId = $logId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("JOB INICIADO: Executando comando '{$this->comando}' para Log ID {$this->logId}. Parâmetros: " . json_encode($this->parametros));
        
        try {
            // O comando Artisan (ReprocessarGeral) agora é responsável por
            // encontrar e atualizar o log (ID: $this->logId)
            $exitCode = Artisan::call($this->comando, $this->parametros);

            // Se o comando não retornar 0 (sucesso), lança uma exceção
            if ($exitCode !== Command::SUCCESS) {
                 throw new \Exception("Comando '{$this->comando}' finalizou com código de erro: {$exitCode}. Verifique o log de processamento e o log do Laravel.");
            }
            
            Log::info("JOB SUCESSO: Comando '{$this->comando}' (Log ID {$this->logId}) finalizou a chamada com sucesso.");

        } catch (Throwable $e) { // Captura qualquer erro
            Log::channel('stderr')->error("JOB FALHA: Erro ao executar '{$this->comando}' (Log ID {$this->logId}): {$e->getMessage()}");
            Log::error("JOB FALHA DETALHES (Log ID {$this->logId}):\n" . $e->getTraceAsString());
            
            // O comando (ReprocessarGeral) já deve ter atualizado o log para 'falha'
            // Mas se o *próprio job* falhar (ex: timeout), falhamos o job aqui.
            $this->fail($e);
        }
    }
    
    /**
     * Lida com a falha do job (ex: timeout ou exceção).
     */
    public function failed(Throwable $exception): void
    {
        Log::channel('stderr')->error("JOB FALHOU PERMANENTEMENTE: Comando '{$this->comando}' (Log ID {$this->logId}). Razão: " . $exception->getMessage());

        // Tenta atualizar o log uma última vez, caso o comando não tenha conseguido
        $log = ProcessamentoLog::find($this->logId);
        if ($log && $log->status !== 'falha') {
            try {
                $log->update([
                    'fim_execucao' => $log->fim_execucao ?? now(),
                    'status' => 'falha',
                    'mensagem_erro' => $log->mensagem_erro ?? ('Job falhou permanentemente: ' . $exception->getMessage()),
                ]);
            } catch (Throwable $updateError) {
                 Log::channel('stderr')->error("JOB FALHA CRÍTICA (failed method): Não foi possível atualizar o log ID {$this->logId}: " . $updateError->getMessage());
            }
        } elseif (!$log) {
             Log::channel('stderr')->error("JOB FALHA CRÍTICA (failed method): Log ID {$this->logId} não encontrado para registrar falha permanente.");
        }
    }
}