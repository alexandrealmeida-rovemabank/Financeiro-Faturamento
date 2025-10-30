<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Municipio;
use App\Models\Estado;
use App\Models\ParametroCredenciado; // Model de Parâmetros
use Yajra\DataTables\Facades\DataTables;
use App\Models\Organizacao;
use App\Models\EmpresaTipo;
use App\Models\POS;


class CredenciadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view credenciado', ['only' => ['index', 'getUnidades']]);
        $this->middleware('permission:show credenciado', ['only' => ['show']]);
        $this->middleware('permission:edit credenciado', ['only' => ['updateParametros']]);

    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Empresa::with(['municipio.estado', 'organizacao', 'empresaTipo'])
                ->withCount('unidades')
                ->where('empresa_tipo_id', 3); // Apenas matriz

            // Filtros
            $query->when($request->filled('cnpj'), function($q) use ($request) {
                $q->where(function($subQ) use ($request) {
                    $subQ->where('cnpj', $request->cnpj)
                         ->orWhereHas('unidades', function($uq) use ($request) {
                             $uq->where('cnpj', $request->cnpj);
                         });
                });
            });

            $query->when($request->filled('razao_social'), function($q) use ($request) {
                $q->where(function($subQ) use ($request) {
                    $subQ->where('razao_social', $request->razao_social)
                         ->orWhereHas('unidades', function($uq) use ($request) {
                             $uq->where('razao_social', $request->razao_social);
                         });
                });
            });

            $query->when($request->filled('municipio_id'), fn($q) => $q->where('municipio_id', $request->municipio_id));

            $query->when($request->filled('estado'), function($q) use ($request) {
                $q->whereHas('municipio.estado', fn($q2) => $q2->where('sigla', $request->estado));
            });

            $query->when($request->filled('empresa_tipo_id'), fn($q) => $q->where('empresa_tipo_id', $request->empresa_tipo_id));

            $query->when($request->filled('status'), fn($q) => $q->where('ativo', $request->status === 'Ativo' ? 1 : 0));

            return DataTables::of($query)
                ->addColumn('details_control', fn($empresa) => $empresa->unidades_count > 0 ? '' : null)
                ->addColumn('municipio_nome', fn($empresa) => $empresa->municipio->nome ?? 'N/A')
                ->addColumn('estado', fn($empresa) => $empresa->municipio->estado->sigla ?? 'N/A')
                ->addColumn('status', fn($empresa) => $empresa->ativo
                    ? '<span class="badge bg-success">Ativo</span>'
                    : '<span class="badge bg-danger">Inativo</span>')
                ->addColumn('action', function ($empresa) {
                    return auth()->user()->can('show credenciado')
                        ? '<a href="' . route('credenciados.show', $empresa->id) . '" class="btn btn-sm btn-info">Visualizar Detalhes</a>'
                        : '';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        // Dados para popular os filtros (todos via select)
        $baseQuery = Empresa::whereIn('empresa_tipo_id', [3, 4]);

        return view('admin.credenciados.index', [
            'crend' => $baseQuery->get(),
            'cnpjs' => (clone $baseQuery)->whereNotNull('cnpj')->distinct()->orderBy('cnpj')->pluck('cnpj'),
            'razoesSociais' => (clone $baseQuery)->whereNotNull('razao_social')->distinct()->orderBy('razao_social')->pluck('razao_social'),
            'municipios' => Municipio::orderBy('nome')->get(),
            'estados' => Estado::orderBy('sigla')->pluck('sigla'),
            'organizacoes' => Organizacao::orderBy('nome')->get(),
            'tipos' => EmpresaTipo::whereIn('id', [3, 4])->orderBy('nome')->get(),
            'statusOptions' => ['Ativo', 'Inativo'],
        ]);
    }


    public function show($id)
    {
        $credenciado = Empresa::with([
            'municipio.estado',
            'organizacao',
            'empresaTipo',
            'taxas',
            'taxas.multitaxas',
            'taxas.taxasEspeciais.cliente',
            'parametroCredenciado',
            'pos.usuarioCadastro', // <-- Adicionado
            'pos.credenciado',
            'unidades' => function($q) {
                $q->with([
                    'municipio.estado',
                    'organizacao',
                    'empresaTipo',
                    'taxas',
                    'taxas.multitaxas',
                    'taxas.taxasEspeciais.cliente',
                    'parametroCredenciado',
                    'pos.usuarioCadastro', // <-- Adicionado
                    'pos.credenciado'
                ]);
            }
        ])->findOrFail($id);
           // dd($credenciado);
        return view('admin.credenciados.show', compact('credenciado'));
    }


    /**
     * ADICIONADO: Retorna a view parcial com as unidades de um credenciado.
     */
    public function getUnidades(Empresa $credenciado)
    {
        try {
            $unidades = $credenciado->unidades()->with('municipio.estado')->get();

            if ($unidades->isEmpty()) {
                \Log::info("Credenciado {$credenciado->id} sem unidades cadastradas.");
            }

            return view('admin.credenciados._unidades_table', compact('unidades'));

        } catch (\Throwable $e) {
            \Log::error("Erro ao carregar unidades do credenciado {$credenciado->id}: " . $e->getMessage());
            return response()->json(['error' => 'Erro ao carregar unidades.'], 500);
        }
    }


    public function updateParametros(Request $request, Empresa $credenciado)
    {
        $request->validate([
            'isencao_irrf' => 'nullable|string',
        ]);

        ParametroCredenciado::updateOrCreate(
            ['empresa_id' => $credenciado->id],
            ['isencao_irrf' => $request->has('isencao_irrf')]
        );

        return back()->with('success', 'Parâmetros do credenciado salvos com sucesso!');
    }

}
