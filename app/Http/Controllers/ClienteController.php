<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Municipio;
use App\Models\Organizacao;
use App\Models\EmpresaTipo;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Estado;
use App\Models\ParametroCliente;

class ClienteController extends Controller
{
    /**
     * Define as permissões para cada método do controller.
     */
    public function __construct()
    {
        $this->middleware('permission:view cliente', ['only' => ['index', 'getUnidades']]);
        $this->middleware('permission:show cliente', ['only' => ['show']]);
        $this->middleware('permission:edit cliente', ['only' => ['updateParametros']]); 
    }

    /**
     * Exibe a listagem de clientes (página inicial e requisições AJAX do DataTables).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // A consulta principal busca APENAS empresas do tipo 1 (Matriz)
            // e já conta as unidades de forma eficiente com withCount('unidades')
            $query = Empresa::with(['municipio.estado', 'organizacao', 'empresaTipo'])
                ->withCount('unidades')
                ->where('empresa_tipo_id', 1);

            // Aplica os filtros recebidos da view
            $query->when($request->filled('cnpj'), function ($q) use ($request) {
                // Permite buscar uma matriz pelo seu próprio CNPJ ou pelo CNPJ de uma de suas unidades
                return $q->where(function($subQuery) use ($request) {
                    $subQuery->where('cnpj', $request->cnpj)
                             ->orWhereHas('unidades', function($unitQuery) use ($request) {
                                 $unitQuery->where('cnpj', $request->cnpj);
                             });
                });
            });

            $query->when($request->filled('razao_social'), function ($q) use ($request) {
                 return $q->where(function($subQuery) use ($request) {
                    $subQuery->where('razao_social', $request->razao_social)
                             ->orWhereHas('unidades', function($unitQuery) use ($request) {
                                 $unitQuery->where('razao_social', $request->razao_social);
                             });
                });
            });

            $query->when($request->filled('municipio_id'), function ($q) use ($request) {
                return $q->where('municipio_id', $request->municipio_id);
            });
            $query->when($request->filled('organizacao_id'), function ($q) use ($request) {
                return $q->where('organizacao_id', $request->organizacao_id);
            });
             $query->when($request->filled('estado'), function ($q) use ($request) {
                return $q->whereHas('municipio.estado', function($q2) use ($request){
                    $q2->where('sigla', $request->estado);
                });
            });

            return DataTables::of($query)
                ->addColumn('details_control', function ($empresa) {
                    // A lógica usa a contagem pré-carregada
                    return $empresa->unidades_count > 0 ? '' : null;
                })
                ->addColumn('municipio_nome', fn($empresa) => $empresa->municipio->nome ?? 'N/A')
                ->addColumn('estado', fn($empresa) => $empresa->municipio->estado->sigla ?? 'N/A')
                ->addColumn('status', fn($empresa) => $empresa->ativo ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>')
                ->addColumn('action', function ($empresa) {
                    $viewBtn = '';
                    if (auth()->user()->can('show cliente')) {
                        $viewBtn = '<a href="' . route('clientes.show', $empresa->id) . '" class="btn btn-sm btn-info">Visualizar Detalhes</a>';
                    }
                    return $viewBtn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        // Dados para popular os filtros
        $baseQuery = Empresa::whereIn('empresa_tipo_id', [1, 2]);

        return view('admin.clientes.index', [
            'cnpjs' => (clone $baseQuery)->whereNotNull('cnpj')->distinct()->orderBy('cnpj')->pluck('cnpj'),
            'razoesSociais' => (clone $baseQuery)->whereNotNull('razao_social')->distinct()->orderBy('razao_social')->pluck('razao_social'),
            'municipios' => Municipio::orderBy('nome')->get(),
            'estados' => Estado::orderBy('sigla')->pluck('sigla'),
            'organizacoes' => Organizacao::orderBy('nome')->get(),
            'tipos' => EmpresaTipo::whereIn('id', [1, 2])->orderBy('nome')->get(),
        ]);
    }

    /**
     * Exibe os detalhes de um cliente específico e suas unidades.
     */
    public function show(Empresa $cliente)
    {
        $empenhoStatuses = ['aguardando_aprovacao', 'aprovado', 'fechado', 'reprovado'];

        $cliente->load([
            'municipio.estado',
            'organizacao',
            'empresaTipo',
            'ParametroCliente',
            'contratos' => function ($q_contrato) use ($empenhoStatuses) {
                $q_contrato->where('contrato_situacao_id', 1)
                    ->with([
                        'situacao',
                        'modalidade',
                        'empenhos' => function($q_empenho) use ($empenhoStatuses) {
                            $q_empenho->whereIn('situacao', $empenhoStatuses)
                                ->with('grupo')
                                ->orderBy('data_cadastro', 'desc');
                        }
                    ]);
            },
            'unidades' => function ($q_unidade) use ($empenhoStatuses) {
                $q_unidade->with([
                    'municipio.estado',
                    'organizacao',
                    'contratos' => function ($q_contrato) use ($empenhoStatuses) {
                        $q_contrato->where('contrato_situacao_id', 1)
                            ->with([
                                'situacao',
                                'modalidade',
                                'empenhos' => function($q_empenho) use ($empenhoStatuses) {
                                    $q_empenho->whereIn('situacao', $empenhoStatuses)
                                        ->with('grupo')
                                        ->orderBy('data_cadastro', 'desc');
                                }
                            ]);
                    }
                ]);
            }
        ]);

        // 🔹 Se não houver parâmetros cadastrados, define padrões
        if (!$cliente->ParametroCliente) {
            $isPublico = $cliente->empresa_tipo_id == 2; // 2 = pública
            $cliente->ParametroCliente = new \App\Models\ParametroCliente([
                'ativar_parametros_globais' => true,
                'descontar_ir_fatura' => false,
                'dias_vencimento' => $isPublico ? 30 : 15,
                'isento_ir' => false,
            ]);
        }
       // dd($cliente);
        // 🔹 Define flag para travar o formulário no front
        $bloquearCamposEspecificos = $cliente->ParametroCliente->ativar_parametros_globais;

        return view('admin.clientes.show', compact('cliente', 'bloquearCamposEspecificos'));
    }

