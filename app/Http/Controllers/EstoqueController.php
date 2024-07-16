<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\estoque;
use App\Models\credenciado;
use App\Models\lote;
use App\Models\historico_terminal;
use App\Models\terminal_vinculado;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\import_ativos;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use DataTables;
use Throwable;
require_once 'actions.php';

class EstoqueController extends Controller
{

    public function index(Request $request)
    {
        $lote = lote::all();
        $historico = historico_terminal::all();
        $terminal_vinculado =  terminal_vinculado::all();
        $sistemaDistinto =  terminal_vinculado::distinct()->pluck('sistema');
        // Para a coluna "modelo"
        $modelosDistintos = estoque::distinct()->pluck('modelo');
        // Para a coluna "lote"
        $lotesDistintos = estoque::distinct()->pluck('id_lote');
        // Para a coluna "categoria"
        $categoriasDistintas = estoque::distinct()->pluck('categoria');
        // Para a coluna "fabricante"
        $fabricantesDistintos = estoque::distinct()->pluck('fabricante');
        // Para a coluna "status"
        $statusDistintos = estoque::distinct()->pluck('status');

        if ($request->ajax()) {
            $data = Estoque::with('lote')
                ->leftJoin('terminal_vinculado', function($join) {
                    $join->on('estoque.id', '=', 'terminal_vinculado.id_estoque')
                         ->where('terminal_vinculado.status', 'Vinculado');
                })
                ->select('estoque.*', 'terminal_vinculado.sistema')
                ->get();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        return button_estoque($row);
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }

        return view('estoque.index', compact('sistemaDistinto','historico','modelosDistintos','lotesDistintos','categoriasDistintas','fabricantesDistintos','statusDistintos','lote'));
    }

     public function import()
     {
        $lote = lote::all();
        return view('estoque.import', compact('lote'));
     }


    public function create(Request $request)
    {
        $request->validate([
            'id_lote' => 'required',
            'categoria' => 'required',
            'fabricante' => 'required',
            'modelo' => 'required',
            'numero_serie' => 'required|unique:estoque,numero_serie',
        ],[
            'id_lote.required' => 'O campo ID do lote é obrigatório.',
            'categoria.required' => 'O campo categoria é obrigatório.',
            'fabricante.required' => 'O campo fabricante é obrigatório.',
            'modelo.required' => 'O campo modelo é obrigatório.',
            'numero_serie.required' => 'O campo número de série é obrigatório.',
            'numero_serie.unique' => 'O numero de série informado já está cadastrado.',
        ]);

        $data = $request->all();
        $data['status'] = $data['status'] ?? 'Disponível';
        $data['observacao'] = $data['observacao'] ?? '';
        $data['metodo_cadastro'] = $data['metodo_cadstro'] ?? 'Manual';

        // Renomeie 'lote' para 'id_lote'
        $lote = Lote::where('lote', $request['id_lote'])->first();
        $data['id_lote'] = $lote->id;
        //dd($data);
        //$data['id'] = 632;
        $estoque = new Estoque;
        //dd($estoque);
        $estoque = Estoque::create($data);

        return redirect()->route('estoque.index')->with('success', 'Ativo cadastrado com sucesso.');
    }

    public function processamento(Request $request)
    {
            $request->validate([
                'id_lote' => 'required',
                'arquivo' => 'required',
            ], [
                'id_lote.required' => 'O campo lote é obrigatório.',
                'arquivo.required' => 'É necessário selecionar um arquivo.',
            ]);
            try {

            $data = $request->all();
            $lote = Lote::where('lote', $request['id_lote'])->first();

            if (!$lote) {
                throw new \Exception('Lote não encontrado.');
            }

            $data['id_lote'] = $lote->id;

            $import = new import_ativos($lote->id);
            Excel::import($import, request()->file('arquivo'));

            return redirect()->route('estoque.index')->with('success', 'Ativos importados com sucesso.');
        } catch (Throwable $e) {
            // Log do erro
            \Log::error($e);

            return redirect()->back()->with('error', 'Ocorreu um erro durante a importação. Por favor, verifique seu arquivo e tente novamente. ' . $e->getMessage());
        }
    }

    public function edit($id, Request $request)
    {
        //dd($request);
        $estoque = estoque::findOrFail($id);
        $lote = Lote::where('lote', $request['id_lote'])->first();

        $data = $request->all();
        $data['id_lote'] = $lote->id;

        if($estoque->status == "Operação"){
            return redirect()->route('estoque.index')->with('error','Não é possivel editar um ativo com status de operação! Desvincule no cadastro do estabelecimento.');
        } else
        {
            if (empty($data['edit-observacao'])){
                $data['observacao'] = $data['observacao'] ?? '';
            }
            else
            $data['observacao'] = $data['observacao'];
            //dd($data);
            $estoque->update($data);

            return redirect()->route('estoque.index')->with('success', 'Ativo Atualizado com sucesso.');
        }
    }

    public function excluir($id)
    {
        // Localize o lote pelo ID
        $estoque = estoque::findOrFail($id);

        if($estoque->status === "Operação"){
            return redirect()->route('estoque.index')->with('error','Ativo não pode ser excluído pois está em operação!');
        } else
        {
            $estoque->delete();
            return redirect()->route('estoque.index')->with('success','Ativo Excluído com sucesso!');
        }

    }

    public function getHistorico(Request $request)
    {
        $id = $request->get('id'); // Supondo que você definiu um ID aqui para teste
        $historico = historico_terminal::where('id_estoque', $id)->get();

        // Array para armazenar os objetos de credenciado correspondentes a cada registro histórico
        $credenciados = [];

        // Iterar sobre cada registro histórico
        foreach ($historico as $registro) {
            // Obter o ID do credenciado para este registro histórico
            $id_credenciado = $registro->id_credenciado;

            // Buscar o objeto de credenciado correspondente ao ID
            $credenciado = credenciado::findOrFail($id_credenciado);

            // Adicionar o objeto de credenciado ao array
            $credenciados[] = $credenciado;
        }


        // Retornar os registros históricos como uma resposta JSON
        return response()->json($historico);
        //dd($historico);
    }

    public function getHistoricocredenciado(Request $request)
    {
        $id = $request->get('id'); // Supondo que você definiu um ID aqui para teste
        $credenciado = Credenciado::findOrFail($id);

        // Array para armazenar os objetos de credenciado correspondentes a cada registro histórico
        return response()->json(['nome_fantasia' => $credenciado->nome_fantasia]);

    }


}
