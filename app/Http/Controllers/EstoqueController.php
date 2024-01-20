<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\estoque;
use App\Models\lote;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\import_ativos;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Readers\LaravelExcelReader;

class EstoqueController extends Controller
{
    public function index()
    {
        $estoque = estoque::all();
        $lote = lote::all();
        return view('estoque.index', compact('estoque','lote'));
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
            'numero_serie' => 'required',
        ]);


        $data = $request->all();
        $data['status'] = $data['status'] ?? 'Disponível';
        $data['observacao'] = $data['observacao'] ?? '';
        $data['historico'] = $data['historico'] ?? '';

        // Renomeie 'lote' para 'id_lote'
        $lote = Lote::where('lote', $request['id_lote'])->first();
        $data['id_lote'] = $lote->id;
        //dd($data['id_lote']);

        $estoque = new Estoque;
        $estoque = Estoque::create($data);

        return redirect()->route('estoque.index')->with('success', 'Ativo cadastrado com sucesso.');
    }

    public function processamento(Request $request)
    {
        $request->validate([
             'id_lote' => 'required',
             'arquivo' => 'required',
              ]);

        //dd($request);

        $data = $request->all();
        $lote = Lote::where('lote', $request['id_lote'])->first();
        $data['id_lote'] = $lote->id;


        $import = new import_ativos($lote->id);
        Excel::import($import, request()->file('arquivo'));

        return redirect()->route('estoque.index')->with('success', 'Ativos importado com sucesso.');
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
}
