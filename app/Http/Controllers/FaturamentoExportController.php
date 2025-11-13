<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\TransacaoFaturamento;
use App\Models\ParametroCliente;
use App\Models\ParametroGlobal;
use App\Models\ParametroTaxaAliquota;
use App\Exports\TransacoesExport; // Vamos criar este arquivo
use Maatwebsite\Excel\Facades\Excel;
//use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;
use App\Models\Fatura;
use Illuminate\Support\Facades\Auth; // <<< ADICIONE ESTE
use App\Jobs\GerarFaturaPdfJob; // <<< ADICIONE ESTE


class FaturamentoExportController extends Controller
{
    /**
     * Constrói a query base de transações.
     */
    private function buildTransacoesQuery(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
        ]);

        $billable_empresa_id = $request->input('cliente_id');
        $empresa = Empresa::find($billable_empresa_id);
        
        $dataInicio = Carbon::createFromFormat('Y-m', $request->periodo)->startOfMonth();
        $dataFim = $dataInicio->copy()->endOfMonth();

        $query = TransacaoFaturamento::with([
                'credenciado', 
                'produto', 
                'empenho', 
                'veiculo.grupo.grupoPai'
            ])
            ->whereBetween('data_transacao', [$dataInicio, $dataFim])
            ->whereIn('status', ['confirmada', 'liquidada']);
        
        if ($empresa->empresa_tipo_id == 1) {
            $query->where('cliente_id', $empresa->id)->whereNull('unidade_id');
        } else {
            $query->where('unidade_id', $empresa->id);
        }

        return $query;
    }

    /**
     * Helper para buscar parâmetros e taxas
     */
    private function getParametrosETaxas($billable_empresa_id)
    {
        $empresa = Empresa::with('organizacao', 'matriz.organizacao')->find($billable_empresa_id);
        $paramGlobal = ParametroGlobal::first();

        // Lógica de Parâmetros (simplificada de FaturamentoController)
        $publico_ids = [1, 2, 3, 5];
        $is_publico = in_array($empresa->organizacao_id, $publico_ids);
        
        $parametro_owner_id = ($empresa->empresa_tipo_id == 2) ? $empresa->empresa_matriz_id : $empresa->id;
        $paramCliente = ParametroCliente::where('empresa_id', $parametro_owner_id)->first();
        
        $parametrosAtivos = [
            'isento_ir' => false,
        ];

        if ($paramCliente && !$paramCliente->ativar_parametros_globais) {
            $parametrosAtivos['isento_ir'] = $paramCliente->isento_ir;
        }

        // Lógica de Taxas
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
     * Exportar para Excel (XLS)
     */
    public function exportXLS(Request $request)
    {
        $cliente_id = $request->input('cliente_id');
        $periodo = $request->input('periodo');
        $cliente = Empresa::find($cliente_id);
        
        $query = $this->buildTransacoesQuery($request);
        extract($this->getParametrosETaxas($cliente_id)); // Pega $parametrosAtivos e $taxas

        $fileName = "transacoes_{$cliente->razao_social}_{$periodo}.xlsx";

        return Excel::download(new TransacoesExport($query, $parametrosAtivos, $taxas), $fileName);
    }

    /**
     * Exportar para PDF
     */
/**
     * Exportar para PDF
     */
    public function exportPDF(Request $request)
    {
        // Aumenta o limite de tempo
        set_time_limit(300);
        ini_set('memory_limit', '2G');

        $cliente_id = $request->input('cliente_id');
        $periodo = $request->input('periodo');
        $cliente = Empresa::find($cliente_id);

        $query = $this->buildTransacoesQuery($request);
        extract($this->getParametrosETaxas($cliente_id)); // Pega $parametrosAtivos e $taxas

        // Processa os dados manualmente para o PDF
        $transacoes = $query->get()->map(function($row) use ($parametrosAtivos, $taxas) {
            $aliquota_ir = 0;
            if (!$parametrosAtivos['isento_ir']) {
                $categoriaId = optional($row->produto)->produto_categoria_id;
                $taxa = $taxas->get($categoriaId);
                $aliquota_ir = $taxa ? $taxa->taxa_aliquota : 0;
            }
            
            $row->faturada_texto = $row->status_faturamento == 'pendente' ? 'Não' : 'Sim';
            $row->data_formatada = Carbon::parse($row->data_transacao)->format('d/m/Y H:i');
            $row->credenciado_nome = optional($row->credenciado)->razao_social ?? 'N/A';
            $row->grupo_nome = optional(optional(optional($row->veiculo)->grupo)->grupoPai)->nome ?? 'N/A';
            $row->subgrupo_nome = optional(optional($row->veiculo)->grupo)->nome ?? 'N/A';
            $row->produto_nome = optional($row->produto)->nome ?? 'N/A';
            $row->placa = optional($row->veiculo)->placa ?? 'N/A';
            $row->aliquota_formatada = number_format($aliquota_ir * 100, 2, ',', '.') . '%';
            $row->valor_ir_calculado = 'R$ ' . number_format($row->valor_total * $aliquota_ir, 2, ',', '.');
            $row->valor_bruto = 'R$ ' . number_format($row->valor_total, 2, ',', '.');
            
            return $row;
        });
        
        // Pega o ParametroGlobal para o rodapé
        $paramGlobal = ParametroGlobal::first();

        $data = [
            'transacoes' => $transacoes,
            'cliente' => $cliente,
            'periodo' => Carbon::createFromFormat('Y-m', $periodo)->locale('pt_BR')->translatedFormat('F/Y'),
            'paramGlobal' => $paramGlobal, // <<< ADICIONADO
        ];
        
        // --- INÍCIO DA MUDANÇA ---
        
        // Renderiza as views de header e footer
        $headerHtml = view('admin.faturamento.exports.transacoes_header', $data)->render();
        $footerHtml = view('admin.faturamento.exports.fatura_footer', $data)->render();
        
        $fileName = "transacoes_pdf_{$cliente->razao_social}_{$periodo}.pdf";

        // Carrega a view principal e INJETA o header/footer
        $pdf = PDF::loadView('admin.faturamento.exports.transacoes_pdf', $data)
                  ->setPaper('a4', 'portrait')
                  ->setOption('enable-local-file-access', true)
                  ->setOption('enable-external-links', true)
                  
                  // Define as margens (em mm)
                  ->setOption('margin-top', '35mm')     // Espaço para o header
                  ->setOption('margin-bottom', '35mm')  // Espaço para o footer
                  ->setOption('margin-left', '10mm')     // Zera as margens da página
                  ->setOption('margin-right', '10mm')    // Zera as margens da página
                  
                  // Passa o HTML renderizado
                  ->setOption('header-html', $headerHtml)
                  ->setOption('footer-html', $footerHtml)
                  
                  // Zera o espaçamento do footer
                  ->setOption('header-spacing', 5)
                  ->setOption('footer-spacing', 5);
        // --- FIM DA MUDANÇA ---
        
        return $pdf->stream($fileName);
    }

public function exportFaturaPDF(Request $request, Fatura $fatura)
     {
         // Aumenta o limite de tempo desta rota específica para 5 minutos (300 segundos)
         set_time_limit(300);

         // <<<--- ADICIONE ESTA LINHA ---
         ini_set('memory_limit', '2G'); // 2 Gigabytes

         // 1. Carrega relações
         $fatura->load([
             'cliente.municipio.estado', 
             'itens.transacao' => function($query) {
                 $query->with(['credenciado', 'produto', 'veiculo.grupo.grupoPai']);
             },
             'descontos.usuario', 
             'pagamentos'
         ]);

         // 2. Busca dados globais
         $paramGlobal = ParametroGlobal::first();
         extract($this->getParametrosETaxas($fatura->cliente_id)); // Pega $parametrosAtivos e $taxas
         $totalDescontosManuais = $fatura->valor_descontos_manuais;

         // 3. <<< OTIMIZAÇÃO: Pré-processa as transações aqui >>>
         $transacoesProcessadas = $fatura->itens->map(function($item) use ($parametrosAtivos, $taxas) {
             $tr = $item->transacao; // A transação já foi carregada
             $aliquota_ir = 0;
             $valor_ir_num = 0;

             if ($tr && !$parametrosAtivos['isento_ir']) {
                 $categoriaId = optional($tr->produto)->produto_categoria_id;
                 $taxa = $taxas->get($categoriaId);
                 $aliquota_ir = $taxa ? $taxa->taxa_aliquota : 0;
             }
             
             if ($tr) { // Calcula o valor do IR apenas se a transação existir
                $valor_ir_num = $item->valor_subtotal * $aliquota_ir;
             }

             // Retorna um objeto simples SÓ com as strings prontas
             return (object) [
                 'id' => $tr->id ?? $item->id,
                 'data' => $tr ? $tr->data_transacao->format('d/m/y H:i') : 'N/A',
                 'credenciado' => optional(optional($tr)->credenciado)->nome ?? 'N/A',
                 'grupo' => optional(optional(optional(optional($tr)->veiculo)->grupo)->grupoPai)->nome ?? 'N/A',
                 'subgrupo' => optional(optional(optional($tr)->veiculo)->grupo)->nome ?? 'N/A',
                 'produto' => $item->descricao_produto,
                 'placa' => optional(optional($tr)->veiculo)->placa ?? 'N/A',
                 'valor_bruto' => number_format($item->valor_subtotal, 2, ',', '.'),
                 'aliquota_ir' => number_format($aliquota_ir * 100, 2, ',') . '%',
                 'valor_ir' => number_format($valor_ir_num, 2, ',', '.'),
             ];
         });
         // --- Fim da Otimização ---

         // 4. Prepara os dados para TODAS as views
         $data = [
             'fatura' => $fatura,
             'paramGlobal' => $paramGlobal,
             'totalDescontosManuais' => $totalDescontosManuais,
             'transacoes' => $transacoesProcessadas, 
         ];

         // --- INÍCIO DA GRANDE MUDANÇA ---

         // 5. Renderiza as views de header e footer para strings HTML
         // (Certifique-se que os caminhos estão corretos)
         $headerHtml = view('admin.faturamento.exports.fatura_header', $data)->render();
         $footerHtml = view('admin.faturamento.exports.fatura_footer', $data)->render();
        
         $fileName = "fatura_{$fatura->numero_fatura}_{$fatura->cliente->razao_social}.pdf";

         // 6. Carrega a view principal e INJETA o header/footer via setOption
         $pdf = PDF::loadView('admin.faturamento.exports.fatura_pdf', $data)
                   ->setPaper('a4', 'portrait')
                   ->setOption('enable-local-file-access', true) // Permite carregar imagens locais
                   ->setOption('enable-external-links', true)
                   
                   // Define as margens (em mm) para acomodar o header/footer
                   ->setOption('margin-top', '35mm')     // Espaço para o header (aprox 120px)
                   ->setOption('margin-bottom', '35mm')  // Espaço para o footer

                   ->setOption('margin-left', '10mm')    // Margem da página (aprox 40px)
                   ->setOption('margin-right', '10mm')   // Margem da página (aprox 40px)
                   
                   // Passa o HTML renderizado para o wkhtmltopdf
                   ->setOption('header-html', $headerHtml)
                   ->setOption('footer-html', $footerHtml)
                   
                   // (Opcional) Define o espaçamento entre o conteúdo e o header/footer
                   ->setOption('header-spacing', 5) // 5mm de espaço
                   ->setOption('footer-spacing', 5); // 5mm de espaço
        // --- FIM DA GRANDE MUDANÇA ---
        
         return $pdf->stream($fileName);
     }

    // public function exportFaturaPDF(Request $request, Fatura $fatura)
    // {
    //     // 1. Pega o usuário logado
    //     $user = Auth::user();

    //     // 2. Dispara o Job para a fila
    //     GerarFaturaPdfJob::dispatch($fatura, $user);

    //     // 3. Retorna uma resposta imediata para o JavaScript
    //     return response()->json([
    //         'success' => true, 
    //         'message' => 'Sua fatura está sendo gerada. Você será notificado no "sininho" quando estiver pronta.'
    //     ]);
    // }
}