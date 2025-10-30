<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// Adicionando os Models para referência, embora a query use Query Builder
use App\Models\TransacaoFaturamento;
use App\Models\Empresa;
use App\Models\Produto;
use App\Models\ParametroCliente;
use App\Models\ParametroTaxaAliquota;

class FaturamentoController extends Controller
{
    /**
     * Exibe a tela inicial de faturamento com os valores agrupados.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // ATUALIZADO: Usando a tabela 'contas_receber.transacao_faturamento'
        $query = DB::table('contas_receber.transacao_faturamento as t')
            
            // Join com Cliente (para nome e organizacao_id)
            ->join('public.empresa as c', 't.cliente_id', '=', 'c.id')
            
            // Join com Produto (para produto_categoria_id)
            ->join('public.produto as p', 't.produto_id', '=', 'p.id')
            
            // Join com Aliquotas (para taxa_aliquota)
            // Usando organizacao_id do cliente e produto_categoria_id do produto
            ->join('contas_receber.parametro_taxa_aliquota as aliquota', function ($join) {
                $join->on('aliquota.organizacao_id', '=', 'c.organizacao_id')
                     ->on('aliquota.produto_categoria_id', '=', 'p.produto_categoria_id');
            })
            
            // Left Join com Parametros do Cliente (para verificar isenção de IR)
            // Assumindo 'public.parametro_cliente' com base no Model Empresa.php
            ->leftJoin('contas_receber.parametro_cliente as pc', 'c.id', '=', 'pc.empresa_id')

            // A documentação menciona "transações CONFIRMADAS".
            ->where('t.status', 'CONFIRMADA')
            
            ->select(
                'c.nome as cliente_nome',
                't.cliente_id',
                // ATUALIZADO: Usando 'data_transacao' da nova tabela
                DB::raw('EXTRACT(YEAR FROM t.data_transacao) as ano'),
                DB::raw('EXTRACT(MONTH FROM t.data_transacao) as mes'),
                DB::raw('SUM(t.valor_total) as valor_bruto_total'),
                
                // ATUALIZADO: Lógica de cálculo do IR
                // 1. Verifica se o cliente é isento (pc.isento_ir = true). Se for, IR é 0.
                // 2. Se não for isento (ou não tiver parâmetro), calcula (valor_total * taxa_aliquota).
                DB::raw('SUM(
                    CASE 
                        WHEN COALESCE(pc.isento_ir, false) = true THEN 0 
                        ELSE (t.valor_total * aliquota.taxa_aliquota) 
                    END
                ) as ir_total')
            )
            ->groupBy('c.nome', 't.cliente_id', 'ano', 'mes')
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->orderBy('cliente_nome');

        $agrupamentos = $query->get();

        // Formata os meses para exibição
        $agrupamentos->map(function ($item) {
            // Cria um nome de mês legível
            $item->mes_ano = $this->formatarMesAno($item->mes, $item->ano);
            return $item;
        });

        return view('admin.faturamento.index', compact('agrupamentos'));
    }

    /**
     * Função helper para formatar o mês/ano.
     *
     * @param int $mes
     * @param int $ano
     * @return string
     */
    private function formatarMesAno($mes, $ano)
    {
        // Define os nomes dos meses em português
        $nomesMeses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        
        return ($nomesMeses[(int)$mes] ?? 'Desconhecido') . ' / ' . $ano;
    }
}