    /**
     * Retorna a view parcial com as unidades de um cliente para a sub-tabela.
     */
    public function getUnidades(Empresa $cliente)
    {
        $unidades = $cliente->unidades()->with('municipio.estado')->get();
        return view('admin.clientes._unidades_table', compact('unidades'));
    }
    
    /**
     * Atualiza ou cria os parâmetros para um cliente (matriz ou unidade).
     */
    public function updateParametros(Request $request, Empresa $cliente)
    {
        $request->validate([
            'ativar_parametros_globais' => 'nullable|string',
            'descontar_ir_fatura' => 'nullable|string',
            'dias_vencimento' => 'nullable|integer|min:0',
            'isencao_ir' => 'nullable|string',
        ]);

        
        $ativarGlobais = $request->has('ativar_parametros_globais');
        

       //dd( $request->has('isento_ir') );
        ParametroCliente::updateOrCreate(
            ['empresa_id' => $cliente->id],
            [
                'ativar_parametros_globais' => $ativarGlobais,
                'descontar_ir_fatura' => $request->has('descontar_ir_fatura'),
                'dias_vencimento' => $request->input('dias_vencimento'),
                'isento_ir' => $request->has('isento_ir'),
            ]
        );
        

        // 🔹 Se estiver usando parâmetros globais, atualiza com base neles
        if ($ativarGlobais) {
            $global = \App\Models\ParametroGlobal::first();
            if ($global) {
                $cliente->ParametroCliente()->update([
                    'descontar_ir_fatura' => $global->descontar_ir_fatura,
                    'dias_vencimento' => $global->dias_vencimento,
                ]);
            }
        }

        return back()->with('success', 'Parâmetros salvos com sucesso!');
    }

}



