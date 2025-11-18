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
use App\Models\FaturaPagamento; // <<< ADICIONADO
// ... (outros use statements)
use App\Models\FaturaDesconto; // Necessário para a exclusão
// ...

class FaturamentoController extends Controller
{
    // ===================================================================
    // ETAPA 1: TELA INICIAL (FILTRO + TABELA RESUMO)
    // ===================================================================

  private function applyIndexFilters($query, Request $request, $periodo, $dataInicio, $dataFim)
    {
        // 1. Filtros Básicos
        $query->when($request->filled('cnpj'), fn($q) => $q->where('cnpj', $request->cnpj));
        $query->when($request->filled('razao_social'), fn($q) => $q->where('razao_social', 'ilike', "%{$request->razao_social}%"));
        $query->when($request->filled('municipio_id'), fn($q) => $q->where('municipio_id', $request->municipio_id));
        $query->when($request->filled('estado'), fn($q) => $q->whereHas('municipio.estado', fn($q2) => $q2->where('sigla', $request->estado)));
        $query->when($request->filled('organizacao'), fn ($q) => $q->where('organizacao_id', $request->organizacao));

        // 2. Filtro Tipo Organização
        $publico_ids = [1, 2, 3, 5];
        $query->when($request->filled('tipo_organizacao'), function ($q) use ($request, $publico_ids) {
            if ($request->tipo_organizacao == 'publica') {
                $q->whereIn('organizacao_id', $publico_ids);
            } elseif ($request->tipo_organizacao == 'privada') {
                $q->whereNotIn('organizacao_id', $publico_ids);
            }
        });

        // 3. Filtro de Status (Lógica Complexa)
        $query->when($request->filled('status'), function($q) use ($request, $periodo, $dataInicio, $dataFim) {
            $status = $request->status;

            $whereTransacoesPeriodo = function($t) use ($dataInicio, $dataFim) {
                $t->whereBetween('data_transacao', [$dataInicio, $dataFim])
                  ->whereIn('status', ['confirmada', 'liquidada']);
            };

            if ($status == 'Não Iniciado') {
                $q->whereDoesntHave('faturas', fn($f) => $f->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo))
                  ->where(function($sub) use ($whereTransacoesPeriodo) {
                      $sub->whereHas('transacoes', fn($t) => $whereTransacoesPeriodo($t)->whereNull('unidade_id'))
                          ->orWhereHas('transacoesUnidade', $whereTransacoesPeriodo);
                  });
            } elseif ($status == 'Pendente') { 
                $q->whereHas('faturas', fn($f) => $f->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo))
                  ->where(function($sub) use ($whereTransacoesPeriodo) {
                      $sub->whereHas('transacoes', fn($t) => $whereTransacoesPeriodo($t)->where('status_faturamento', 'pendente'))
                          ->orWhereHas('transacoesUnidade', fn($t) => $whereTransacoesPeriodo($t)->where('status_faturamento', 'pendente'));
                  });
            } elseif ($status == 'Aguardando Pagamento') { 
                $q->where(function($sub) use ($whereTransacoesPeriodo) {
                    $sub->whereDoesntHave('transacoes', fn($t) => $whereTransacoesPeriodo($t)->where('status_faturamento', 'pendente'))
                        ->whereDoesntHave('transacoesUnidade', fn($t) => $whereTransacoesPeriodo($t)->where('status_faturamento', 'pendente'));
                })->whereHas('faturas', fn($f) => 
                    $f->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo)
                      ->whereIn('status', ['pendente', 'recebida_parcial'])
                );
            } elseif ($status == 'Pago') { 
                $q->whereHas('faturas', fn($f) => 
                    $f->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo)->where('status', 'recebida')
                )->whereDoesntHave('faturas', fn($f) => 
                    $f->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo)->where('status', '!=', 'recebida')
                );
            } elseif ($status == 'Nada a Faturar') { 
                $q->whereDoesntHave('faturas', fn($f) => $f->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo))
                  ->whereDoesntHave('transacoes', $whereTransacoesPeriodo)
                  ->whereDoesntHave('transacoesUnidade', $whereTransacoesPeriodo);
            }
        });

        return $query;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Define datas
            $periodo = $request->input('periodo', Carbon::now()->subMonth()->format('Y-m'));
            try {
                $dataInicio = Carbon::createFromFormat('Y-m', $periodo)->startOfMonth();
                $dataFim = $dataInicio->copy()->endOfMonth();
            } catch (\Exception $e) {
                $periodo = Carbon::now()->subMonth()->format('Y-m');
                $dataInicio = Carbon::now()->subMonth()->startOfMonth();
                $dataFim = $dataInicio->copy()->endOfMonth();
            }

            // Inicia Query e Aplica Filtros usando o Helper
            $query = Empresa::whereIn('empresa_tipo_id', [1, 2]);
            $this->applyIndexFilters($query, $request, $periodo, $dataInicio, $dataFim);

            // --- Selects e Subqueries (Otimizados) ---
            $query->select('empresa.*');

            // Valor Bruto Matriz e Unidade (usando COALESCE para evitar nulos)
            $query->addSelect([
                'valor_bruto_matriz' => TransacaoFaturamento::selectRaw('COALESCE(SUM(valor_total), 0)')
                    ->whereColumn('cliente_id', 'empresa.id')
                    ->whereNull('unidade_id')
                    ->whereBetween('data_transacao', [$dataInicio, $dataFim])
                    ->whereIn('status', ['confirmada', 'liquidada']),
                
                'valor_bruto_unidade' => TransacaoFaturamento::selectRaw('COALESCE(SUM(valor_total), 0)')
                    ->whereColumn('unidade_id', 'empresa.id')
                    ->whereBetween('data_transacao', [$dataInicio, $dataFim])
                    ->whereIn('status', ['confirmada', 'liquidada']),

                'valor_pendente_matriz' => TransacaoFaturamento::selectRaw('COALESCE(SUM(valor_total), 0)')
                    ->whereColumn('cliente_id', 'empresa.id')
                    ->whereNull('unidade_id')
                    ->where('status_faturamento', 'pendente')
                    ->whereBetween('data_transacao', [$dataInicio, $dataFim])
                    ->whereIn('status', ['confirmada', 'liquidada']),

                'valor_pendente_unidade' => TransacaoFaturamento::selectRaw('COALESCE(SUM(valor_total), 0)')
                    ->whereColumn('unidade_id', 'empresa.id')
                    ->where('status_faturamento', 'pendente')
                    ->whereBetween('data_transacao', [$dataInicio, $dataFim])
                    ->whereIn('status', ['confirmada', 'liquidada'])
            ]);

            // Contagens pré-carregadas
            $query->withCount(['faturas as faturas_count' => fn($q) => 
                $q->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo)
            ]);
            $query->withCount(['faturas as faturas_pendentes_ou_parciais_count' => fn($q) => 
                $q->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo)
                  ->whereIn('status', ['pendente', 'recebida_parcial'])
            ]);

            $mesFormatado = Carbon::createFromFormat('Y-m', $periodo)->locale('pt_BR')->translatedFormat('F');
            $anoFormatado = Carbon::createFromFormat('Y-m', $periodo)->format('Y');

            return DataTables::of($query)
                ->addColumn('mes', $mesFormatado)
                ->addColumn('ano', $anoFormatado)
                ->addColumn('valor_bruto_total', function ($row) {
                    $valor = ($row->empresa_tipo_id == 1) ? $row->valor_bruto_matriz : $row->valor_bruto_unidade;
                    return 'R$ ' . number_format($valor, 2, ',', '.');
                })
                ->addColumn('status', function ($row) {
                    $valorPendente = ($row->empresa_tipo_id == 1) ? $row->valor_pendente_matriz : $row->valor_pendente_unidade;
                    $valorBruto = ($row->empresa_tipo_id == 1) ? $row->valor_bruto_matriz : $row->valor_bruto_unidade;
                    
                    $totalFaturas = $row->faturas_count;
                    $totalParciais = $row->faturas_pendentes_ou_parciais_count;
                    
                    if ($totalFaturas == 0) {
                        return ($valorBruto > 0.01) 
                            ? '<span class="badge badge-secondary">Não Iniciado</span>' 
                            : '<span class="badge badge-light">Nada a Faturar</span>';
                    }
                    
                    if ($valorPendente > 0.01) return '<span class="badge badge-info">Pendente</span>';
                    if ($totalParciais > 0) return '<span class="badge badge-warning">Aguardando Pagamento</span>';
                    
                    return '<span class="badge badge-success">Pago</span>';
                })
                ->addColumn('action', function ($row) use ($periodo) {
                    $url = route('faturamento.show', ['cliente_id' => $row->id, 'periodo' => $periodo]);
                    return '<a href="' . $url . '" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> Visualizar</a>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        // Carregamento inicial da View
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

    /**
     * --- NOVO MÉTODO PARA OS CARDS DA INDEX ---
     */
    public function getIndexStats(Request $request)
    {
        // 1. Prepara Datas
        $periodo = $request->input('periodo', Carbon::now()->subMonth()->format('Y-m'));
        try {
            $dataInicio = Carbon::createFromFormat('Y-m', $periodo)->startOfMonth();
            $dataFim = $dataInicio->copy()->endOfMonth();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Data inválida'], 400);
        }

        // 2. Obtém a Query de Empresas Filtradas (reutilizando a lógica)
        $queryEmpresas = Empresa::whereIn('empresa_tipo_id', [1, 2]);
        $this->applyIndexFilters($queryEmpresas, $request, $periodo, $dataInicio, $dataFim);
        
        // Pega apenas os IDs para usar nas queries agregadas (mais leve que hidratar models)
        $empresaIds = $queryEmpresas->pluck('id');

        if ($empresaIds->isEmpty()) {
            return response()->json([
                'pendente_geracao' => 'R$ 0,00',
                'qtd_faturas' => 0,
                'valor_gerado' => 'R$ 0,00',
                'valor_pago' => 'R$ 0,00',
                'pendente_pagamento' => 'R$ 0,00',
                'valor_ir' => 'R$ 0,00',
            ]);
        }

        // 3. Cálculos Agregados no Banco de Dados

        // A. Métricas de Transações (Pendente de Geração)
        // Soma transações onde o cliente_id OU unidade_id está na lista filtrada
        $valorPendenteGeracao = TransacaoFaturamento::whereBetween('data_transacao', [$dataInicio, $dataFim])
            ->whereIn('status', ['confirmada', 'liquidada'])
            ->where('status_faturamento', 'pendente')
            ->where(function($q) use ($empresaIds) {
                $q->whereIn('cliente_id', $empresaIds)
                  ->orWhereIn('unidade_id', $empresaIds);
            })
            ->sum(DB::raw('valor_total - valor_faturado'));

        // B. Métricas de Faturas (Geradas)
        // Baseado nos IDs das empresas filtradas e no período
        $faturasQuery = Fatura::whereIn('cliente_id', $empresaIds)
            ->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo);

        $faturasAgregadas = $faturasQuery->selectRaw('
            COUNT(*) as qtd, 
            COALESCE(SUM(valor_liquido), 0) as valor_liquido,
            COALESCE(SUM(valor_descontos), 0) as valor_ir
        ')->first();

        // C. Métricas de Pagamentos
        // Soma pagamentos das faturas filtradas acima
        $valorPago = FaturaPagamento::whereHas('fatura', function($q) use ($empresaIds, $periodo) {
            $q->whereIn('cliente_id', $empresaIds)
              ->where(DB::raw("TO_CHAR(periodo_fatura, 'YYYY-MM')"), $periodo);
        })->sum('valor_pago');

        // D. Cálculo Final
        $valorGerado = $faturasAgregadas->valor_liquido; // Usamos líquido como "Gerado" para bater com o financeiro
        $valorPendentePagamento = $valorGerado - $valorPago;

        return response()->json([
            'pendente_geracao' => 'R$ ' . number_format($valorPendenteGeracao, 2, ',', '.'),
            'qtd_faturas' => $faturasAgregadas->qtd,
            'valor_gerado' => 'R$ ' . number_format($valorGerado, 2, ',', '.'), // Líquido
            'valor_pago' => 'R$ ' . number_format($valorPago, 2, ',', '.'),
            'pendente_pagamento' => 'R$ ' . number_format(max(0, $valorPendentePagamento), 2, ',', '.'),
            'valor_ir' => 'R$ ' . number_format($faturasAgregadas->valor_ir, 2, ',', '.'), // IR das faturas geradas
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
        $parametro_owner_id = ($empresa->empresa_tipo_id == [1,2]) ? $empresa->empresa_matriz_id : $empresa->id;
        $paramCliente = ParametroCliente::where('empresa_id', $parametro_owner_id)->first();
        
        // REGRA: Se existe paramCliente E 'ativar_globais' é FALSE, usa paramCliente
        if ($paramCliente && !$paramCliente->ativar_parametros_globais) {
            
            // --- INÍCIO DA CORREÇÃO (Ponto 1 e 2 da sua solicitação) ---
            $vencimentoPersonalizado = $paramCliente->vencimento_fatura_personalizado ?? false;
            $dias_vencimento_cliente = $paramCliente->dias_vencimento ?? 30; // Pega o valor do cliente

            if ($is_publico) {
                // PÚBLICO: Usa o do cliente APENAS se a flag 'personalizado' estiver TRUE
                $dias_vencimento = $vencimentoPersonalizado
                                    ? $dias_vencimento_cliente
                                    : $paramGlobal->dias_vencimento_publico;
            } else {
                // PRIVADO: Conforme solicitado, se a fonte é 'Cliente', deve usar o parametro do cliente.
                $dias_vencimento = $dias_vencimento_cliente;
            }
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

        $contrato = null;
            if ($request->filled('contrato_id')) {
                $contrato = Contrato::find($request->contrato_id);
            }
            if (!$contrato) {
                $contrato = Contrato::where('empresa_id', $cliente->id) 
                                    ->where('contrato_situacao_id', 1)
                                    ->first();
            }
        
        // Query Base (Todas as transações do período)
        $queryBase = TransacaoFaturamento::whereBetween('data_transacao', [$dataInicio, $dataFim]) 
            ->whereIn('status', ['confirmada', 'liquidada']);

        if ($cliente->empresa_tipo_id == 1) {
            $queryBase->where('cliente_id', $cliente->id)->whereNull('unidade_id');
        } else {
            $queryBase->where('unidade_id', $cliente->id);
        }

        $queryBasePendentes = $queryBase->clone()->where('status_faturamento', 'pendente');
        $queryBaseFaturadas = $queryBase->clone()->where('status_faturamento', '!=', 'pendente');

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

        // <<<--- A VARIÁVEL É CRIADA AQUI ---
        $totaisPendentes = [
            'bruto' => $totalBrutoPendente,
            'ir' => $totalIRPendente,
            'liquido' => $totalLiquidoPendente,
            'faturado' => $totalValorFaturado, 
        ];
        
        $totaisPorUnidade = $this->getTotaisAgrupados($queryBase->clone(), 'unidade_id', 'public.empresa', 'razao_social');
        $totaisPorEmpenho = $this->getTotaisAgrupados($queryBase->clone(), 'empenho_id', 'public.empenho', 'numero_empenho');
        
        $totaisPorGrupo = $queryBase->clone() 
            ->leftJoin('public.veiculo as v', 'transacao_faturamento.veiculo_id', '=', 'v.id')
            ->leftJoin('public.grupo as subgrupo', 'v.grupo_id', '=', 'subgrupo.id')
            ->leftJoin('public.grupo as grupo_pai', 'subgrupo.grupo_id', '=', 'grupo_pai.id')
            
            // <<<--- ESTA É A PARTE IMPORTANTE ---
            ->select(
                'grupo_pai.id as grupo_pai_id',
                DB::raw("COALESCE(grupo_pai.nome, 'Sem Grupo Pai') as nome"),
                DB::raw('SUM(transacao_faturamento.valor_total) as valor_bruto'), // <-- Define "valor_bruto"
                DB::raw('COUNT(DISTINCT subgrupo.id) as subgrupos_count')
            )
            // --- FIM DA PARTE IMPORTANTE ---
            
            ->groupBy('grupo_pai.id', 'grupo_pai.nome')
            ->orderBy('valor_bruto', 'desc') // <-- Agora isso funciona
            ->get();
        
        $publico_ids = [1, 2, 3, 5];
        $is_publico = in_array($cliente->organizacao_id, $publico_ids);
        
        // <<<--- E ELA É PASSADA PARA A VIEW AQUI ---
        return view('admin.faturamento.show', compact(
            'cliente', 
            'periodo',
            'faturamentoPeriodo',
            'parametrosAtivos',
            'totaisPendentes', // <<< AQUI
            'totaisPorUnidade',
            'totaisPorEmpenho',
            'totaisPorGrupo',
            'is_publico' ,
            'contrato'
        ));
    }

    // ===================================================================
    // MÉTODOS AJAX (Abas e Modal)
    // ===================================================================

    public function getResumoAbaGeral(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
        ]);

        $billable_empresa_id = $request->input('cliente_id');
        $periodo = $request->input('periodo');
        $dataInicio = Carbon::createFromFormat('Y-m', $periodo)->startOfMonth();
        $dataFim = $dataInicio->copy()->endOfMonth();
        $cliente = Empresa::with('matriz.organizacao')->find($billable_empresa_id);

        $parametrosAtivos = $this->getParametrosAtivos($billable_empresa_id);
        
        $queryBase = TransacaoFaturamento::whereBetween('data_transacao', [$dataInicio, $dataFim]) 
            ->whereIn('status', ['confirmada', 'liquidada']);
        if ($cliente->empresa_tipo_id == 1) {
            $queryBase->where('cliente_id', $cliente->id)->whereNull('unidade_id');
        } else {
            $queryBase->where('unidade_id', $cliente->id);
        }

        // --- CORREÇÃO: Usa 'status_faturamento' como fonte da verdade ---
        $queryBasePendentes = $queryBase->clone()->where('status_faturamento', 'pendente');
        $queryBaseFaturadas = $queryBase->clone()->where('status_faturamento', '!=', 'pendente');
        // --- FIM DA CORREÇÃO ---

        // --- CORREÇÃO APLICADA ---
        $totalBrutoPendente = $queryBasePendentes->sum(DB::raw('valor_total - valor_faturado'));
        // O total faturado continua sendo o valor_total das transações marcadas como 'faturada'
        $totalValorFaturado = $queryBaseFaturadas->sum('valor_total');
        
        $matriz = ($cliente->empresa_tipo_id == 2) ? $cliente->matriz : $cliente;
        $organizacao_id_para_taxa = $matriz ? $matriz->organizacao_id : null;

        $totalIRPendente = 0;
        if (!$parametrosAtivos['isento_ir'] && $organizacao_id_para_taxa) {
            // O IR Pendente SÓ deve ser calculado sobre transações pendentes
            $subQuery = $queryBasePendentes->clone()
                ->join('public.produto as p', 'transacao_faturamento.produto_id', '=', 'p.id')
                ->leftJoin('contas_receber.parametro_taxa_aliquota as pta', function ($join) use ($organizacao_id_para_taxa) {
                    $join->on('p.produto_categoria_id', '=', 'pta.produto_categoria_id')
                         ->where('pta.organizacao_id', '=', $organizacao_id_para_taxa);
                })
                ->select(DB::raw('SUM((transacao_faturamento.valor_total - transacao_faturamento.valor_faturado) * COALESCE(pta.taxa_aliquota, 0)) as total_ir_calculado'));
            $totalIRPendente = $subQuery->first()->total_ir_calculado ?? 0;
        }
            
        $totalLiquidoPendente = $totalBrutoPendente;
        if ($parametrosAtivos['descontar_ir_fatura']) {
            $totalLiquidoPendente = $totalBrutoPendente - $totalIRPendente;
        }

        return response()->json([
            'bruto' => 'R$ ' . number_format($totalBrutoPendente, 2, ',', '.'),
            'ir' => 'R$ ' . number_format($totalIRPendente, 2, ',', '.'),
            'liquido' => 'R$ ' . number_format($totalLiquidoPendente, 2, ',', '.'),
            'faturado' => 'R$ ' . number_format($totalValorFaturado, 2, ',', '.'),
        ]);
    }

   public function getSubgrupos(Request $request)
    {
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
             if ($request->grupo_pai_id == 'null' || $request->grupo_pai_id == 0) {
                 $query->where(function($q) {
                    $q->whereNull('subgrupo.grupo_id')
                      ->orWhereNull('v.grupo_id');
                });
             } else {
                $query->where('subgrupo.grupo_id', $request->grupo_pai_id);
             }
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
     * CORREÇÃO 2: Adicionados botões 'btn-editar-fatura' e 'btn-registrar-pagamento'
     */
    public function getFaturas(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
        ]);

        $query = Fatura::with(['pagamentos', 'descontos'])
            ->where('cliente_id', $request->cliente_id)
            ->whereRaw("TO_CHAR(periodo_fatura, 'YYYY-MM') = ?", [$request->periodo])
            ->orderBy('id', 'desc');

        return DataTables::of($query)
            ->addColumn('checkbox', function($row) {
                $disabled = $row->status == 'recebida' ? 'disabled' : '';
                return '<input type="checkbox" class="fatura-checkbox" data-id="'.$row->id.'" '.$disabled.'>';
            })
            ->addColumn('action', function($row){
                $btn = '<div class="dropdown">
                          <button class="btn btn-xs btn-default" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                          </button>
                          <div class="dropdown-menu dropdown-menu-right">';
                          
                $btn .= '<a href="'. route('faturamento.exportFaturaPDF', $row) .'" target="_blank" class="dropdown-item">
                            <i class="fa fa-print text-primary mr-2"></i> Imprimir Fatura
                         </a>';

                if ($row->status == 'recebida') {
                    $btn .= '<button type="button" class="dropdown-item btn-ver-comprovantes" data-id="'.$row->id.'">
                                <i class="fa fa-receipt text-success mr-2"></i> Ver Comprovantes
                             </button>';
                    $btn .= '<button type="button" class="dropdown-item btn-editar-fatura" data-id="'.$row->id.'">
                                <i class="fa fa-pencil-alt text-warning mr-2"></i> Editar / Refaturar
                             </button>';
                } else {
                    $btn .= '<button type="button" class="dropdown-item btn-editar-fatura" data-id="'.$row->id.'">
                                <i class="fa fa-pencil-alt text-warning mr-2"></i> Editar Fatura
                             </button>';
                    $btn .= '<button type="button" class="dropdown-item btn-registrar-pagamento" data-id="'.$row->id.'">
                                <i class="fa fa-dollar-sign text-success mr-2"></i> Registrar Pagamento
                             </button>';
                    $btn .= '<button type="button" class="dropdown-item btn-aplicar-desconto" data-id="'.$row->id.'">
                                <i class="fa fa-tag text-info mr-2"></i> Aplicar Desconto
                             </button>';
                }

                $btn .= '<div class="dropdown-divider"></div>';

                $btn .= '<button type="button" class="dropdown-item btn-editar-observacao" data-id="'.$row->id.'">
                            <i class="fa fa-edit text-primary mr-2"></i> Editar Observação
                         </button>';
                
                if ($row->status != 'recebida') {
                    $btn .= '<button type="button" class="dropdown-item btn-excluir" data-id="'.$row->id.'">
                                <i class="fa fa-trash text-danger mr-2"></i> Excluir
                             </button>';
                }

                $btn .= '</div></div>';
                return $btn;
            })
            ->editColumn('data_emissao', fn($row) => $row->data_emissao ? Carbon::parse($row->data_emissao)->format('d-m-Y') : '-')
            ->editColumn('data_vencimento', fn($row) => $row->data_vencimento ? Carbon::parse($row->data_vencimento)->format('d-m-Y') : '-')
            ->editColumn('valor_total', fn($row) => 'R$ ' . number_format($row->valor_total, 2, ',', '.'))
            ->editColumn('valor_impostos', fn($row) => 'R$ ' . number_format($row->valor_impostos, 2, ',', '.'))
            ->editColumn('valor_descontos', fn($row) => 'R$ ' . number_format($row->valor_descontos, 2, ',', '.'))
            ->addColumn('desconto_manual', function($row) {
                return 'R$ ' . number_format($row->valor_descontos_manuais, 2, ',', '.');
            })
            ->addColumn('taxa_adm', fn($row) => number_format($row->taxa_adm_percent, 2, ',', '.') . '%')
            
            ->addColumn('tipo_taxa', function($row) {
                if ($row->taxa_adm_percent < 0) {
                    return '<span class="badge badge-danger">Negativa</span>';
                } else {
                    return '<span class="badge badge-success">Positiva</span>';
                }
            })

            ->addColumn('valor_taxa', function($row) {
                return 'R$ ' . number_format($row->taxa_adm_valor, 2, ',', '.');
            })
            
            ->editColumn('valor_liquido', fn($row) => 'R$ ' . number_format($row->valor_liquido, 2, ',', '.'))
            ->addColumn('valor_recebido', function ($row) {
                return 'R$ ' . number_format($row->pagamentos->sum('valor_pago'), 2, ',', '.');
            })
            ->addColumn('saldo_pendente', function ($row) {
                return 'R$ ' . number_format($row->saldo_pendente, 2, ',', '.');
            })
            ->editColumn('status', function ($row) {
                return match($row->status) {
                    'recebida' => '<span class="badge badge-success">Recebida</span>',
                    'pendente' => '<span class="badge badge-warning">Pendente</span>',
                    'recebida_parcial' => '<span class="badge badge-info">Recebida Parcial</span>',
                    'cancelada' => '<span class="badge badge-danger">Cancelada</span>',
                    'aguardando_pagamento' => '<span class="badge badge-warning">Aguardando Pagamento</span>',
                    default => ucfirst($row->status),
                };
            })
            
            // <<<--- CORREÇÃO AQUI ---
            ->rawColumns(['checkbox', 'action', 'status', 'tipo_taxa'])
            // --- FIM DA CORREÇÃO ---
            
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
            ->addColumn('faturada', fn($row) => $row->status_faturamento == 'pendente' ? '<span class="badge badge-warning">Não</span>' : '<span class="badge badge-success">Sim</span>')
            ->addColumn('credenciado_nome', fn($row) => optional($row->credenciado)->razao_social ?? 'N/A')
            ->addColumn('produto_nome', fn($row) => optional($row->produto)->nome ?? 'N/A')
            
            ->addColumn('grupo_nome', fn($row) => optional(optional(optional($row->veiculo)->grupo)->grupoPai)->nome ?? 'N/A') 
            ->addColumn('subgrupo_nome', fn($row) => optional(optional($row->veiculo)->grupo)->nome ?? 'N/A')
            ->addColumn('placa', fn($row) => optional($row->veiculo)->placa ?? 'N/A')
            
            // --- INÍCIO DA CORREÇÃO (Cálculo de IR) ---
            ->addColumn('aliquota_ir', function($row) use ($parametrosAtivos, $taxas) {
                if ($parametrosAtivos['isento_ir']) {
                    $aliquota_ir = 0;
                } else {
                    $categoriaId = optional($row->produto)->produto_categoria_id;
                    $taxa = $taxas->get($categoriaId);
                    $aliquota_ir = $taxa ? $taxa->taxa_aliquota : 0;
                }
                // Formatação movida para dentro do addColumn
                return number_format($aliquota_ir * 100, 2, ',', '.') . '%';
            })
            ->addColumn('valor_ir', function($row) use ($parametrosAtivos, $taxas) {
                if ($parametrosAtivos['isento_ir']) {
                    $valor_ir = 0;
                } else {
                    $categoriaId = optional($row->produto)->produto_categoria_id;
                    $taxa = $taxas->get($categoriaId);
                    $aliquota_ir = $taxa ? $taxa->taxa_aliquota : 0;
                    $valor_ir = $row->valor_total * $aliquota_ir;
                }
                // Formatação movida para dentro do addColumn
                return 'R$ ' . number_format($valor_ir, 2, ',', '.');
            })
            // --- FIM DA CORREÇÃO ---

            ->editColumn('valor_unitario', fn($row) => 'R$ ' . number_format($row->valor_unitario, 2, ',', '.'))
            ->editColumn('valor_total', fn($row) => 'R$ ' . number_format($row->valor_total, 2, ',', '.'))
            
            // --- REMOVIDO (Movido para addColumn) ---
            // ->editColumn('aliquota_ir', fn($row) => number_format($row->aliquota_ir * 100, 2, ',', '.') . '%')
            // ->editColumn('valor_ir', fn($row) => 'R$ ' . number_format($row->valor_ir, 2, ',', '.'))
            
            ->rawColumns(['faturada'])
            ->make(true);
    }
   
public function gerarFatura(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
            'data_vencimento' => 'required|date',
            'tipo_geracao' => 'required|string|in:Total,Fracionada',
            'nota_fiscal' => 'nullable|string|max:100',
            'contrato_id' => 'nullable|array',
            'empenho_id' => 'nullable|array',
            'grupo_id' => 'nullable|array',
            'subgrupo_id' => 'nullable|array',
            'valor_fatura_calculado' => 'required|numeric|min:0.01', 
        ]);

        $empresa = Empresa::find($request->cliente_id);
        $publico_ids = [1, 2, 3, 5];
        $is_publico = in_array($empresa->organizacao_id, $publico_ids);

        if ($is_publico && $request->tipo_geracao == 'Fracionada' && 
            !$request->filled('grupo_id') && 
            !$request->filled('subgrupo_id') && 
            !$request->filled('empenho_id')
        ) {
            return response()->json(['success' => false, 'message' => 'Cliente Público: Selecione ao menos um filtro.'], 422);
        }
        
        $paramGlobal = ParametroGlobal::first();
        if (!$paramGlobal) {
            return response()->json(['success' => false, 'message' => 'Erro: Parâmetros Globais ausentes.'], 500);
        }

        DB::beginTransaction();
        try {
            $billable_empresa_id = $request->cliente_id;
            $periodo = $request->periodo;
            
            $empresa = Empresa::with('codigoDealer')->find($billable_empresa_id);
            $parametrosAtivos = $this->getParametrosAtivos($billable_empresa_id);
            $matriz = ($empresa->empresa_tipo_id == 2) ? $empresa->matriz : $empresa;
            $organizacao_id_para_taxa = $matriz ? $matriz->organizacao_id : null;
            $taxas = collect();
            if ($organizacao_id_para_taxa) {
                $taxas = ParametroTaxaAliquota::where('organizacao_id', $organizacao_id_para_taxa)
                                        ->get()
                                        ->keyBy('produto_categoria_id');
            }
            
            // 1. CALCULAR TOTAIS PENDENTES REAIS
            $queryBase = $this->buildPendentesQuery($request);
            
            if ($request->tipo_geracao == 'Fracionada') {             
                if ($is_publico && $request->filled('empenho_id')) {
                    $queryBase->whereIn('empenho_id', $request->empenho_id);
                }

                // Tratamento de "null" (Sem Grupo)
                if ($request->filled('grupo_id')) {
                    $grupoIds = $request->grupo_id;
                    $idsNumericos = array_filter($grupoIds, fn($v) => is_numeric($v));
                    $incluirSemGrupo = in_array('null', $grupoIds) || in_array(null, $grupoIds);

                    $queryBase->whereHas('veiculo.grupo', function($q) use ($idsNumericos, $incluirSemGrupo) {
                        $q->where(function($sub) use ($idsNumericos, $incluirSemGrupo) {
                            if (!empty($idsNumericos)) $sub->whereIn('grupo_id', $idsNumericos);
                            if ($incluirSemGrupo) $sub->orWhereNull('grupo_id');
                        });
                    });
                }

                // Tratamento de "null" (Sem Subgrupo)
                if ($request->filled('subgrupo_id')) {
                    $subIds = $request->subgrupo_id;
                    $idsNumericos = array_filter($subIds, fn($v) => is_numeric($v));
                    $incluirSemSub = in_array('null', $subIds) || in_array(null, $subIds);

                    $queryBase->whereHas('veiculo', function($q) use ($idsNumericos, $incluirSemSub) {
                        $q->where(function($sub) use ($idsNumericos, $incluirSemSub) {
                             if (!empty($idsNumericos)) $sub->whereIn('grupo_id', $idsNumericos);
                             if ($incluirSemSub) $sub->orWhereNull('grupo_id');
                        });
                    });
                }
            }

            $totalBrutoPendenteReal = $queryBase->clone()->sum(DB::raw('valor_total - valor_faturado'));
            
            $transacoesParaFaturar = $queryBase->clone()
                                    ->with('produto', 'veiculo.grupo') 
                                    ->orderBy('data_transacao', 'asc')
                                    ->orderBy('id', 'asc')
                                    ->get();

            $totalIRPendenteReal = 0; 
            foreach ($transacoesParaFaturar as $transacao) {
                $valorPendenteDaTransacao = $transacao->valor_pendente; 
                if (!$parametrosAtivos['isento_ir'] && $valorPendenteDaTransacao > 0) {
                    $categoriaId = optional($transacao->produto)->produto_categoria_id ?? 0;
                    $taxa = $taxas->get($categoriaId);
                    $aliquota = $taxa ? $taxa->taxa_aliquota : 0;
                    $totalIRPendenteReal += ($valorPendenteDaTransacao * $aliquota); 
                }
            }

            // 2. VALIDAR VALOR
            if ($transacoesParaFaturar->isEmpty() || $totalBrutoPendenteReal < 0.01) {
                return response()->json(['success' => false, 'message' => 'Nenhuma transação pendente encontrada para esses filtros.'], 400);
            }
            
            $valorDesejadoFaturar = (float) $request->valor_fatura_calculado;

            if ($request->tipo_geracao == 'Fracionada') {
                if ($valorDesejadoFaturar > ($totalBrutoPendenteReal + 0.001)) { 
                    return response()->json([
                        'success' => false, 
                        'message' => sprintf(
                            'Valor a faturar (R$ %s) não pode ser maior que o total pendente para estes filtros (R$ %s).',
                            number_format($valorDesejadoFaturar, 2, ',', '.'),
                            number_format($totalBrutoPendenteReal, 2, ',', '.')
                        )
                    ], 400);
                }
            } else {
                 if (abs($totalBrutoPendenteReal - $valorDesejadoFaturar) > 0.01) {
                      return response()->json(['success' => false, 'message' => 'Inconsistência de valores no modo Total. Recarregue e tente novamente.'], 400);
                 }
            }

            // 3. CRIAR A FATURA
            $horaMinuto = Carbon::now()->format('Hi'); 
            $numeroFatura = 'FAT-' . $billable_empresa_id . '' . $horaMinuto . '' . rand(0, 1000);
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

            // 4. VINCULAÇÃO
            $totalRealFaturadoNestaNota = 0;
            $totalImpostosItens = 0; 
            $valorRestanteParaFaturar = $valorDesejadoFaturar; 

            foreach ($transacoesParaFaturar as $transacao) {
                if ($valorRestanteParaFaturar <= 0.001) {
                    break; 
                }
                $valorPendenteDaTransacao = $transacao->valor_pendente; 
                if ($valorPendenteDaTransacao <= 0.001) continue; 

                $valorAFaturarDestaTransacao = min($valorRestanteParaFaturar, $valorPendenteDaTransacao);

                $transacao->valor_faturado += $valorAFaturarDestaTransacao;
                
                if (abs($transacao->valor_total - $transacao->valor_faturado) < 0.01) {
                    $transacao->status_faturamento = 'faturada';
                    $transacao->fatura_id = $fatura->id; 
                }
                $transacao->save();
                
                FaturaItem::create([
                    'fatura_id' => $fatura->id,
                    'transacao_faturamento_id' => $transacao->id,
                    'descricao_produto' => optional($transacao->produto)->nome ?? 'N/A',
                    'produto_id' => $transacao->produto_id,
                    'produto_categoria_id' => optional($transacao->produto)->produto_categoria_id ?? 0,
                    'quantidade' => 1, 
                    'valor_unitario' => $valorAFaturarDestaTransacao, 
                    'valor_subtotal' => $valorAFaturarDestaTransacao,
                    'aliquota_aplicada' => 0, 
                    'valor_imposto' => 0,
                    'valor_total_item' => $valorAFaturarDestaTransacao,
                ]);

                $totalRealFaturadoNestaNota += $valorAFaturarDestaTransacao;
                $valorRestanteParaFaturar -= $valorAFaturarDestaTransacao;
            }
            
            // 5. ATUALIZAR TOTAIS
            $faturaBruta = $totalRealFaturadoNestaNota;
            $faturaDescontoIR = 0;

            if ($parametrosAtivos['descontar_ir_fatura'] && $totalBrutoPendenteReal > 0.01) {
                $taxaDeIRMedia = $totalIRPendenteReal / $totalBrutoPendenteReal;
                $faturaDescontoIR = round($faturaBruta * $taxaDeIRMedia, 2);
            }

            $contrato = null;
            if ($request->filled('contrato_id')) {
                $contrato = Contrato::find($request->contrato_id[0]); 
            }
            if (!$contrato) {
                $matriz = ($empresa->empresa_tipo_id == 2) ? $empresa->matriz : $empresa;
                $contrato = Contrato::where('empresa_id', $matriz->id) 
                                    ->where('contrato_situacao_id', 1)
                                    ->first();
            }

            $taxaAdmPercent = $contrato ? (float)($contrato->taxa_administrativa ?? 0) : 0;
            $valorTaxaAdm = round(($faturaBruta * $taxaAdmPercent) / 100, 2);
            
            $fatura->valor_total = $faturaBruta; 
            $fatura->valor_impostos = $totalImpostosItens; 
            $fatura->valor_descontos = $faturaDescontoIR; 
            $fatura->taxa_adm_percent = $taxaAdmPercent;
            $fatura->taxa_adm_valor = $valorTaxaAdm;
            $fatura->valor_liquido = $faturaBruta - $faturaDescontoIR + $valorTaxaAdm;
            
            // 6. GERAÇÃO DA OBSERVAÇÃO (CORRIGIDO O ERRO "integer: null")
            $textoContrato = "";
            $textoTaxa = "";
            if ($contrato) { 
                $textoContrato = "(CONTRATO nº {$contrato->numero})";
                $taxaAdm = (float)($contrato->taxa_administrativa ?? 0); 
                $labelTaxa = $taxaAdm < 0 ? 'Taxa Negativa' : 'Taxa Positiva';
                $valorTaxaFormatado = number_format($taxaAdm, 2, ',', '.'). '%';
                $textoTaxa = "| $labelTaxa: $valorTaxaFormatado";
            }
            $textoEmpenho = "";
            if ($is_publico && $request->filled('empenho_id')) {
                $empenhos = Empenho::whereIn('id', $request->empenho_id)->pluck('numero_empenho');
                if ($empenhos->isNotEmpty()) {
                    $textoEmpenho = "| Empenho(s): " . $empenhos->implode(', ');
                }
            }
            $textoDealer = "";
            if ($empresa->codigoDealer && !empty($empresa->codigoDealer->cod_dealer)) {
                $textoDealer = "| DEALER: " . $empresa->codigoDealer->cod_dealer;
            }
            $periodoCarbon = Carbon::createFromFormat('Y-m', $periodo);
            $textoPeriodo = $periodoCarbon->locale('pt_BR')->translatedFormat('F/Y');
            $textoVencimento = Carbon::parse($request->data_vencimento)->format('d/m/Y');
            $textoFiltros = "";

            if ($request->tipo_geracao == 'Fracionada') {
                $filtrosUsados = [];

                // --- CORREÇÃO DA OBSERVAÇÃO PARA GRUPO ---
                if ($request->filled('grupo_id')) {
                    $grupoIds = $request->grupo_id;
                    $idsNumericos = array_filter($grupoIds, fn($v) => is_numeric($v));
                    $nomes = [];
                    
                    if (!empty($idsNumericos)) {
                        $nomes = Grupo::whereIn('id', $idsNumericos)->pluck('nome')->toArray();
                    }
                    if (in_array('null', $grupoIds) || in_array(null, $grupoIds)) {
                        $nomes[] = "Sem Grupo";
                    }
                    
                    if (!empty($nomes)) {
                        $filtrosUsados[] = "Grupo(s): " . implode(', ', $nomes);
                    }
                }

                // --- CORREÇÃO DA OBSERVAÇÃO PARA SUBGRUPO ---
                if ($request->filled('subgrupo_id')) {
                    $subIds = $request->subgrupo_id;
                    $idsNumericos = array_filter($subIds, fn($v) => is_numeric($v));
                    $nomes = [];

                    if (!empty($idsNumericos)) {
                        $nomes = Grupo::whereIn('id', $idsNumericos)->pluck('nome')->toArray();
                    }
                    if (in_array('null', $subIds) || in_array(null, $subIds)) {
                        $nomes[] = "Sem Subgrupo";
                    }

                    if (!empty($nomes)) {
                        $filtrosUsados[] = "Subgrupo(s): " . implode(', ', $nomes);
                    }
                }

                if ($request->filled('contrato_id')) {
                    $contratos = Contrato::whereIn('id', $request->contrato_id)->pluck('numero');
                     if ($contratos->isNotEmpty()) $filtrosUsados[] = "Contrato(s): " . $contratos->implode(', ');
                }
                if (!empty($filtrosUsados)) {
                    $textoFiltros = "| Filtros: (" . implode('; ', $filtrosUsados) . ")";
                }
            }
            // --- FIM DAS CORREÇÕES ---

            $obs = [];
            if ($textoContrato) $obs[] = $textoContrato;
            $obs[] = "Valor Bruto: R$ " . number_format($fatura->valor_total, 2, ',', '.');
            if ($textoTaxa) $obs[] = $textoTaxa;
            $obs[] = "| Valor Líquido: R$ " . number_format($fatura->valor_liquido, 2, ',', '.');
            $obs[] = "| IR Retido: R$ " . number_format($fatura->valor_descontos, 2, ',', '.');
            $obs[] = "| Período: " . $textoPeriodo;
            if ($is_publico) {if ($textoEmpenho) $obs[] = $textoEmpenho; else null;}
            if ($textoDealer)  $obs[] = $textoDealer; 
            if ($textoFiltros) $obs[] = $textoFiltros;
            $obs[] = "| Vencimento: " . $textoVencimento;
            $dadosBancarios = "DADOS BANCÁRIOS: BANCO: {$paramGlobal->banco} | AGÊNCIA: {$paramGlobal->agencia} | C/C: {$paramGlobal->conta} | CNPJ: {$paramGlobal->cnpj} – {$paramGlobal->razao_social} PIX: {$paramGlobal->chave_pix}";
            $fatura->observacoes = trim(implode(' ', $obs)) . ' ' . $dadosBancarios;

            $fatura->save();
            
            DB::commit();
            return response()->json(['success' => true, 'message' => "Fatura #{$fatura->id} (Valor: R$ ".number_format($fatura->valor_liquido, 2, ',', '.').") gerada com sucesso!"]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro ao gerar fatura: ' . $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }
    
   public function destroyFatura(Fatura $fatura)
    {
        if ($fatura->status == 'recebida') {
            return response()->json([
                'success' => false,
                'message' => 'Faturas recebidas não podem ser excluídas. Reabra a fatura primeiro.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $itens = FaturaItem::where('fatura_id', $fatura->id)->get();
            
            foreach ($itens as $item) {
                $transacao = TransacaoFaturamento::find($item->transacao_faturamento_id);
                if ($transacao) {
                    // Devolve o valor do item para a transação
                    $transacao->valor_faturado -= $item->valor_total_item;
                    
                    if ($transacao->valor_faturado < 0.01) {
                        $transacao->valor_faturado = 0;
                    }
                    
                    // Marca como pendente (pois agora tem valor a faturar)
                    $transacao->status_faturamento = 'pendente';
                    $transacao->fatura_id = null; // <-- CORREÇÃO: Limpa o fatura_id
                    $transacao->save();
                }
            }
            
            FaturaPagamento::where('fatura_id', $fatura->id)->delete();
            FaturaDesconto::where('fatura_id', $fatura->id)->delete();
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
        // ... (Esta função agora só é chamada pelo bulk-action) ...
        $saldoPendente = $fatura->saldo_pendente;

        if ($saldoPendente > 0) {
            $pagamento = new FaturaPagamento([
                'fatura_id' => $fatura->id,
                'data_pagamento' => Carbon::now(),
                'valor_pago' => $saldoPendente,
                'comprovante_path' => null,
                'registrado_por_user_id' => auth()->id(),
            ]);
            $pagamento->save();
        }

        $fatura->status = 'recebida';
        $fatura->save();

        return response()->json(['success' => true, 'message' => 'Fatura marcada como recebida.']);
    }

    
    public function getContratosCliente(Request $request)
    {
        $request->validate(['cliente_id' => 'required|integer|exists:empresa,id']);
        $empresa = Empresa::find($request->query('cliente_id'));
        $matriz_id = ($empresa->empresa_tipo_id == [1,2]) ? $empresa->empresa_matriz_id : $empresa->id;
        
        $contratos = Contrato::where('empresa_id', $matriz_id)
                            ->where('contrato_situacao_id', 1) // Apenas ativos
                            ->get(['id', 'numero']); 
        
        return response()->json($contratos);
    }

    /**
     * getEmpenhosPendentes (Corrigido - Fix erro SQL 'integer: null')
     */
    public function getEmpenhosPendentes(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
            'grupo_id' => 'nullable|array', 
            'subgrupo_id' => 'nullable|array',
        ]);
        
        $queryBasePendentes = $this->buildPendentesQuery($request);
        
        // --- CORREÇÃO APLICADA AQUI ---
        if ($request->filled('grupo_id')) {
            $grupoIds = $request->grupo_id;
            $idsNumericos = array_filter($grupoIds, fn($v) => is_numeric($v));
            $incluirSemGrupoPai = in_array('null', $grupoIds) || in_array(null, $grupoIds);

            $queryBasePendentes->whereHas('veiculo.grupo', function($q) use ($idsNumericos, $incluirSemGrupoPai) {
                $q->where(function($sub) use ($idsNumericos, $incluirSemGrupoPai) {
                    if (!empty($idsNumericos)) {
                        $sub->whereIn('grupo_id', $idsNumericos);
                    }
                    if ($incluirSemGrupoPai) {
                        $sub->orWhereNull('grupo_id');
                    }
                });
            });
        }
        // --- FIM DA CORREÇÃO ---

        if ($request->filled('subgrupo_id')) {
             $parent_ids = Grupo::whereIn('id', array_filter($request->subgrupo_id, 'is_numeric'))
                            ->pluck('grupo_id')
                            ->unique()
                            ->filter(); 
            $includes_null_subgroup = collect($request->subgrupo_id)->contains(function($value) {
                if ($value == 'null' || $value == null) return true;
                $grupo = Grupo::find($value); 
                return $grupo && $grupo->grupo_id === null;
            });
            $queryBasePendentes->where(function($q) use ($parent_ids, $includes_null_subgroup) {
                if ($parent_ids->isNotEmpty()) {
                    $q->whereHas('veiculo.grupo', fn($q2) => $q2->whereIn('grupo_id', $parent_ids));
                }
                if ($includes_null_subgroup) {
                    $q->orWhere(function($q_null) {
                        $q_null->whereDoesntHave('veiculo.grupo') 
                               ->orWhereHas('veiculo.grupo', fn($q3) => $q3->whereNull('grupo_id'));
                    });
                }
            });
        }

        $empenhos = $queryBasePendentes
            ->leftJoin('public.empenho', 'transacao_faturamento.empenho_id', '=', 'empenho.id')
            ->select(
                'empenho.id',
                'empenho.numero_empenho',
                'empenho.valor', 
                DB::raw('SUM(transacao_faturamento.valor_total - transacao_faturamento.valor_faturado) as total_pendente')
            )
            ->whereNotNull('transacao_faturamento.empenho_id')
            ->groupBy('empenho.id', 'empenho.numero_empenho', 'empenho.valor')
            ->get();
            
        return response()->json($empenhos);
    }
    /**
     * getGruposPendentes (Corrigido - Fix erro SQL 'integer: null')
     */
    public function getGruposPendentes(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
            'empenho_id' => 'nullable|array',
            'subgrupo_id' => 'nullable|array',
            'grupo_id' => 'nullable|array',
        ]);

        $queryBasePendentes = $this->buildPendentesQuery($request);

        // --- Filtros em cascata ---
        $queryBasePendentes->when($request->filled('empenho_id'), 
            fn($q) => $q->whereIn('empenho_id', $request->empenho_id));
        
        // --- CORREÇÃO APLICADA AQUI ---
        if ($request->filled('grupo_id')) {
            $grupoIds = $request->grupo_id;
            // 1. Pega apenas os IDs numéricos reais (ex: [36, 37])
            $idsNumericos = array_filter($grupoIds, fn($v) => is_numeric($v));
            // 2. Verifica se a string "null" ou valor nulo foi enviado
            $incluirSemGrupoPai = in_array('null', $grupoIds) || in_array(null, $grupoIds);

            $queryBasePendentes->whereHas('veiculo.grupo', function($q) use ($idsNumericos, $incluirSemGrupoPai) {
                $q->where(function($sub) use ($idsNumericos, $incluirSemGrupoPai) {
                    if (!empty($idsNumericos)) {
                        $sub->whereIn('grupo_id', $idsNumericos);
                    }
                    if ($incluirSemGrupoPai) {
                        $sub->orWhereNull('grupo_id');
                    }
                });
            });
        }
        // --- FIM DA CORREÇÃO ---
        
        // Lógica de "subgrupos irmãos"
        if ($request->filled('subgrupo_id')) {
            $parent_ids = Grupo::whereIn('id', array_filter($request->subgrupo_id, 'is_numeric')) 
                            ->pluck('grupo_id')
                            ->unique()
                            ->filter(); 

            $includes_null_subgroup = collect($request->subgrupo_id)->contains(function($value) {
                if ($value == 'null' || $value == null) return true;
                $grupo = Grupo::find($value); 
                return $grupo && $grupo->grupo_id === null;
            });
                                          
            $queryBasePendentes->where(function($q) use ($parent_ids, $includes_null_subgroup) {
                if ($parent_ids->isNotEmpty()) {
                    $q->whereHas('veiculo.grupo', fn($q2) => $q2->whereIn('grupo_id', $parent_ids));
                }
                
                if ($includes_null_subgroup) {
                    $q->orWhere(function($q_null) {
                        $q_null->whereDoesntHave('veiculo.grupo') 
                               ->orWhereHas('veiculo.grupo', fn($q3) => $q3->whereNull('grupo_id'));
                    });
                }
            });
        }

        $grupos = $queryBasePendentes
            ->leftJoin('public.veiculo as v', 'transacao_faturamento.veiculo_id', '=', 'v.id')
            ->leftJoin('public.grupo as subgrupo', 'v.grupo_id', '=', 'subgrupo.id')
            ->leftJoin('public.grupo as grupo_pai', 'subgrupo.grupo_id', '=', 'grupo_pai.id')
            ->select(
                'grupo_pai.id as grupo_pai_id',
                DB::raw("COALESCE(grupo_pai.nome, 'Sem Grupo Pai') as grupo_pai_nome"),
                'subgrupo.id as subgrupo_id',
                DB::raw("COALESCE(subgrupo.nome, 'Sem Subgrupo') as subgrupo_nome"),
                DB::raw('SUM(transacao_faturamento.valor_total - transacao_faturamento.valor_faturado) as total_pendente')
            )
            ->groupBy('grupo_pai.id', 'grupo_pai.nome', 'subgrupo.id', 'subgrupo.nome')
            ->get();
            
        // Mapeamento
        $grupos_pais = $grupos->groupBy('grupo_pai_nome')->map(function($g) {
            return [
                'id' => $g->first()->grupo_pai_id ?? 'null',
                'text' => $g->first()->grupo_pai_nome,
                'valor_pendente' => $g->sum('total_pendente'), 
            ];
        });
        $subgrupos = $grupos->map(function($g) {
            return [
                'id' => $g->subgrupo_id ?? 'null',
                'text' => $g->subgrupo_nome,
                'grupo_pai_id' => $g->grupo_pai_id ?? 'null',
                'valor_pendente' => $g->total_pendente, 
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
            'empenho_id' => 'nullable|array',
            'grupo_id' => 'nullable|array',
            'subgrupo_id' => 'nullable|array',
        ]);
        
        $empresa = Empresa::find($request->cliente_id);
        $publico_ids = [1, 2, 3, 5];
        $is_publico = in_array($empresa->organizacao_id, $publico_ids);

        $campoValorPendente = DB::raw('valor_total - valor_faturado');

        if ($request->tipo_geracao == 'Fracionada') {
            
            $filtroSelecionado = $request->filled('grupo_id') || 
                                 $request->filled('subgrupo_id') || 
                                 ($is_publico && $request->filled('empenho_id'));

            if (!$filtroSelecionado) {
                 return response()->json(['valor_filtrado' => 0, 'limite_aplicavel' => 0]);
            }
            
            // --- POOL FILTRADO ---
            $poolQuery = $this->buildPendentesQuery($request);
            if ($is_publico && $request->filled('empenho_id')) {
                $poolQuery->whereIn('empenho_id', $request->empenho_id);
            }

            // --- CORREÇÃO (Tratamento de "null" no Pool) ---
            if ($request->filled('grupo_id')) {
                $grupoIds = $request->grupo_id;
                $idsNumericos = array_filter($grupoIds, fn($v) => is_numeric($v));
                $incluirSemGrupo = in_array('null', $grupoIds) || in_array(null, $grupoIds);
                
                $poolQuery->whereHas('veiculo.grupo', function($q) use ($idsNumericos, $incluirSemGrupo) {
                    $q->where(function($sub) use ($idsNumericos, $incluirSemGrupo) {
                        if (!empty($idsNumericos)) $sub->whereIn('grupo_id', $idsNumericos);
                        if ($incluirSemGrupo) $sub->orWhereNull('grupo_id');
                    });
                });
            }
            // --- FIM CORREÇÃO ---

            if ($request->filled('subgrupo_id')) {
                $poolQuery->whereHas('veiculo', fn($q) => $q->whereIn('grupo_id', $request->subgrupo_id));
            }
            $totalFiltrado = $poolQuery->sum($campoValorPendente); 
            
            // --- LIMITE APLICÁVEL ---
            $limiteQuery = $this->buildPendentesQuery($request);
            
            if ($is_publico && $request->filled('empenho_id')) {
                $limiteQuery->whereIn('empenho_id', $request->empenho_id);
            
            } else if ($request->filled('subgrupo_id')) {
                $limiteQuery->whereHas('veiculo', fn($q) => $q->whereIn('grupo_id', $request->subgrupo_id));
                
            } else if ($request->filled('grupo_id')) {
                // --- CORREÇÃO (Tratamento de "null" no Limite) ---
                $grupoIds = $request->grupo_id;
                $idsNumericos = array_filter($grupoIds, fn($v) => is_numeric($v));
                $incluirSemGrupo = in_array('null', $grupoIds) || in_array(null, $grupoIds);

                $limiteQuery->whereHas('veiculo.grupo', function($q) use ($idsNumericos, $incluirSemGrupo) {
                    $q->where(function($sub) use ($idsNumericos, $incluirSemGrupo) {
                        if (!empty($idsNumericos)) $sub->whereIn('grupo_id', $idsNumericos);
                        if ($incluirSemGrupo) $sub->orWhereNull('grupo_id');
                    });
                });
                // --- FIM CORREÇÃO ---
            }
            $limiteAplicavel = $limiteQuery->sum($campoValorPendente); 

        } else {
            $queryTransacoes = $this->buildPendentesQuery($request);
            $totalFiltrado = $queryTransacoes->sum($campoValorPendente); 
            $limiteAplicavel = $totalFiltrado;
        }
        
        return response()->json([
            'valor_filtrado' => $totalFiltrado,
            'limite_aplicavel' => $limiteAplicavel
        ]);
    }

    private function buildPendentesQuery(Request $request)
    {
        $billable_empresa_id = $request->input('cliente_id');
        $periodo = $request->input('periodo');
        $dataInicio = Carbon::createFromFormat('Y-m', $periodo)->startOfMonth();
        $dataFim = $dataInicio->copy()->endOfMonth();
        $empresa = Empresa::find($billable_empresa_id);

        $query = TransacaoFaturamento::whereBetween('data_transacao', [$dataInicio, $dataFim])
            ->whereIn('status', ['confirmada', 'liquidada'])
            // --- CORREÇÃO: Fonte da verdade é 'status_faturamento' ---
            ->where('status_faturamento', 'pendente');
            // ->whereNull('fatura_id'); // <-- LÓGICA ANTIGA REMOVIDA

        if ($empresa->empresa_tipo_id == 1) {
            $query->where('cliente_id', $empresa->id)->whereNull('unidade_id');
        } else {
            $query->where('unidade_id', $empresa->id);
        }
        
        return $query;
    }

    private function getLimiteScope($tipo, $id, $cliente, $periodo)
    {
        // Se o ID for "opcional" (string vazia) ou a string "null" (Sem Grupo)
        if (!$id || $id == 'null') {
             // Se for 'null', precisamos calcular o total de transações "Sem Grupo"
             // Se for '', é "Opcional", o limite não se aplica (INF)
             if ($id == '') return INF;
        }

        $dataInicio = Carbon::createFromFormat('Y-m', $periodo)->startOfMonth();
        $dataFim = $dataInicio->copy()->endOfMonth();

        $query = TransacaoFaturamento::whereBetween('data_transacao', [$dataInicio, $dataFim]) 
            ->whereIn('status', ['confirmada', 'liquidada'])
            // --- CORREÇÃO: Fonte da verdade é 'status_faturamento' ---
            ->where('status_faturamento', 'pendente');
            // ->whereNull('fatura_id'); // <-- LÓGICA ANTIGA REMOVIDA

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
                if ($id == 'null') {
                    $query->where(function($q) {
                        $q->whereDoesntHave('veiculo.grupo.grupoPai')
                          ->orWhereNull('veiculo_id');
                    });
                } else {
                    $query->whereHas('veiculo.grupo', fn($q) => $q->where('grupo_id', $id));
                }
                break;
            case 'subgrupo':
                 if ($id == 'null') {
                    $query->where(function($q) {
                        $q->whereDoesntHave('veiculo.grupo')
                          ->orWhereNull('veiculo_id');
                    });
                } else {
                    $query->whereHas('veiculo', fn($q) => $q->where('grupo_id', $id));
                }
                break;
            default:
                return INF;
        }

        return $query->sum('valor_total');
    }
/**
     * Busca o texto da observação de uma fatura.
     */
    public function getObservacao(Fatura $fatura)
    {
        return response()->json(['observacoes' => $fatura->observacoes]);
    }

    /**
     * Atualiza o texto da observação de uma fatura.
     */
    public function updateObservacao(Request $request, Fatura $fatura)
    {
        $request->validate(['observacoes' => 'nullable|string']);
        
        $fatura->observacoes = $request->observacoes;
        $fatura->save();
        
        return response()->json(['success' => true, 'message' => 'Observações da Fatura #'.$fatura->id.' atualizadas.']);
    }

    /**
     * Marca várias faturas como recebidas.
     */
    public function bulkMarcarRecebida(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        
        // <<<--- INÍCIO DA MUDANÇA: LÓGICA DE 'MARCAR RECEBIDA' EM MASSA ---
        $faturas = Fatura::whereIn('id', $request->ids)
                        ->whereIn('status', ['pendente', 'recebida_parcial'])
                        ->get();
        
        $count = 0;
        DB::beginTransaction();
        try {
            foreach ($faturas as $fatura) {
                $saldoPendente = $fatura->saldo_pendente;

                if ($saldoPendente > 0) {
                    // Cria um pagamento "virtual" para quitar
                    $pagamento = new FaturaPagamento([
                        'fatura_id' => $fatura->id,
                        'data_pagamento' => Carbon::now(),
                        'valor_pago' => $saldoPendente,
                        'comprovante_path' => null,
                        'registrado_por_user_id' => auth()->id(),
                    ]);
                    $pagamento->save();
                }

                $fatura->status = 'recebida';
                $fatura->save();
                $count++;
            }
            DB::commit();
            // --- FIM DA MUDANÇA ---
            
            return response()->json([
                'success' => true,
                'message' => "{$count} fatura(s) marcada(s) como recebida."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar faturas: ' . $e->getMessage()
            ], 500);
        }
    }

   /**
     * bulkDestroy (Atualizado)
     *
     * Aplica a mesma lógica de 'destroyFatura' para exclusão em massa.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $faturas = Fatura::whereIn('id', $request->ids)
                        ->where('status', '!=', 'recebida')
                        ->get();

        DB::beginTransaction();
        try {
            $count = 0;

            foreach ($faturas as $fatura) {
                $itens = FaturaItem::where('fatura_id', $fatura->id)->get();
                foreach ($itens as $item) {
                    $transacao = TransacaoFaturamento::find($item->transacao_faturamento_id);
                    if ($transacao) {
                        $transacao->valor_faturado -= $item->valor_total_item;
                        if ($transacao->valor_faturado < 0.01) {
                            $transacao->valor_faturado = 0;
                        }
                        $transacao->status_faturamento = 'pendente';
                        $transacao->fatura_id = null; // <-- CORREÇÃO: Limpa o fatura_id
                        $transacao->save();
                    }
                }

                $fatura->itens()->delete();
                $fatura->pagamentos()->delete();
                $fatura->descontos()->delete();
                $fatura->delete();

                $count++;
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "{$count} fatura(s) excluída(s) com sucesso."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir faturas: ' . $e->getMessage()
            ], 500);
        }
    }


 public function getFaturasSummary(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer|exists:empresa,id',
            'periodo' => 'required|date_format:Y-m',
        ]);

        $cliente_id = $request->cliente_id;
        $periodo = $request->periodo;
        $dataInicio = Carbon::createFromFormat('Y-m', $periodo)->startOfMonth();
        $dataFim = $dataInicio->copy()->endOfMonth();

        // --- 1. Lógica dos Cards (Faturas Geradas) ---
        $queryFaturas = Fatura::where('cliente_id', $cliente_id)
            ->whereRaw("TO_CHAR(periodo_fatura, 'YYYY-MM') = ?", [$periodo]);
        
        $queryPagamentos = FaturaPagamento::whereHas('fatura', function ($q) use ($cliente_id, $periodo) {
            $q->where('cliente_id', $cliente_id)
              ->whereRaw("TO_CHAR(periodo_fatura, 'YYYY-MM') = ?", [$periodo]);
        });

        $qtd_faturas = $queryFaturas->count();
        $valor_gerado = $queryFaturas->sum('valor_liquido'); // Valor líquido total faturado
        $valor_pago = $queryPagamentos->sum('valor_pago');
        
        // Saldo pendente (das faturas geradas)
        $valor_pendente_faturado = $valor_gerado - $valor_pago;


        // --- 2. Lógica do Novo Card (Pendente de Geração - Copiado de getResumoAbaGeral) ---
        
        $cliente = Empresa::with('matriz.organizacao')->find($cliente_id);
        // Garante que a função getParametrosAtivos() existe neste controller
        $parametrosAtivos = $this->getParametrosAtivos($cliente_id); 
        
        $queryBase = TransacaoFaturamento::whereBetween('data_transacao', [$dataInicio, $dataFim]) 
            ->whereIn('status', ['confirmada', 'liquidada']);
        
        if ($cliente->empresa_tipo_id == 1) {
            $queryBase->where('cliente_id', $cliente->id)->whereNull('unidade_id');
        } else {
            $queryBase->where('unidade_id', $cliente->id);
        }

        $queryBasePendentes = $queryBase->clone()->where('status_faturamento', 'pendente');
        $totalBrutoPendente = $queryBasePendentes->sum('valor_total');
        
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
            
        // Valor líquido pendente de geração
        $valor_pendente_geracao = $totalBrutoPendente;
        if ($parametrosAtivos['descontar_ir_fatura']) {
            $valor_pendente_geracao = $totalBrutoPendente - $totalIRPendente;
        }
        // --- Fim da Lógica (Pendente de Geração) ---

        return response()->json([
            'qtd_faturas' => $qtd_faturas,
            'valor_gerado' => 'R$ ' . number_format($valor_gerado, 2, ',', '.'),
            'valor_pago' => 'R$ ' . number_format($valor_pago, 2, ',', '.'),
            'valor_pendente' => 'R$ ' . number_format(max(0, $valor_pendente_faturado), 2, ',', '.'), // Das geradas
            
            // <<<--- NOVO VALOR ADICIONADO ---
            'valor_pendente_geracao' => 'R$ ' . number_format(max(0, $valor_pendente_geracao), 2, ',', '.')
        ]);
    }
}