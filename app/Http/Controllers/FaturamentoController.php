<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Fatura;
use App\Models\FaturaItem;
use App\Models\FaturamentoPeriodo;
use App\Models\ParametroCliente;
use App\Models\ParametroGlobal;
use App\Models\TransacaoFaturamento;
use App\Models\ParametroTaxaAliquota;
use App\Models\Empenho;
use App\Models\Produto;
use App\Models\Grupo;
use App\Models\ProdutoCategoria;
use App\Models\Municipio;
use App\Models\Estado;
use App\Models\Veiculo;
use App\Models\Organizacao;
use App\Models\Contrato;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class FaturamentoController extends Controller
{
    // ===================================================================
    // ETAPA 1: TELA INICIAL (FILTRO + TABELA RESUMO)
    // ===================================================================

    public function index(Request $request)
    {
        // --- 1. SE FOR UMA REQUISIÇÃO AJAX (DATATABLES) ---
        if ($request->ajax()) {
            
            $periodo = $request->input('periodo', Carbon::now()->subMonth()->format('Y-m'));
            try {
                $dataInicio = Carbon::createFromFormat('Y-m', $periodo)->startOfMonth();
                $dataFim = $dataInicio->copy()->endOfMonth();
            } catch (\Exception $e) {
                $periodo = Carbon::now()->subMonth()->format('Y-m');
                $dataInicio = Carbon::now()->subMonth()->startOfMonth();
                $dataFim = $dataInicio->copy()->endOfMonth();
            }

            $query = Empresa::whereIn('empresa_tipo_id', [1, 2]);

            // --- APLICA OS FILTROS DA VIEW ---
            $query->when($request->filled('cnpj'), fn($q) => $q->where('cnpj', $request->cnpj));
            $query->when($request->filled('razao_social'), fn($q) => $q->where('razao_social', $request->razao_social));
            $query->when($request->filled('municipio_id'), fn($q) => $q->where('municipio_id', $request->municipio_id));
            $query->when($request->filled('estado'), fn($q) => $q->whereHas('municipio.estado', fn($q2) => $q2->where('sigla', $request->estado)));
            $query->when($request->filled('organizacao'), fn ($q) => $q->where('organizacao_id', $request->organizacao));
            
            // --- FILTRO TIPO ORGANIZAÇÃO (PÚBLICO/PRIVADO) ---
            
            // --- CORREÇÃO: IDs baseados na sua tabela: 1,2,3,5 = Público; 4 = Privado ---
            $publico_ids = [1, 2, 3, 5]; // 1=Federal, 2=Estadual, 3=Municipal, 5=Economia Mista
            
            $query->when($request->filled('tipo_organizacao'), function ($q) use ($request, $publico_ids) {
                if ($request->tipo_organizacao == 'publica') {
                    $q->whereIn('organizacao_id', $publico_ids);
                } elseif ($request->tipo_organizacao == 'privada') {
                    $q->whereNotIn('organizacao_id', $publico_ids); // ID 4 (Privada) será selecionado
                }
            });
            
            // --- LÓGICA DE FILTRO DE STATUS (COM CORREÇÃO) ---
            $query->when($request->filled('status'), function($q) use ($request, $periodo, $dataInicio, $dataFim) {
                $status = $request->status;

                // Helper: Tem Transações Pendentes (Usa orWhereHas, precisa do wrapper)
                $hasPendentes = function($builder) use ($dataInicio, $dataFim) {
                    $builder->where(function($q_inner) use ($dataInicio, $dataFim) {
                        $q_inner->whereHas('transacoes', function($t) use ($dataInicio, $dataFim) {
                            $t->whereBetween('data_transacao', [$dataInicio, $dataFim])
                              ->whereIn('status', ['confirmada', 'liquidada'])
                              ->whereNull('unidade_id')
                              ->whereNull('fatura_id');
                        })
                        ->orWhereHas('transacoesUnidade', function($t) use ($dataInicio, $dataFim) {
                             $t->whereBetween('data_transacao', [$dataInicio, $dataFim])
                              ->whereIn('status', ['confirmada', 'liquidada'])
                              ->whereNull('fatura_id');
                        });
                    });
                };

                // Helper: Nenhuma Transação Pendente (Usa AND, não precisa de wrapper)
                $noPendentes = function($builder) use ($dataInicio, $dataFim) {
                    $builder->whereDoesntHave('transacoes', function($t) use ($dataInicio, $dataFim) {
                        $t->whereBetween('data_transacao', [$dataInicio, $dataFim])
                          ->whereIn('status', ['confirmada', 'liquidada'])
                          ->whereNull('unidade_id')
                          ->whereNull('fatura_id');
                    })
                    ->whereDoesntHave('transacoesUnidade', function($t) use ($dataInicio, $dataFim) {
                         $t->whereBetween('data_transacao', [$dataInicio, $dataFim])
                          ->whereIn('status', ['confirmada', 'liquidada'])
                          ->whereNull('fatura_id');
                    });
                };
                
                // Helper: Faturas no período (count > 0)
                $hasFaturas = function($builder) use ($periodo) {
                    $builder->whereHas('faturas', fn ($q_fat) => $q_fat->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo));
                };
                // Helper: Nenhuma Fatura no período (count = 0)
                $noFaturas = function($builder) use ($periodo) {
                    $builder->whereDoesntHave('faturas', fn ($q_fat) => $q_fat->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo));
                };
                // Helper: Todas as faturas do período estão pagas
                $allFaturasPagas = function($builder) use ($periodo) {
                    $builder->whereHas('faturas', fn($q_fat) => $q_fat->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo))
                      ->whereDoesntHave('faturas', function ($q_fat) use ($periodo) {
                        $q_fat->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo)
                              ->where('status', '!=', 'recebida');
                    });
                };
                 // Helper: Tem faturas, mas nem todas estão pagas
                 $someFaturasNaoPagas = function($builder) use ($periodo) {
                    $builder->whereHas('faturas', function ($q_fat) use ($periodo) {
                        $q_fat->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo)
                              ->where('status', '!=', 'recebida');
                    });
                };
                
                // Helper: Teve transações brutas no período (Usa orWhereHas, precisa do wrapper)
                $hasTransacoesBrutas = function($builder) use ($dataInicio, $dataFim) {
                    $builder->where(function($q_inner) use ($dataInicio, $dataFim) {
                        $q_inner->whereHas('transacoes', function($t) use ($dataInicio, $dataFim) {
                            $t->whereBetween('data_transacao', [$dataInicio, $dataFim])
                            ->whereIn('status', ['confirmada', 'liquidada'])
                            ->whereNull('unidade_id');
                        })
                        ->orWhereHas('transacoesUnidade', function($t) use ($dataInicio, $dataFim) {
                            $t->whereBetween('data_transacao', [$dataInicio, $dataFim])
                            ->whereIn('status', ['confirmada', 'liquidada']);
                        });
                    });
                };

                // Helper: NÃO teve transações brutas no período (Usa AND, não precisa de wrapper)
                $noTransacoesBrutas = function($builder) use ($dataInicio, $dataFim) {
                    $builder->whereDoesntHave('transacoes', function($t) use ($dataInicio, $dataFim) {
                        $t->whereBetween('data_transacao', [$dataInicio, $dataFim])
                        ->whereIn('status', ['confirmada', 'liquidada'])
                        ->whereNull('unidade_id');
                    })
                    ->whereDoesntHave('transacoesUnidade', function($t) use ($dataInicio, $dataFim) {
                        $t->whereBetween('data_transacao', [$dataInicio, $dataFim])
                        ->whereIn('status', ['confirmada', 'liquidada']);
                    });
                };


                // Aplicando a lógica de status (Conforme nova regra)
                if ($status == 'Não Iniciado') {
                    // Tem transação bruta E não tem fatura
                    $q->where($hasTransacoesBrutas)->where($noFaturas);
                } 
                elseif ($status == 'Pendente') { 
                    // Tem fatura E tem pendente
                    $q->where($hasFaturas)->where($hasPendentes);
                } 
                elseif ($status == 'Aguardando Pagamento') { 
                    // Faturou tudo (não tem pendente) E tem faturas não pagas
                    $q->where($noPendentes)->where($someFaturasNaoPagas);
                }
                elseif ($status == 'Pago') { 
                    // Faturou tudo (não tem pendente) E pagou tudo
                    $q->where($noPendentes)->where($allFaturasPagas);
                }
                elseif ($status == 'Nada a Faturar') { 
                    // Não tem transação bruta E não tem fatura
                     $q->where($noFaturas)->where($noTransacoesBrutas);
                }
            });


            // --- CÁLCULOS (SUM e COUNT) ---

            // --- INÍCIO DA CORREÇÃO: Substituir withSum por addSelect ---
            
            // Adiciona o select base
            $query->select('empresa.*');

            // 1. VALOR BRUTO TOTAL (Matriz)
            $query->addSelect([
                'valor_bruto_matriz' => TransacaoFaturamento::selectRaw('SUM(valor_total)')
                    ->whereColumn('cliente_id', 'empresa.id')
                    ->whereNull('unidade_id')
                    ->whereBetween('data_transacao', [$dataInicio, $dataFim])
                    ->whereIn('status', ['confirmada', 'liquidada'])
            ]);
            
            // 2. VALOR BRUTO TOTAL (Unidade)
            $query->addSelect([
                'valor_bruto_unidade' => TransacaoFaturamento::selectRaw('SUM(valor_total)')
                    ->whereColumn('unidade_id', 'empresa.id')
                    ->whereBetween('data_transacao', [$dataInicio, $dataFim])
                    ->whereIn('status', ['confirmada', 'liquidada'])
            ]);

            // 3. VALOR PENDENTE (Matriz)
            $query->addSelect([
                'valor_pendente_matriz' => TransacaoFaturamento::selectRaw('SUM(valor_total)')
                    ->whereColumn('cliente_id', 'empresa.id')
                    ->whereNull('unidade_id')
                    ->whereNull('fatura_id') // Apenas pendentes
                    ->whereBetween('data_transacao', [$dataInicio, $dataFim])
                    ->whereIn('status', ['confirmada', 'liquidada'])
            ]);
            
            // 4. VALOR PENDENTE (Unidade)
            $query->addSelect([
                'valor_pendente_unidade' => TransacaoFaturamento::selectRaw('SUM(valor_total)')
                    ->whereColumn('unidade_id', 'empresa.id')
                    ->whereNull('fatura_id') // Apenas pendentes
                    ->whereBetween('data_transacao', [$dataInicio, $dataFim])
                    ->whereIn('status', ['confirmada', 'liquidada'])
            ]);
            // --- FIM DA CORREÇÃO ---


            // --- Contagem de faturas (paga e total) ---
            // (withCount é seguro e pode ser mantido)
            $query->withCount(['faturas as faturas_count' => fn($q) => 
                $q->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo)
            ]);
            $query->withCount(['faturas as faturas_recebidas_count' => fn($q) => 
                $q->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo)
                  ->where('status', 'recebida')
            ]);


            $mesFormatado = Carbon::createFromFormat('Y-m', $periodo)->locale('pt_BR')->translatedFormat('F');
            $anoFormatado = Carbon::createFromFormat('Y-m', $periodo)->format('Y');

            return DataTables::of($query)
                ->addColumn('mes', $mesFormatado)
                ->addColumn('ano', $anoFormatado)
                // --- COLUNA DE VALOR (Valor Bruto Total do Período) ---
                ->addColumn('valor_bruto_total', function ($row) {
                    $valor = ($row->empresa_tipo_id == 1) ? $row->valor_bruto_matriz : $row->valor_bruto_unidade;
                    return 'R$ ' . number_format($valor ?? 0, 2, ',', '.');
                })
                // --- Lógica de Status (baseada na sua definição) ---
                ->addColumn('status', function ($row) {
                    $valorPendente = ($row->empresa_tipo_id == 1) ? $row->valor_pendente_matriz : $row->valor_pendente_unidade;
                    $valorPendente = $valorPendente ?? 0;
                    
                    $totalFaturas = $row->faturas_count;
                    $totalRecebidas = $row->faturas_recebidas_count;
                    
                    $valorBruto = ($row->empresa_tipo_id == 1) ? $row->valor_bruto_matriz : $row->valor_bruto_unidade;
                    $temValorBruto = ($valorBruto ?? 0) > 0.01;
                    
                    $toleranciaPendente = 0.01;

                    // Lógica 1: Se não tem faturas
                    if ($totalFaturas == 0) {
                        if ($temValorBruto) {
                            return '<span class="badge badge-secondary">Não Iniciado</span>'; // (Nenhuma fatura gerada)
                        } else {
                            return '<span class="badge badge-light">Nada a Faturar</span>';
                        }
                    }
                    
                    // Lógica 2: Se tem faturas
                    if ($valorPendente > $toleranciaPendente) {
                        return '<span class="badge badge-info">Pendente</span>'; // (Tem fatura, mas não é o valor total)
                    }
                    
                    // Lógica 3: Tem faturas e NÃO tem valor pendente (faturou tudo)
                    if ($totalRecebidas < $totalFaturas) {
                        return '<span class="badge badge-warning">Aguardando Pagamento</span>'; // (Todas geradas, aguardando pgto)
                    } else {
                        return '<span class="badge badge-success">Pago</span>'; // (Todas foram pagas)
                    }
                })
                ->addColumn('action', function ($row) use ($periodo) {
                    $url = route('faturamento.show', [
                        'cliente_id' => $row->id, 
                        'periodo' => $periodo
                    ]);
                    return '<a href="' . $url . '" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> Visualizar</a>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        // --- 2. SE FOR UMA REQUISIÇÃO GET (CARREGAMENTO DA PÁGINA) ---
        $baseQuery = Empresa::whereIn('empresa_tipo_id', [1, 2]); 

        $statusOptions = ['Não Iniciado', 'Pendente', 'Aguardando Pagamento', 'Pago', 'Nada a Faturar'];

        return view('admin.faturamento.index', [
            'cnpjs' => (clone $baseQuery)->whereNotNull('cnpj')->distinct()->orderBy('cnpj')->pluck('cnpj'),
            'razoesSociais' => (clone $baseQuery)->whereNotNull('razao_social')->distinct()->orderBy('razao_social')->pluck('razao_social'),
            'municipios' => Municipio::orderBy('nome')->get(),
            'estados' => Estado::orderBy('sigla')->pluck('sigla'),
            'organizacoes' => Organizacao::orderBy('nome')->get(), 
            'statusOptions' => $statusOptions,
        ]);
    }
    // ===================================================================
    // ETAPA 2: PAINEL DE FATURAMENTO (ABAS)
    // ===================================================================
    
    /**
     * Helper para buscar os parâmetros corretos (Lógica de Parâmetros)
     */
  private function getParametrosAtivos($billable_empresa_id)
    {
        $empresa = Empresa::with('organizacao')->find($billable_empresa_id);
        $paramGlobal = ParametroGlobal::first();
        if (!$empresa) {
            // Fallback
            return [
                'fonte' => 'Global (Fallback)',
                'dias_vencimento' => 30,
                'isento_ir' => false,
                'descontar_ir_fatura' => $paramGlobal->descontar_ir_fatura,
            ];
        }
        
        // --- INÍCIO DA CORREÇÃO: Lógica de Vencimento Público/Privado ---
        
        // --- CORREÇÃO: IDs baseados na sua tabela: 1,2,3,5 = Público; 4 = Privado ---
        $publico_ids = [1, 2, 3, 5]; // 1=Federal, 2=Estadual, 3=Municipal, 5=Economia Mista
        
        // Usa o ID da organização da empresa faturável (seja ela matriz ou unidade)
        $is_publico = in_array($empresa->organizacao_id, $publico_ids);
        // --- FIM DA CORREÇÃO ---

        // Se for unidade (tipo 2), busca o parâmetro da Matriz (tipo 1)
        $parametro_owner_id = ($empresa->empresa_tipo_id == 2) ? $empresa->empresa_matriz_id : $empresa->id;
        $paramCliente = ParametroCliente::where('empresa_id', $parametro_owner_id)->first();
        
        // REGRA: Se existe paramCliente E 'ativar_globais' é FALSE, usa paramCliente
        if ($paramCliente && !$paramCliente->ativar_parametros_globais) {
            
            // --- INÍCIO DA CORREÇÃO: Usa $is_publico ---
            $vencimentoPersonalizado = $paramCliente->vencimento_fatura_personalizado ?? false;

            $dias_vencimento = $vencimentoPersonalizado
                                ? $paramCliente->dias_vencimento 
                                : ($is_publico ? $paramGlobal->dias_vencimento_publico : $paramGlobal->dias_vencimento_privado);
            // --- FIM DA CORREÇÃO ---
            
            return [
                'fonte' => 'Cliente',
                'dias_vencimento' => $dias_vencimento,
                'isento_ir' => $paramCliente->isento_ir,
                'descontar_ir_fatura' => $paramCliente->descontar_ir_fatura,
            ];
        } 
        
        // ELSE: Usa Global
        return [
            'fonte' => 'Global',
            // --- INÍCIO DA CORREÇÃO: Usa $is_publico ---
            'dias_vencimento' => $is_publico ? $paramGlobal->dias_vencimento_publico : $paramGlobal->dias_vencimento_privado,
            // --- FIM DA CORREÇÃO ---
            'isento_ir' => false, // Global não tem isenção
            'descontar_ir_fatura' => $paramGlobal->descontar_ir_fatura,
        ];
    }

    public function visualizar(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
        ]);

        $billable_empresa_id = $request->input('cliente_id');
        $periodo = $request->input('periodo');
        
        $dataInicio = Carbon::createFromFormat('Y-m', $periodo)->startOfMonth();
        $dataFim = $dataInicio->copy()->endOfMonth();

        $cliente = Empresa::with(['unidades', 'organizacao', 'municipio.estado', 'matriz.organizacao'])
                            ->findOrFail($billable_empresa_id);

        $faturamentoPeriodo = FaturamentoPeriodo::firstOrCreate(
            ['cliente_id' => $billable_empresa_id, 'periodo' => $periodo]
        );

        $parametrosAtivos = $this->getParametrosAtivos($billable_empresa_id);
        
        // Query Base (Todas as transações do período)
        $queryBase = TransacaoFaturamento::whereBetween('data_transacao', [$dataInicio, $dataFim]) 
            ->whereIn('status', ['confirmada', 'liquidada']);

        if ($cliente->empresa_tipo_id == 1) {
            $queryBase->where('cliente_id', $cliente->id)->whereNull('unidade_id');
        } else {
            $queryBase->where('unidade_id', $cliente->id);
        }

        $queryBasePendentes = $queryBase->clone()->whereNull('fatura_id');
        $queryBaseFaturadas = $queryBase->clone()->whereNotNull('fatura_id');

        $totalBrutoPendente = $queryBasePendentes->sum('valor_total');
        $totalValorFaturado = $queryBaseFaturadas->sum('valor_total');
        
        $matriz = ($cliente->empresa_tipo_id == 2) ? $cliente->matriz : $cliente;
        $organizacao_id_para_taxa = $matriz ? $matriz->organizacao_id : null;

        $totalIRPendente = 0;
        if (!$parametrosAtivos['isento_ir'] && $organizacao_id_para_taxa) { 
            $subQuery = $queryBasePendentes->clone()
                ->join('public.produto as p', 'transacao_faturamento.produto_id', '=', 'p.id')
                ->leftJoin('contas_receber.parametro_taxa_aliquota as pta', function ($join) use ($organizacao_id_para_taxa) {
                    $join->on('p.produto_categoria_id', '=', 'pta.produto_categoria_id')
                         ->where('pta.organizacao_id', '=', $organizacao_id_para_taxa);
                })
                ->select(DB::raw('SUM(transacao_faturamento.valor_total * COALESCE(pta.taxa_aliquota, 0)) as total_ir_calculado'));
            
            $totalIRPendente = $subQuery->first()->total_ir_calculado ?? 0;
        }
            
        $totalLiquidoPendente = $totalBrutoPendente;
        if ($parametrosAtivos['descontar_ir_fatura']) {
            $totalLiquidoPendente = $totalBrutoPendente - $totalIRPendente;
        }

        $totaisPendentes = [
            'bruto' => $totalBrutoPendente,
            'ir' => $totalIRPendente,
            'liquido' => $totalLiquidoPendente,
            'faturado' => $totalValorFaturado, // Valor já faturado
        ];
        
        // --- INÍCIO DA CORREÇÃO: Mudar JOIN para LEFT JOIN ---
        // Isso garante que transações sem grupo (comuns em empresas privadas)
        // ainda sejam contabilizadas.

        $totaisPorUnidade = $this->getTotaisAgrupados($queryBase->clone(), 'unidade_id', 'public.empresa', 'razao_social');
        $totaisPorEmpenho = $this->getTotaisAgrupados($queryBase->clone(), 'empenho_id', 'public.empenho', 'numero_empenho');
        
        $totaisPorGrupo = $queryBase->clone() 
            ->leftJoin('public.veiculo as v', 'transacao_faturamento.veiculo_id', '=', 'v.id')
            ->leftJoin('public.grupo as subgrupo', 'v.grupo_id', '=', 'subgrupo.id')
            ->leftJoin('public.grupo as grupo_pai', 'subgrupo.grupo_id', '=', 'grupo_pai.id')
            ->select(
                'grupo_pai.id as grupo_pai_id',
                DB::raw("COALESCE(grupo_pai.nome, 'Sem Grupo Pai') as nome"),
                DB::raw('SUM(transacao_faturamento.valor_total) as valor_bruto'),
                DB::raw('COUNT(DISTINCT subgrupo.id) as subgrupos_count')
            )
            // ->whereNotNull('v.grupo_id') // <-- REMOVIDO: Isso quebrava a query para privados
            ->groupBy('grupo_pai.id', 'grupo_pai.nome')
            ->orderBy('valor_bruto', 'desc')
            ->get();
        
        // --- FIM DA CORREÇÃO ---
        
        // --- ADIÇÃO DA FLAG 'is_publico' ---
        $publico_ids = [1, 2, 3, 5]; // 1=Federal, 2=Estadual, 3=Municipal, 5=Economia Mista
        $is_publico = in_array($cliente->organizacao_id, $publico_ids);
        
        return view('admin.faturamento.show', compact(
            'cliente', 
            'periodo',
            'faturamentoPeriodo',
            'parametrosAtivos',
            'totaisPendentes',
            'totaisPorUnidade',
            'totaisPorEmpenho',
            'totaisPorGrupo',
            'is_publico' // <-- NOVA FLAG PARA A VIEW
        ));
    }

    // ===================================================================
    // MÉTODOS AJAX (Abas e Modal)
    // ===================================================================

    public function getSubgrupos(Request $request)
    {
        // --- INÍCIO DA CORREÇÃO: Mudar JOIN para LEFT JOIN ---
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
            'grupo_pai_id' => 'nullable',
        ]);

        $billable_empresa_id = $request->input('cliente_id');
        $empresa = Empresa::findOrFail($billable_empresa_id);

        $dataInicio = Carbon::createFromFormat('Y-m', $request->periodo)->startOfMonth();
        $dataFim = $dataInicio->copy()->endOfMonth();

        // Query Base (Total, não apenas pendente)
        $queryBase = TransacaoFaturamento::whereBetween('data_transacao', [$dataInicio, $dataFim]) 
            ->whereIn('status', ['confirmada', 'liquidada']);
        
        if ($empresa->empresa_tipo_id == 1) {
            $queryBase->where('cliente_id', $empresa->id)->whereNull('unidade_id');
        } else {
            $queryBase->where('unidade_id', $empresa->id);
        }
        // --- FIM DA CORREÇÃO ---

        $query = $queryBase // Usa a $queryBase (total do período)
            ->leftJoin('public.veiculo as v', 'transacao_faturamento.veiculo_id', '=', 'v.id')
            ->leftJoin('public.grupo as subgrupo', 'v.grupo_id', '=', 'subgrupo.id')
            ->select(
                DB::raw("COALESCE(subgrupo.nome, 'Sem Subgrupo') as nome"),
                DB::raw('SUM(transacao_faturamento.valor_total) as valor_bruto')
            );
            // ->whereNotNull('v.grupo_id'); // <-- REMOVIDO: Quebrava para privados

        if ($request->filled('grupo_pai_id')) {
            $query->where('subgrupo.grupo_id', $request->grupo_pai_id);
        } else {
            // Se o grupo_pai_id for nulo (ou seja, "Sem Grupo Pai"),
            // queremos transações onde (subgrupo.grupo_id é nulo)
            // OU onde o subgrupo em si é nulo (v.grupo_id é nulo)
            $query->where(function($q) {
                $q->whereNull('subgrupo.grupo_id')
                  ->orWhereNull('v.grupo_id');
            });
        }

        $subgrupos = $query
             // --- CORREÇÃO: Agrupa pela expressão COALESCE para evitar erro SQL ---
            ->groupBy(DB::raw("COALESCE(subgrupo.nome, 'Sem Subgrupo')"))
            ->orderBy('valor_bruto', 'desc')
            ->get();
            
        return view('admin.faturamento._subgrupos_table', compact('subgrupos'));
    }
    
    private function getTotaisAgrupados($query, $foreignKey, $joinTable, $nomeColuna)
    {
        // $query->whereNotNull($foreignKey); // <-- REMOVIDO: Isso quebrava para empenho em privados
        
        return $query
            ->leftJoin($joinTable, "transacao_faturamento.{$foreignKey}", '=', "{$joinTable}.id")
            ->select(
                // Adiciona COALESCE para agrupar nulos (ex: 'Sem Empenho' ou 'Sem Dados')
                DB::raw("COALESCE({$joinTable}.{$nomeColuna}, 'Sem Dados') as nome"),
                DB::raw("SUM(transacao_faturamento.valor_total) as valor_bruto")
            )
            // --- CORREÇÃO: Agrupa pela coluna original para evitar erro no PostgreSQL ---
            ->groupBy("{$joinTable}.{$nomeColuna}") 
            ->orderBy('valor_bruto', 'desc')
            ->get();
    }

    public function updateObservacoes(Request $request)
    {
        $request->validate([
            'faturamento_periodo_id' => 'required|integer|exists:faturamento_periodos,id',
            'observacoes' => 'nullable|string',
        ]);
        $periodo = FaturamentoPeriodo::find($request->faturamento_periodo_id);
        $periodo->observacoes = $request->observacoes;
        $periodo->save();
        return response()->json(['success' => true, 'message' => 'Observações salvas.']);
    }

    /**
     * CORREÇÃO 1: Botões trocados de <a> para <button>
     */
    public function getFaturas(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
        ]);

        // ================================
        // Query base
        // ================================
        $query = Fatura::where('cliente_id', $request->cliente_id)
            ->whereRaw("TO_CHAR(periodo_fatura, 'YYYY-MM') = ?", [$request->periodo]);

        // ================================
        // Retorno via DataTables
        // ================================
        return DataTables::of($query)
            ->addColumn('action', function($row){
                $btn = '<button type="button" class="btn btn-xs btn-primary btn-editar" data-id="'.$row->id.'"><i class="fa fa-edit"></i> Editar</button> ';
                $btn .= '<button type="button" class="btn btn-xs btn-success btn-receber" data-id="'.$row->id.'"><i class="fa fa-check"></i> Recebido</button> ';
                $btn .= '<button type="button" class="btn btn-xs btn-danger btn-excluir" data-id="'.$row->id.'"><i class="fa fa-trash"></i> Excluir</button>';
                return $btn;
            })
            ->editColumn('data_emissao', fn($row) => $row->data_emissao ? Carbon::parse($row->data_emissao)->format('d-m-Y') : '-')
            ->editColumn('data_vencimento', fn($row) => $row->data_vencimento ? Carbon::parse($row->data_vencimento)->format('d-m-Y') : '-')
            ->editColumn('valor_total', fn($row) => 'R$ ' . number_format($row->valor_total, 2, ',', '.'))
            ->editColumn('valor_impostos', fn($row) => 'R$ ' . number_format($row->valor_impostos, 2, ',', '.'))
            ->editColumn('valor_descontos', fn($row) => 'R$ ' . number_format($row->valor_descontos, 2, ',', '.'))
            ->editColumn('valor_liquido', fn($row) => 'R$ ' . number_format($row->valor_liquido, 2, ',', '.'))


            ->editColumn('status', function ($row) {
                return match($row->status) {
                    'recebida' => '<span class="badge badge-success">Recebida</span>',
                    'pendente' => '<span class="badge badge-warning">Pendente</span>',
                    'recebida_parcial' => '<span class="badge badge-info">Parcial</span>',
                    'cancelada' => '<span class="badge badge-danger">Cancelada</span>',
                    default => ucfirst($row->status),
                };
            })
            ->rawColumns(['action','status'])
            ->make(true);
    }

    public function getTransacoes(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
        ]);

        $billable_empresa_id = $request->input('cliente_id');
        $empresa = Empresa::with('matriz.organizacao')->findOrFail($billable_empresa_id);
        $parametrosAtivos = $this->getParametrosAtivos($billable_empresa_id);
        
        $dataInicio = Carbon::createFromFormat('Y-m', $request->periodo)->startOfMonth();
        $dataFim = $dataInicio->copy()->endOfMonth();

        $query = TransacaoFaturamento::with(['credenciado', 'produto', 'empenho', 'veiculo.grupo.grupoPai'])
            ->whereBetween('data_transacao', [$dataInicio, $dataFim])
            ->whereIn('status', ['confirmada', 'liquidada']);
        
        if ($empresa->empresa_tipo_id == 1) {
            $query->where('cliente_id', $empresa->id)->whereNull('unidade_id');
        } else {
            $query->where('unidade_id', $empresa->id);
        }

        $matriz = ($empresa->empresa_tipo_id == 2) ? $empresa->matriz : $empresa;
        $organizacao_id_para_taxa = $matriz ? $matriz->organizacao_id : null;

        $taxas = collect();
        if ($organizacao_id_para_taxa) {
             $taxas = ParametroTaxaAliquota::where('organizacao_id', $organizacao_id_para_taxa)
                         ->get()
                         ->keyBy('produto_categoria_id');
        }

        return DataTables::of($query)
            ->addColumn('faturada', fn($row) => $row->fatura_id ? '<span class="badge badge-success">Sim</span>' : '<span class="badge badge-warning">Não</span>')
            ->addColumn('credenciado_nome', fn($row) => optional($row->credenciado)->razao_social ?? 'N/A')
            ->addColumn('produto_nome', fn($row) => optional($row->produto)->nome ?? 'N/A')
            
            // --- INÍCIO DA CORREÇÃO: Acesso seguro (optional) ---
            ->addColumn('grupo_nome', fn($row) => optional(optional(optional($row->veiculo)->grupo)->grupoPai)->nome ?? 'N/A') 
            ->addColumn('subgrupo_nome', fn($row) => optional(optional($row->veiculo)->grupo)->nome ?? 'N/A')
            ->addColumn('placa', fn($row) => optional($row->veiculo)->placa ?? 'N/A')
            // --- FIM DA CORREÇÃO ---
            
            ->addColumn('aliquota_ir', function($row) use ($parametrosAtivos, $taxas) {
                if ($parametrosAtivos['isento_ir']) {
                    return 0;
                }
                // --- CORREÇÃO: Acesso seguro (optional) ---
                $categoriaId = optional($row->produto)->produto_categoria_id;
                $taxa = $taxas->get($categoriaId);
                return $taxa ? $taxa->taxa_aliquota : 0;
            })
            ->addColumn('valor_ir', function($row) {
                $aliquota_ir = $row->aliquota_ir; 
                return $row->valor_total * $aliquota_ir;
            })

            ->editColumn('valor_unitario', fn($row) => 'R$ ' . number_format($row->valor_unitario, 2, ',', '.'))
            ->editColumn('valor_total', fn($row) => 'R$ ' . number_format($row->valor_total, 2, ',', '.'))
            
            ->editColumn('aliquota_ir', fn($row) => number_format($row->aliquota_ir * 100, 2, ',', '.') . '%')
            ->editColumn('valor_ir', fn($row) => 'R$ ' . number_format($row->valor_ir, 2, ',', '.'))
            
            ->rawColumns(['faturada'])
            ->make(true);
    }

   public function gerarFatura(Request $request)
    {
        // (Sem alteração - Seu código já está correto para a nova regra)
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
            'data_vencimento' => 'required|date',
            'tipo_geracao' => 'required|string|in:Total,Fracionada',
            'nota_fiscal' => 'nullable|string|max:100',
            // Validações de filtros
            'contrato_id' => 'nullable|integer|exists:contrato,id',
            'empenho_id' => 'nullable|integer|exists:empenho,id',
            'grupo_id' => 'nullable|integer|exists:grupo,id',
            'subgrupo_id' => 'nullable|integer|exists:grupo,id',
            'valor_fatura_calculado' => 'required|numeric|min:0.01', 
        ]);

        if ($request->tipo_geracao == 'Fracionada' && 
            !$request->filled('grupo_id') && 
            !$request->filled('subgrupo_id') && 
            !$request->filled('empenho_id')
        ) {
            return response()->json(['success' => false, 'message' => 'Selecione ao menos um filtro (Grupo, Subgrupo ou Empenho) para faturamento fracionado.'], 422);
        }

        DB::beginTransaction();
        try {
            $billable_empresa_id = $request->cliente_id;
            $periodo = $request->periodo;
            
            $parametrosAtivos = $this->getParametrosAtivos($billable_empresa_id);
            $empresa = Empresa::with('organizacao', 'matriz.organizacao')->find($billable_empresa_id);
            
            // ... (Lógica de $taxas - Sem mudança)
            $matriz = ($empresa->empresa_tipo_id == 2) ? $empresa->matriz : $empresa;
            $organizacao_id_para_taxa = $matriz ? $matriz->organizacao_id : null;
            $taxas = collect();
            if ($organizacao_id_para_taxa) {
                $taxas = ParametroTaxaAliquota::where('organizacao_id', $organizacao_id_para_taxa)
                                ->get()
                                ->keyBy('produto_categoria_id');
            }
            
            // 1. CONSTRUIR A QUERY DE TRANSAÇÕES A FATURAR
            $queryTransacoes = $this->buildPendentesQuery($request)
                                  ->with('produto', 'veiculo.grupo') 
                                  ->orderBy('data_transacao', 'asc');
            
            if ($request->tipo_geracao == 'Fracionada') {
                $queryTransacoes->when($request->filled('contrato_id'), fn($q) => $q->where('contrato_id', $request->contrato_id));
                $queryTransacoes->when($request->filled('empenho_id'), fn($q) => $q->where('empenho_id', $request->empenho_id));
                $queryTransacoes->when($request->filled('grupo_id'), fn($q) => $q->whereHas('veiculo.grupo', fn($q2) => $q2->where('grupo_id', $request->grupo_id)));
                $queryTransacoes->when($request->filled('subgrupo_id'), fn($q) => $q->whereHas('veiculo', fn($q2) => $q2->where('grupo_id', $request->subgrupo_id)));
            }

            $transacoesParaFaturar = $queryTransacoes->get();
            $totalBrutoFiltrado = $transacoesParaFaturar->sum('valor_total');
            
            if ($transacoesParaFaturar->isEmpty() || $totalBrutoFiltrado <= 0) {
                return response()->json(['success' => false, 'message' => 'Nenhuma transação pendente encontrada para esses filtros.'], 400);
            }

            // 2. VALIDAÇÃO DE LIMITE (REGRA FUNCIONAL)
            $valorFaturaDigitado = (float) $request->valor_fatura_calculado;
            
            if ($request->tipo_geracao == 'Fracionada') {
                
                // 2a. O valor digitado não pode ser maior que o total disponível NESSES FILTROS
                if ($valorFaturaDigitado > ($totalBrutoFiltrado + 0.001)) { 
                    return response()->json([
                        'success' => false, 
                        'message' => sprintf(
                            'Valor a faturar (R$ %s) não pode ser maior que o total pendente para estes filtros (R$ %s).',
                            number_format($valorFaturaDigitado, 2, ',', '.'),
                            number_format($totalBrutoFiltrado, 2, ',', '.')
                        )
                    ], 400);
                }

                // 2b. O valor digitado não pode ser maior que o limite do ESCOPO HIERÁRQUICO
                $limiteAplicavel = INF;
                
                if ($request->filled('empenho_id')) {
                    $limiteAplicavel = $this->getLimiteScope('empenho', $request->empenho_id, $empresa, $periodo);
                } elseif ($request->filled('subgrupo_id')) {
                    $limiteAplicavel = $this->getLimiteScope('subgrupo', $request->subgrupo_id, $empresa, $periodo);
                } elseif ($request->filled('grupo_id')) {
                    $limiteAplicavel = $this->getLimiteScope('grupo', $request->grupo_id, $empresa, $periodo);
                }
                
                if ($valorFaturaDigitado > ($limiteAplicavel + 0.001)) { 
                     return response()->json([
                        'success' => false, 
                        'message' => sprintf(
                            'Valor a faturar (R$ %s) excede o limite de saldo pendente para o escopo selecionado (R$ %s).',
                            number_format($valorFaturaDigitado, 2, ',', '.'),
                            number_format($limiteAplicavel, 2, ',', '.')
                        )
                    ], 400);
                }
                
                $valorFinalDaFatura = $valorFaturaDigitado;
                
            } else {
                 if (abs($totalBrutoFiltrado - $valorFaturaDigitado) > 0.01) {
                     return response()->json([
                        'success' => false, 
                        'message' => 'Inconsistência de valores no modo Total. Recarregue e tente novamente.'
                    ], 400);
                 }
                 $valorFinalDaFatura = $totalBrutoFiltrado;
            }

            $horaMinuto = Carbon::now()->format('Hi'); 
            $numeroFatura = 'FAT-' . $billable_empresa_id . '' . $horaMinuto . '' . rand(0, 1000);

            // 3. SE PASSOU NA VALIDAÇÃO, GERAR A FATURA
            
            $fatura = Fatura::create([
                'cliente_id' => $billable_empresa_id,
                'data_emissao' => Carbon::now(),
                'data_vencimento' => $request->data_vencimento,
                'nota_fiscal' => $request->nota_fiscal,
                'status' => 'pendente',
                'numero_fatura' => $numeroFatura,
                'periodo_fatura' => Carbon::createFromFormat('Y-m', $periodo)->startOfMonth(),
                'valor_total' => 0, 'valor_impostos' => 0, 'valor_descontos' => 0, 'valor_liquido' => 0, 
            ]);

            // Esta lógica de loop é o que permite faturar R$ 10 de um pool de R$ 100
            $totalBrutoReal = 0;
            $totalDescontoIRReal = 0;
            $totalImpostosItens = 0; 

            foreach ($transacoesParaFaturar as $transacao) {
                $subtotal = $transacao->valor_total;
                $categoriaId = $transacao->produto->produto_categoria_id ?? 0;
                
                // Regra: Se o total bruto + subtotal ultrapassar o limite da fatura (valor digitado), pula.
                if ( ($totalBrutoReal + $subtotal) > ($valorFinalDaFatura + 0.001) ) {
                    continue; 
                }
                
                $totalBrutoReal += $subtotal;
                
                $valorImpostoTransacao = 0;
                if (!$parametrosAtivos['isento_ir']) {
                    $taxa = $taxas->get($categoriaId);
                    $aliquota = $taxa ? $taxa->taxa_aliquota : 0;
                    $valorImpostoTransacao = $subtotal * $aliquota;
                }
                $totalDescontoIRReal += $valorImpostoTransacao;
                
                FaturaItem::create([
                    'fatura_id' => $fatura->id,
                    'transacao_faturamento_id' => $transacao->id,
                    'descricao_produto' => $transacao->produto->nome ?? 'N/A',
                    'produto_id' => $transacao->produto_id,
                    'produto_categoria_id' => $categoriaId,
                    'quantidade' => $transacao->quantidade ?? 1,
                    'valor_unitario' => $transacao->valor_unitario ?? $subtotal,
                    'valor_subtotal' => $subtotal,
                    'aliquota_aplicada' => 0, 
                    'valor_imposto' => 0,
                    'valor_total_item' => $subtotal,
                ]);
                
                $transacao->fatura_id = $fatura->id;
                $transacao->save();
            }

            // 4. ATUALIZAR TOTAIS DA FATURA
            $fatura->valor_total = $totalBrutoReal;
            $fatura->valor_impostos = $totalImpostosItens; // 0
            $fatura->valor_descontos = $totalDescontoIRReal; 
            
            $fatura->valor_liquido = $totalBrutoReal;
            if ($parametrosAtivos['descontar_ir_fatura']) {
                $fatura->valor_liquido = $totalBrutoReal - $totalDescontoIRReal;
            }
            
            $fatura->save();
            
            DB::commit();
            return response()->json(['success' => true, 'message' => "Fatura #{$fatura->id} (Valor: R$ ".number_format($fatura->valor_liquido, 2, ',', '.').") gerada com sucesso!"]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro ao gerar fatura: ' . $e->getMessage(), 'trace' => $e->getTrace()], 500);
        }
    }

    public function destroyFatura(Fatura $fatura)
    {
        // dd($fatura); // <-- REMOVIDO
        
        if ($fatura->status != 'pendente') {
            return response()->json(['success' => false, 'message' => 'Apenas faturas pendentes podem ser excluídas.'], 400);
        }
        DB::beginTransaction();
        try {
            TransacaoFaturamento::where('fatura_id', $fatura->id)->update(['fatura_id' => null]);
            $fatura->itens()->delete();
            $fatura->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Fatura excluída e transações reabertas.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro ao excluir fatura: ' . $e->getMessage()], 500);
        }
    }
    
    public function marcarRecebida(Request $request, Fatura $fatura)
    {
        $fatura->status = 'recebida';
        $fatura->save();
        return response()->json(['success' => true, 'message' => 'Fatura marcada como recebida.']);
    }

    public function getContratosCliente(Request $request)
    {
        $request->validate(['cliente_id' => 'required|integer|exists:empresa,id']);
        $empresa = Empresa::find($request->query('cliente_id'));
        $matriz_id = ($empresa->empresa_tipo_id == 2) ? $empresa->empresa_matriz_id : $empresa->id;
        
        $contratos = Contrato::where('empresa_id', $matriz_id)
                            ->where('contrato_situacao_id', 1) // Apenas ativos
                            ->get(['id', 'numero']); 
        
        return response()->json($contratos);
    }

    public function getEmpenhosPendentes(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
            'contrato_id' => 'nullable|integer',
            'grupo_id' => 'nullable|integer',
            'subgrupo_id' => 'nullable|integer',
        ]);
        
        $queryBasePendentes = $this->buildPendentesQuery($request);
        
        // --- INÍCIO DA MUDANÇA (LÓGICA DE CASCATA) ---
        $queryBasePendentes->when($request->filled('contrato_id'), fn($q) => $q->where('contrato_id', $request->contrato_id));
        $queryBasePendentes->when($request->filled('grupo_id'), fn($q) => $q->whereHas('veiculo.grupo', fn($q2) => $q2->where('grupo_id', $request->grupo_id)));
        $queryBasePendentes->when($request->filled('subgrupo_id'), fn($q) => $q->whereHas('veiculo', fn($q2) => $q2->where('grupo_id', $request->subgrupo_id)));
        // --- FIM DA MUDANÇA ---

        $empenhos = $queryBasePendentes
            ->join('public.empenho', 'transacao_faturamento.empenho_id', '=', 'empenho.id')
            ->select(
                'empenho.id',
                'empenho.numero_empenho',
                'empenho.valor', 
                DB::raw('SUM(transacao_faturamento.valor_total) as valor_pendente')
            )
            ->whereNotNull('transacao_faturamento.empenho_id')
            ->groupBy('empenho.id', 'empenho.numero_empenho', 'empenho.valor')
            ->get();
            
        return response()->json($empenhos);
    }

   public function getGruposPendentes(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
            'contrato_id' => 'nullable|integer',
            'empenho_id' => 'nullable|integer',
        ]);

        $queryBasePendentes = $this->buildPendentesQuery($request);

        // --- INÍCIO DA MUDANÇA (LÓGICA DE CASCATA) ---
        $queryBasePendentes->when($request->filled('contrato_id'), fn($q) => $q->where('contrato_id', $request->contrato_id));
        $queryBasePendentes->when($request->filled('empenho_id'), fn($q) => $q->where('empenho_id', $request->empenho_id));
        // --- FIM DA MUDANÇA ---

        // --- CORREÇÃO: Mudar JOIN para LEFT JOIN ---
        $grupos = $queryBasePendentes
            ->leftJoin('public.veiculo as v', 'transacao_faturamento.veiculo_id', '=', 'v.id')
            ->leftJoin('public.grupo as subgrupo', 'v.grupo_id', '=', 'subgrupo.id')
            ->leftJoin('public.grupo as grupo_pai', 'subgrupo.grupo_id', '=', 'grupo_pai.id')
            ->select(
                'grupo_pai.id as grupo_pai_id',
                DB::raw("COALESCE(grupo_pai.nome, 'Sem Grupo Pai') as grupo_pai_nome"),
                'subgrupo.id as subgrupo_id',
                DB::raw("COALESCE(subgrupo.nome, 'Sem Subgrupo') as subgrupo_nome"),
                DB::raw('SUM(transacao_faturamento.valor_total) as valor_pendente')
            )
            // ->whereNotNull('v.grupo_id') // <-- REMOVIDO: Quebrava para privados
            ->groupBy('grupo_pai.id', 'grupo_pai.nome', 'subgrupo.id', 'subgrupo.nome')
            ->get();
            
        // Mapeamento (sem alteração)
        $grupos_pais = $grupos->groupBy('grupo_pai_nome')->map(function($g) {
            return [
                'id' => $g->first()->grupo_pai_id,
                'text' => $g->first()->grupo_pai_nome,
                'valor_pendente' => $g->sum('valor_pendente'),
            ];
        });
        $subgrupos = $grupos->map(function($g) {
            return [
                'id' => $g->subgrupo_id,
                'text' => $g->subgrupo_nome,
                'grupo_pai_id' => $g->grupo_pai_id,
                'valor_pendente' => $g->valor_pendente,
            ];
        });
            
        return response()->json([
            'grupos_pais' => $grupos_pais->values(),
            'subgrupos' => $subgrupos
        ]);
    }

    public function getValorFiltrado(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
            'tipo_geracao' => 'required|string',
            'contrato_id' => 'nullable|integer',
            'empenho_id' => 'nullable|integer',
            'grupo_id' => 'nullable|integer',
            'subgrupo_id' => 'nullable|integer',
        ]);

        $queryTransacoes = $this->buildPendentesQuery($request);

        // Aplica os filtros exatos (lógica E)
        if ($request->tipo_geracao == 'Fracionada') {
            
            // Regra: Modo Fracionada *EXIGE PELO MENOS UM* filtro de escopo
            if (
                !$request->filled('empenho_id') &&
                !$request->filled('grupo_id') &&
                !$request->filled('subgrupo_id')
            ) {
                 return response()->json(['valor_filtrado' => 0, 'limite_aplicavel' => 0]);
            }
            
            // Filtros opcionais (Lógica E)
            $queryTransacoes->when($request->filled('contrato_id'), fn($q) => $q->where('contrato_id', $request->contrato_id));
            $queryTransacoes->when($request->filled('empenho_id'), fn($q) => $q->where('empenho_id', $request->empenho_id));
            $queryTransacoes->when($request->filled('grupo_id'), fn($q) => $q->whereHas('veiculo.grupo', fn($q2) => $q2->where('grupo_id', $request->grupo_id)));
            $queryTransacoes->when($request->filled('subgrupo_id'), fn($q) => $q->whereHas('veiculo', fn($q2) => $q2->where('grupo_id', $request->subgrupo_id)));
            
        }
        
        $totalFiltrado = $queryTransacoes->sum('valor_total');
        
        // CALCULAR O LIMITE HIERÁRQUICO
        $limiteAplicavel = INF;
        $empresa = Empresa::find($request->cliente_id);
        $periodo = $request->periodo;
        
        if ($request->tipo_geracao == 'Fracionada') {
            if ($request->filled('empenho_id')) {
                $limiteAplicavel = $this->getLimiteScope('empenho', $request->empenho_id, $empresa, $periodo);
            } elseif ($request->filled('subgrupo_id')) {
                $limiteAplicavel = $this->getLimiteScope('subgrupo', $request->subgrupo_id, $empresa, $periodo);
            } elseif ($request->filled('grupo_id')) {
                $limiteAplicavel = $this->getLimiteScope('grupo', $request->grupo_id, $empresa, $periodo);
            }
        } else {
            $limiteAplicavel = $totalFiltrado;
        }

        if ($limiteAplicavel === INF) {
            $limiteAplicavel = $totalFiltrado;
        }
        
        return response()->json([
            'valor_filtrado' => $totalFiltrado, // Total do pool (lógica E)
            'limite_aplicavel' => $limiteAplicavel // Limite do escopo (lógica hierárquica)
        ]);
    }

    private function buildPendentesQuery(Request $request)
    {
        // ... (Seu código existente - sem mudança)
        $billable_empresa_id = $request->input('cliente_id');
        $periodo = $request->input('periodo');
        $dataInicio = Carbon::createFromFormat('Y-m', $periodo)->startOfMonth();
        $dataFim = $dataInicio->copy()->endOfMonth();
        $empresa = Empresa::find($billable_empresa_id);

        $query = TransacaoFaturamento::whereBetween('data_transacao', [$dataInicio, $dataFim])
            ->whereIn('status', ['confirmada', 'liquidada'])
            ->whereNull('fatura_id');

        if ($empresa->empresa_tipo_id == 1) {
            $query->where('cliente_id', $empresa->id)->whereNull('unidade_id');
        } else {
            $query->where('unidade_id', $empresa->id);
        }
        
        return $query;
    }

    private function getLimiteScope($tipo, $id, $cliente, $periodo)
    {
        // ... (Seu código existente - sem mudança)
        if (!$id) {
            return INF;
        }

        $dataInicio = Carbon::createFromFormat('Y-m', $periodo)->startOfMonth();
        $dataFim = $dataInicio->copy()->endOfMonth();

        $query = TransacaoFaturamento::whereBetween('data_transacao', [$dataInicio, $dataFim]) 
            ->whereIn('status', ['confirmada', 'liquidada'])
            ->whereNull('fatura_id');

        if ($cliente->empresa_tipo_id == 1) {
            $query->where('cliente_id', $cliente->id)->whereNull('unidade_id');
        } else {
            $query->where('unidade_id', $cliente->id);
        }

        switch ($tipo) {
            case 'empenho':
                $query->where('empenho_id', $id);
                break;
            case 'grupo':
                $query->whereHas('veiculo.grupo', fn($q) => $q->where('grupo_id', $id));
                break;
            case 'subgrupo':
                $query->whereHas('veiculo', fn($q) => $q->where('grupo_id', $id));
                break;
            default:
                return INF;
        }

        return $query->sum('valor_total');
    }
}