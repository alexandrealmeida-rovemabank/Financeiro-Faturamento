<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProcessamentoLog;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use App\Models\Empresa;
use Illuminate\Support\Facades\Log;
use App\Jobs\ExecutarComandoArtisanJob; // Importar o Job
// use Illuminate\Support\Facades\Bus; // Descomente se precisar de cadeias/batches

class ProcessamentoLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view reprocessamento', ['only' => ['index']]);
        $this->middleware('permission:run reprocessamento geral|run reprocessamento personalizado|run reprocessamento ultimas transações', ['only' => ['acionarProcessamentoManual']]);

        
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ProcessamentoLog::query();

            // ... (Lógica do DataTables e Filtros) ...
            $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));
            $query->when($request->filled('data_inicio'), function ($q) use ($request) {
                try { return $q->where('inicio_execucao', '>=', Carbon::parse($request->data_inicio)->startOfDay()); } catch (\Exception $e) { return $q; }
            });
            $query->when($request->filled('data_fim'), function ($q) use ($request) {
                 try { return $q->where('inicio_execucao', '<=', Carbon::parse($request->data_fim)->endOfDay()); } catch (\Exception $e) { return $q; }
            });

             return DataTables::of($query)
                ->editColumn('inicio_execucao', fn ($log) => $log->inicio_execucao?->format('d/m/Y H:i:s') ?? 'N/A')
                ->editColumn('fim_execucao', fn ($log) => $log->fim_execucao?->format('d/m/Y H:i:s') ?? 'Em execução')
                ->addColumn('duracao', fn ($log) => ($log->inicio_execucao && $log->fim_execucao) ? $log->inicio_execucao->diffForHumans($log->fim_execucao, true) : 'N/A' )
                ->editColumn('status', function ($log) { $badgeClass = match($log->status) { 'sucesso' => 'bg-success', 'falha' => 'bg-danger', 'iniciado' => 'bg-info', default => 'bg-secondary' }; return '<span class="badge ' . $badgeClass . '">' . ucfirst($log->status) . '</span>'; })
                ->addColumn('intervalo_ids', function ($log) { if (in_array($log->comando, ['faturamento:processar-transacoes', 'faturamento:reprocessar-geral', 'manual_ultimas', 'manual_geral'])) { if ($log->ultimo_id_processado_antes !== null && $log->ultimo_id_processado_depois !== null) { if ($log->ultimo_id_processado_depois > $log->ultimo_id_processado_antes) { return ($log->ultimo_id_processado_antes + 1) . ' - ' . $log->ultimo_id_processado_depois; } elseif ($log->ultimo_id_processado_depois == $log->ultimo_id_processado_antes && $log->status !== 'iniciado') { return 'Nenhum novo'; } } } elseif (in_array($log->comando, ['faturamento:reprocessar-personalizado', 'manual_personalizado']) && $log->parametros) { $params = json_decode($log->parametros, true); $clienteNome = Empresa::find($params['cliente_id'])?->razao_social ?? "ID {$params['cliente_id']}"; return "Cliente {$clienteNome} ({$params['data_inicio']} a {$params['data_fim']}) [{$params['escopo']}]"; } return 'N/A'; })
                ->addColumn('mensagem_erro_formatada', function ($log) { if ($log->mensagem_erro) { $shortMessage = \Illuminate\Support\Str::limit(explode("\n", $log->mensagem_erro)[0], 100); return '<span title="'.htmlspecialchars($log->mensagem_erro).'">' . htmlspecialchars($shortMessage) . '</span>'; } return ''; })
                ->rawColumns(['status', 'mensagem_erro_formatada'])
                ->orderColumn('inicio_execucao', fn ($query, $order) => $query->orderBy('inicio_execucao', $order))
                ->orderColumn('fim_execucao', fn ($query, $order) => $query->orderBy('fim_execucao', $order))
                ->make(true);
        }

        // Busca a coleção de clientes E unidades com os campos necessários
        $clientes = Empresa::whereIn('empresa_tipo_id', [1, 2]) // 1=Matriz, 2=Unidade
                            ->select('id', 'razao_social', 'cnpj', 'empresa_tipo_id')
                            ->orderBy('razao_social')
                            ->get();

        return view('admin.processamento.log.index', compact('clientes'));
    }

    /**
     * Aciona um comando de processamento manualmente via formulário do Modal.
     */
    public function acionarProcessamentoManual(Request $request)
    {
        $validated = $request->validate([
            'tipo_processamento' => 'required|in:ultimas,geral,personalizado',
            'cliente_id' => 'required_if:tipo_processamento,personalizado|nullable|exists:empresa,id',
            'data_inicio' => 'required_if:tipo_processamento,personalizado|nullable|date_format:Y-m-d',
            'data_fim' => 'required_if:tipo_processamento,personalizado|nullable|date_format:Y-m-d|after_or_equal:data_inicio',
            'escopo_matriz' => 'nullable|in:todos,so_matriz,so_unidades', // NOVO: Valida o escopo
        ],[
            'cliente_id.required_if' => 'O campo Cliente é obrigatório para processamento personalizado.',
            'data_inicio.required_if' => 'O campo Data Início é obrigatório para processamento personalizado.',
            'data_fim.required_if' => 'O campo Data Fim é obrigatório para processamento personalizado.',
            'data_fim.after_or_equal' => 'A Data Fim deve ser igual ou posterior à Data Início.',
        ]);

        $tipo = $validated['tipo_processamento'];
        $comando = '';
        $parametros = [];
        $logMessage = '';
        $logParams = null;

        // 1. Cria o Log primeiro para obter um ID
        $log = ProcessamentoLog::create([
            'comando' => 'manual_' . $tipo,
            'inicio_execucao' => now(),
            'status' => 'iniciado',
        ]);

        try {
            switch ($tipo) {
                case 'ultimas':
                    $comando = 'faturamento:processar-transacoes';
                    $logMessage = 'Processamento das últimas transações foi enviado para a fila.';
                    $parametros = ['--log_id' => $log->id];
                    break;

                case 'geral':
                    $comando = 'faturamento:reprocessar-geral';
                    $logMessage = 'Reprocessamento geral foi enviado para a fila.';
                    $parametros = [
                        '--log_id' => $log->id,
                        '--force' => true
                    ];
                    break;

                case 'personalizado':
                    $comando = 'faturamento:reprocessar-personalizado';
                    $parametros = [
                        '--log_id' => $log->id,
                        '--cliente' => $validated['cliente_id'],
                        '--data-inicio' => $validated['data_inicio'],
                        '--data-fim' => $validated['data_fim'],
                        '--escopo' => $validated['escopo_matriz'] ?? 'todos', // NOVO: Passa o escopo
                        '--force' => true
                    ];
                    $logMessage = "Reprocessamento personalizado foi enviado para a fila.";
                    $logParams = json_encode([ // NOVO: Salva o escopo no log
                        'cliente_id' => $validated['cliente_id'],
                        'data_inicio' => $validated['data_inicio'],
                        'data_fim' => $validated['data_fim'],
                        'escopo' => $validated['escopo_matriz'] ?? 'todos'
                    ]);
                    break;
            }

            if ($logParams) {
                $log->update(['parametros' => $logParams]);
            }

            if (!array_key_exists($comando, Artisan::all())) {
                 throw new \Exception("Comando {$comando} não encontrado.");
            }
            
            ExecutarComandoArtisanJob::dispatch($comando, $parametros, $log->id);

            return redirect()
                ->route('admin.processamento.logs.index')
                ->with('success', $logMessage . ' Acompanhe o status na tabela de logs.');

        } catch (\Exception $e) {
            Log::error("Erro ao acionar processamento manual ({$tipo}): " . $e->getMessage());
            $log->update([
                'fim_execucao' => now(),
                'status' => 'falha',
                'mensagem_erro' => "Erro ao iniciar o comando: " . $e->getMessage(),
                'parametros' => $logParams,
            ]);
            return redirect()
                ->route('admin.processamento.logs.index')
                ->with('error', 'Erro ao iniciar o processamento: ' . $e->getMessage());
        }
    }
}

