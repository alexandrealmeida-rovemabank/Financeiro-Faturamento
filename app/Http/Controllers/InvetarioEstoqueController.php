<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvetarioEstoqueController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:visualizar relatorio')->only(['index', 'visualizarRelatorio', 'dados']);
        $this->middleware('permission:gerar relatorio')->only(['baixarPDF', 'gerar', 'exportarPdf']);
    }

    public function index()
    {
        $resultados = DB::select('SELECT * FROM registrosCardsRelatorioEstoque');
        $resultado = $resultados[0];
        $user = auth()->user();

        return view('inventario.estoque.index', compact('user', 'resultado'));
    }

    private function buscarDadosPorTipo($tipo)
    {
        return match ($tipo) {
            'por-modelo' => DB::table('quantidadeestoquepormodelo')->get(),
            'por-lote' => DB::table('quantidadeestoqueporlote')->get(),
            'por-credenciado' => DB::table('vw_terminais_por_credenciado')->get(),
            'por-fabricante' => DB::table('quantidadeestoqueporfabricante')->get(),
            'por-status' => DB::table('quantidadeestoqueporstatus')->get(),
            'por-vinculo-mes' => DB::table('vw_por_status_vinculos_por_mes')->get(),
            'por-sistema' => DB::table('vw_terminais_por_sistema')->get(),
            'por-status-vinculado' => DB::table('vw_terminais_vinculados')->get(),
            'por-status-vinculado-grupo-rovema' => DB::table('vw_terminais_grupo_rovema')->get(),
            default => collect(),
        };
    }

