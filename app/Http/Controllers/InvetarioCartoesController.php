<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvetarioCartoesController extends Controller
{
    public function __construct()
    {
        // As permissões são mantidas, garantindo acesso controlado aos relatórios
        $this->middleware('permission:visualizar relatorio')->only([
            'index',
            'visualizarRelatorio',
            'dados'
        ]);
        $this->middleware('permission:gerar relatorio')->only([
            'gerar',
            'exportarPdf'
        ]);
    }

    public function index()
    {
        // Busca **apenas** os dados para os cards do dashboard de Cartões/Impressão
        $cardsDataCartoes = DB::selectOne('SELECT * FROM relatorio_cards_dashboard');
        $resultadoCardsCartoes = (object) $cardsDataCartoes;

        // A view agora será específica para o dashboard de cartões
        return view('inventario.cartao.index', compact('resultadoCardsCartoes'));
    }

    private function buscarDadosPorTipo($tipo)
    {
        // Retorna os dados **somente** das views relacionadas a impressões e lotes
        return match ($tipo) {
            'status-lotes' => DB::table('relatorio_status_lotes')->get(),
            'detalhe-lotes-contagem' => DB::table('relatorio_detalhe_lotes_com_contagem')->get(),
            'impressoes-cliente-subgrupo' => DB::table('relatorio_impressoes_por_cliente_subgrupo')->get(),
            'volume-impressao-diario' => DB::table('relatorio_volume_impressao_diario')->get(),
            'top-clientes-cartoes' => DB::table('relatorio_top_clientes_cartoes')->get(),
            'periodos-maior-impressao' => DB::table('relatorio_periodos_maior_impressao')->get(),
            'combustiveis-solicitados' => DB::table('relatorio_combustiveis_mais_solicitados')->get(),
            'status-lotes-por-cliente' => DB::table('relatorio_status_lotes_por_cliente')->get(),
            'media-impressoes-por-lote' => DB::table('relatorio_media_impressoes_por_lote')->get(),
            'clientes-cartoes-por-ano' => DB::table('relatorio_clientes_cartoes_por_ano')->get(),
            'carros-multiplas-solicitacoes' => DB::table('relatorio_carros_multiplas_solicitacoes_por_cliente')->get(),
            default => collect(),
        };
    }

    public function visualizarRelatorio($tipo)
    {
        $titulo = $this->gerarTitulo($tipo);
        $colunas = $this->gerarColunas($tipo); // Usando o método unificado para colunas

        $dados = $this->buscarDadosPorTipo($tipo);
        // dd($dados);

        $filtros = [];
        // Filtros **somente** para os relatórios de Cartões
        if (in_array($tipo, ['impressoes-cliente-subgrupo', 'top-clientes-cartoes', 'status-lotes-por-cliente', 'clientes-cartoes-por-ano', 'clientes-lotes-por-placa-detalhado', 'carros-multiplas-solicitacoes'])) {
            $filtros[] = ['campo' => 'cliente', 'label' => 'Cliente', 'coluna' => 'cliente', 'opcoes' => $dados->pluck('cliente')->unique()->filter()->values()];
        }
        if (in_array($tipo, ['clientes-cartoes-por-ano', 'periodos-maior-impressao'])) {
            $filtros[] = ['campo' => 'ano', 'label' => 'Ano', 'coluna' => 'ano', 'opcoes' => $dados->pluck('ano')->unique()->filter()->values()];
        }
        if (in_array($tipo, ['clientes-lotes-por-placa-detalhado', 'carros-multiplas-solicitacoes', 'placas-cliente-modelo'])) {
            $filtros[] = ['campo' => 'placa', 'label' => 'Placa', 'coluna' => 'placa', 'opcoes' => $dados->pluck('placa')->unique()->filter()->values()];
        }
        if (in_array($tipo, ['clientes-lotes-por-placa-detalhado'])) {
            $filtros[] = ['campo' => 'modelo', 'label' => 'Modelo', 'coluna' => 'modelo', 'opcoes' => $dados->pluck('modelo')->unique()->filter()->values()];
        }
        if (in_array($tipo, ['combustiveis-solicitados'])) {
            $filtros[] = ['campo' => 'combustivel', 'label' => 'Combustível', 'coluna' => 'combustivel', 'opcoes' => $dados->pluck('combustivel')->unique()->filter()->values()];
        }

        // A rota agora aponta para a view específica de cartões, que você pode querer criar
        // Ex: return view('inventario.cartao.view.template', compact('titulo', 'tipo', 'colunas', 'filtros', 'urlDados'));
        // Mantendo 'inventario.estoque.view.template' se você reutilizar a mesma estrutura para ambos
        $urlDados = route('inventario.cartao.dados', ['tipo' => $tipo]);
        $modelo_relatorio = "Cartoes";
        return view('inventario.estoque.view.template', compact('titulo', 'tipo', 'colunas', 'filtros', 'urlDados','modelo_relatorio'));
    }

    public function dados(Request $request, $tipo)
    {
        $dados = $this->buscarDadosPorTipo($tipo);

        if ($request->has('filtros')) {
            foreach ($request->filtros as $coluna => $valor) {
                if ($valor !== '') {
                    $dados = $dados->filter(function ($item) use ($coluna, $valor) {
                        if (isset($item->{$coluna})) {
                            return stripos((string)$item->{$coluna}, (string)$valor) !== false;
                        }
                        return false;
                    });
                }
            }
        }

        return datatables()->of($dados)->toJson();
    }

    private function gerarTitulo($tipo)
    {
        // Títulos **somente** dos relatórios de Cartões/Impressão
        return match ($tipo) {
            'status-lotes' => 'Visão Geral do Status dos Lotes de Impressão',
            'detalhe-lotes-contagem' => 'Detalhe de Lotes de Impressão com Contagem de Impressões',
            'impressoes-cliente-subgrupo' => 'Impressões por Cliente e Grupo/Subgrupo',
            'volume-impressao-diario' => 'Volume Diário de Impressão',
            'top-clientes-cartoes' => 'Clientes com Maior Volume de Solicitação de Cartões',
            'periodos-maior-impressao' => 'Períodos de Maior Volume de Impressão (Análise Anual e Mensal)',
            'combustiveis-solicitados' => 'Tipos de Combustível Mais Solicitados por Impressão',
            'status-lotes-por-cliente' => 'Status de Impressão de Lotes por Cliente',
            'media-impressoes-por-lote' => 'Média de Impressões por Lote',
            'clientes-cartoes-por-ano' => 'Clientes com Maior Volume de Cartões Solicitados por Ano',
            'carros-multiplas-solicitacoes' => 'Carros com Múltiplas Solicitações por Cliente',
            default => 'Relatório de Cartões', // Título padrão para qualquer tipo não mapeado
        };
    }

    // Método centralizado para definição das colunas dos relatórios de Cartões/Impressão
    private function gerarColunas($tipo)
    {
        return match($tipo) {
            'status-lotes' => ['status_impressao', 'total_lotes', 'primeiro_lote_nesse_status', 'ultimo_lote_nesse_status'],
            'detalhe-lotes-contagem' => ['id_lote', 'lote', 'cliente', 'data_importacao', 'status_impressao', 'total_impressoes_no_lote'],
            'impressoes-cliente-subgrupo' => ['cliente', 'gruposubgrupo', 'total_impressoes', 'total_lotes_envolvidos'],
            'volume-impressao-diario' => ['data_impressao', 'total_impressoes_no_dia', 'total_lotes_processados_no_dia'],
            'placas-cliente-modelo' => ['cliente', 'modelo', 'total_placas_unicas', 'total_impressoes_para_modelo'],
            'top-clientes-cartoes' => ['cliente', 'total_cartoes_solicitados', 'total_lotes_envolvidos'],
            'periodos-maior-impressao' => ['ano', 'mes', 'total_impressoes', 'total_lotes_processados'],
            'combustiveis-solicitados' => ['combustivel', 'total_impressoes', 'total_clientes_envolvidos'],
            'status-lotes-por-cliente' => ['cliente', 'status_impressao', 'total_lotes'],
            'media-impressoes-por-lote' => ['media_impressoes_por_lote'],
            'clientes-cartoes-por-ano' => ['ano', 'cliente', 'total_cartoes_solicitados', 'total_lotes_envolvidos'],
            'carros-multiplas-solicitacoes' => ['cliente', 'placa', 'modelo', 'total_solicitacoes_cartao', 'primeira_solicitacao', 'ultima_solicitacao'],
            default => [],
        };
    }

    public function gerar($tipo, $acao)
    {
        $dados = $this->buscarDadosPorTipo($tipo);
        $titulo = $this->gerarTitulo($tipo);
        $colunas = $this->gerarColunas($tipo); // Colunas obtidas do novo método centralizado

        // Define a orientação do PDF. Inclui apenas os tipos de cartões que podem precisar de 'landscape'.
        $orientacao = in_array($tipo, [
            'clientes-lotes-por-placa-detalhado',
            'carros-multiplas-solicitacoes'
        ]) ? 'landscape' : 'portrait';

        if ($acao === 'visualizar') {
            // Passa 'colunas' para a view de template para renderização dinâmica
            return view('inventario.estoque.view.template', compact('dados', 'titulo', 'tipo', 'colunas')); // Se a view for 'inventario.cartao.pdf.template' ajuste aqui
        }

        if ($acao === 'baixar') {
            // Passa 'colunas' para a view de PDF template
            $pdf = Pdf::loadView('inventario.estoque.pdf.template', compact('dados', 'titulo', 'tipo', 'colunas')) // Se a view for 'inventario.cartao.pdf.template' ajuste aqui
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
    $colunas = $this->gerarColunas($tipo);

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