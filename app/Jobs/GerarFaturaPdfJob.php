<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// Imports necessários para gerar o PDF
use App\Models\Fatura;
use App\Models\User;
use App\Models\ParametroGlobal;
use App\Models\ParametroCliente;
use App\Models\ParametroTaxaAliquota;
use App\Models\Empresa;
use App\Notifications\FaturaProntaNotification;
use Illuminate\Support\Facades\Storage;
// use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;


class GerarFaturaPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fatura;
    protected $user; // O usuário que solicitou

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Fatura $fatura, User $user)
    {
        $this->fatura = $fatura;
        $this->user = $user;
    }

    /**
     * (HELPER) Copiado do FaturamentoExportController.
     */
    private function getParametrosETaxas($billable_empresa_id)
    {
        $empresa = Empresa::with('organizacao', 'matriz.organizacao')->find($billable_empresa_id);
        $paramGlobal = ParametroGlobal::first();
        $publico_ids = [1, 2, 3, 5];
        $is_publico = in_array($empresa->organizacao_id, $publico_ids);
        $parametro_owner_id = ($empresa->empresa_tipo_id == 2) ? $empresa->empresa_matriz_id : $empresa->id;
        $paramCliente = ParametroCliente::where('empresa_id', $parametro_owner_id)->first();
        
        $parametrosAtivos = [ 'isento_ir' => false ];
        if ($paramCliente && !$paramCliente->ativar_parametros_globais) {
            $parametrosAtivos['isento_ir'] = $paramCliente->isento_ir;
        }

        $matriz = ($empresa->empresa_tipo_id == 2) ? $empresa->matriz : $empresa;
        $organizacao_id_para_taxa = $matriz ? $matriz->organizacao_id : null;
        $taxas = collect();
        if ($organizacao_id_para_taxa) {
             $taxas = ParametroTaxaAliquota::where('organizacao_id', $organizacao_id_para_taxa)
                         ->get()
                         ->keyBy('produto_categoria_id');
        }
        return compact('parametrosAtivos', 'taxas');
    }

    /**
     * Execute the job.
     * (Esta é a lógica que estava no seu Controller)
     *
     * @return void
     */
    public function handle()
    {
        // 1. Aumenta os limites para este Job
        set_time_limit(300); // 5 minutos
        //ini_set('memory_limit', '2G'); // 2GB

        // 2. Carrega as relações (como no controller)
        $this->fatura->load([
            'cliente.municipio.estado', 
            'itens.transacao' => function($query) {
                $query->with(['credenciado', 'produto', 'veiculo.grupo.grupoPai']);
            },
            'descontos.usuario', 
            'pagamentos'
        ]);

        // 3. Busca dados globais
        $paramGlobal = ParametroGlobal::first();
        extract($this->getParametrosETaxas($this->fatura->cliente_id)); // Pega $parametrosAtivos e $taxas
        $totalDescontosManuais = $this->fatura->valor_descontos_manuais;

        // 4. Pré-processa as transações (Otimização)
        $transacoesProcessadas = $this->fatura->itens->map(function($item) use ($parametrosAtivos, $taxas) {
            $tr = $item->transacao;
            $aliquota_ir = 0;
            if ($tr && !$parametrosAtivos['isento_ir']) {
                $categoriaId = optional($tr->produto)->produto_categoria_id;
                $taxa = $taxas->get($categoriaId);
                $aliquota_ir = $taxa ? $taxa->taxa_aliquota : 0;
            }
            $valor_ir_num = $item->valor_subtotal * $aliquota_ir;

            return (object) [
                'id' => $tr->id ?? $item->id,
                'data' => $tr ? $tr->data_transacao->format('d/m/y H:i') : 'N/A',
                'credenciado' => $tr->credenciado->nome ?? 'N/A',
                'grupo' => $tr->veiculo->grupo->grupoPai->nome ?? 'N/A',
                'subgrupo' => $tr->veiculo->grupo->nome ?? 'N/A',
                'produto' => $item->descricao_produto,
                'placa' => $tr->veiculo->placa ?? 'N/A',
                'valor_bruto' => number_format($item->valor_subtotal, 2, ',', '.'),
                'aliquota_ir' => number_format($aliquota_ir * 100, 1, ',') . '%',
                'valor_ir' => number_format($valor_ir_num, 2, ',', '.'),
            ];
        });

        $data = [
            'fatura' => $this->fatura,
            'paramGlobal' => $paramGlobal,
            'totalDescontosManuais' => $totalDescontosManuais,
            'transacoes' => $transacoesProcessadas,
        ];

// <<<--- ESTA É A MUDANÇA ---
        // 5. Gera o PDF usando o Snappy
        $pdf = PDF::loadView('admin.faturamento.exports.fatura_pdf', $data)
                  ->setPaper('a5', 'portrait')
                  ->setOption('enable-local-file-access', true); // Permite carregar imagens locais
        // --- FIM DA MUDANÇA ---

        // 6. Define o nome e SALVA no Storage
        $fileName = "faturas_pdf/fatura_{$this->fatura->numero_fatura}.pdf";
        Storage::disk('public')->put($fileName, $pdf->output());

        // 7. Notifica o usuário
        $this->user->notify(new FaturaProntaNotification($this->fatura, $fileName));
    }
}