public function visualizarRelatorio($tipo)
{
    $titulo = $this->gerarTitulo($tipo);

    $colunas = match($tipo) {
        'por-modelo' => ['modelo', 'quantidade'],
        'por-lote' => ['lote', 'quantidade'],
        'por-status' => ['status', 'quantidade'],
        'por-fabricante' => ['fabricante', 'quantidade'],
        'por-credenciado' => ['razao_social', 'cnpj', 'vinculado', 'desvinculado'],
        'por-vinculo-mes' => ['mes_ano', 'status', 'total'],
        'por-sistema' => ['sistema', 'vinculado', 'desvinculado'],
        'por-status-vinculado', 'por-status-vinculado-grupo-rovema' => [
            'razao_social', 'cnpj', 'numero_serie', 'chip', 'produto', 'status',
            'created_at', 'updated_at', 'sistema'
        ],
        default => [],
    };

    // Para os filtros, você decide: pegar dados ou deixar vazio e filtrar no DataTables
   $dados = $this->buscarDadosPorTipo($tipo);

    $filtros = [];

// Exemplo de filtros automáticos:
    
    if ($tipo === 'por-vinculo-mes') {
        $filtros[] = [
            'campo' => 'mes_ano',
            'label' => 'Mês/Ano',
            'coluna' => 'mes_ano',
            'opcoes' => $dados->pluck('mes_ano')->unique()->filter()->values()
        ];
        $filtros[] = [
            'campo' => 'status',
            'label' => 'Status',
            'coluna' => 'status',
            'opcoes' => $dados->pluck('status')->unique()->filter()->values()
        ];
    }

    if ($tipo === 'por-sistema') {
        $filtros[] = [
            'campo' => 'sistema',
            'label' => 'Sistema',
            'coluna' => 'sistema',
            'opcoes' => $dados->pluck('sistema')->unique()->filter()->values()
        ];
    }

    if (in_array($tipo, ['por-status-vinculado', 'por-status-vinculado-grupo-rovema'])) {
        $filtros[] = [
            'campo' => 'razao_social',
            'label' => 'Credenciado',
            'coluna' => 'razao_social',
            'opcoes' => $dados->pluck('razao_social')->unique()->filter()->values()
        ];
        $filtros[] = [
            'campo' => 'status',
            'label' => 'Status',
            'coluna' => 'status',
            'opcoes' => $dados->pluck('status')->unique()->filter()->values()
        ];
        $filtros[] = [
            'campo' => 'produto',
            'label' => 'Produto',
            'coluna' => 'produto',
            'opcoes' => $dados->pluck('produto')->unique()->filter()->values()
        ];
        $filtros[] = [
            'campo' => 'sistema',
            'label' => 'Sistema',
            'coluna' => 'sistema',
            'opcoes' => $dados->pluck('sistema')->unique()->filter()->values()
        ];
    }


    $urlDados = route('inventario.dados', ['tipo' => $tipo]);
    $modelo_relatorio = "Estoque";
    return view('inventario.estoque.view.template', compact('titulo', 'tipo', 'colunas', 'filtros', 'urlDados','modelo_relatorio'));
}
public function dados(Request $request, $tipo)
{
    $dados = $this->buscarDadosPorTipo($tipo);

    if ($request->has('filtros')) {
        foreach($request->filtros as $coluna => $valor){
            if($valor !== '') {
                $dados = $dados->filter(function($item) use ($coluna, $valor) {
                    return stripos($item->{$coluna} ?? '', $valor) !== false;
                });
            }
        }
    }

    return datatables()->of($dados)->toJson();
}

    private function gerarTitulo($tipo)
    {
        return match ($tipo) {
            'por-modelo' => 'Relatório de Terminais por Modelo',
            'por-lote' => 'Relatório de Terminais por Lote',
            'por-credenciado' => 'Relatório de Terminais por Credenciado',
            'por-fabricante' => 'Relatório de Terminais por Fabricante',
            'por-status' => 'Relatório de Terminais por Status',
            'por-vinculo-mes' => 'Relatório de Terminais Vinculados por Mês',
            'por-sistema' => 'Relatório de Terminais por Sistema',
            'por-status-vinculado' => 'Relatório Geral de Terminais Vinculados',
            'por-status-vinculado-grupo-rovema' => 'Relatório Geral de Terminais Vinculados - Grupo Rovema',
            default => 'Relatório',
        };
    }

    private function gerarColunas($tipo)
    {
        return match($tipo) {
            'por-modelo' => ['modelo', 'quantidade'],
            'por-lote' => ['lote', 'quantidade'],
            'por-status' => ['status', 'quantidade'],
            'por-fabricante' => ['fabricante', 'quantidade'],
            'por-credenciado' => ['razao_social', 'cnpj', 'vinculado', 'desvinculado'],
            'por-vinculo-mes' => ['mes_ano', 'status', 'total'],
            'por-sistema' => ['sistema', 'vinculado', 'desvinculado'],
            'por-status-vinculado', 'por-status-vinculado-grupo-rovema' => [
                'razao_social', 'cnpj', 'numero_serie', 'chip', 'produto', 'status',
                'created_at', 'updated_at', 'sistema'
            ],
            default => [],
        };
    }

    public function gerar($tipo, $acao)
    {
        $dados = $this->buscarDadosPorTipo($tipo);
        $titulo = $this->gerarTitulo($tipo);
        $colunas = $this->gerarColunas($tipo);
        $orientacao = in_array($tipo, ['por-status-vinculado', 'por-status-vinculado-grupo-rovema']) ? 'landscape' : 'portrait';

        if ($acao === 'visualizar') {
            return view('inventario.estoque.view.template', compact('dados', 'titulo', 'tipo', 'colunas'));
        }

        if ($acao === 'baixar') {
            $pdf = Pdf::loadView('inventario.estoque.pdf.template', compact('dados', 'titulo', 'tipo', 'colunas'))
                ->setPaper('a4', $orientacao);

            $dompdf = $pdf->getDomPDF();
            $dompdf->render();

            $canvas = $dompdf->getCanvas();
            $font = $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');

            if ($orientacao === 'landscape') {
                $canvas->page_text(770, 580, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 10, [0, 0, 0]);
            } else {
                $canvas->page_text(520, 820, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 10, [0, 0, 0]);
            }

            return $pdf->stream("$titulo.pdf");
        }

        abort(404);
    }

    private function obterColunasPorTipo($tipo)
{
    return match($tipo) {
        'por-modelo' => ['modelo', 'quantidade'],
        'por-lote' => ['lote', 'quantidade'],
        'por-status' => ['status', 'quantidade'],
        'por-fabricante' => ['fabricante', 'quantidade'],
        'por-credenciado' => ['razao_social', 'cnpj', 'vinculado', 'desvinculado'],
        'por-vinculo-mes' => ['mes_ano', 'status', 'total'],
        'por-sistema' => ['sistema', 'vinculado', 'desvinculado'],
        'por-status-vinculado', 'por-status-vinculado-grupo-rovema' => [
            'razao_social', 'cnpj', 'numero_serie', 'chip', 'produto', 'status',
            'created_at', 'updated_at', 'sistema'
        ],
        default => [],
    };
}

public function exportarPdf(Request $request, $tipo)
{
    $dados = $this->buscarDadosPorTipo($tipo);
    $orientacao = in_array($tipo, ['por-status-vinculado', 'por-status-vinculado-grupo-rovema']) ? 'landscape' : 'portrait';

    if ($request->has('filtros')) {
        foreach($request->filtros as $coluna => $valor){
            if($valor !== ''){
                $dados = $dados->filter(function($item) use ($coluna, $valor) {
                    return stripos($item->{$coluna} ?? '', $valor) !== false;
                });
            }
        }
    }

    $titulo = $this->gerarTitulo($tipo);
    $colunas = $this->obterColunasPorTipo($tipo);

    $pdf = Pdf::loadView('inventario.estoque.pdf.template', compact('dados', 'titulo', 'tipo', 'colunas'))
            ->setPaper('A4', $orientacao);

    $dompdf = $pdf->getDomPDF();
    $dompdf->render();

    $canvas = $dompdf->getCanvas();
    $font = $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');

    if ($orientacao === 'landscape'){
        $canvas->page_text(750, 580, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 10, [0, 0, 0]);
    } else {
        $canvas->page_text(520, 820, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 10, [0, 0, 0]);
    }

    return $pdf->download("$titulo.pdf");
}


}