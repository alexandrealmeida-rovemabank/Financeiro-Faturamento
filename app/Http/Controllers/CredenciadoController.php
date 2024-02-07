<?php

namespace App\Http\Controllers;
use App\Models\Credenciado;
use App\Models\Estoque;
use App\Models\terminal_vinculado;
use Illuminate\Http\Request;
use DataTables;
require_once 'actions.php';

class CredenciadoController extends Controller
{
    // public function index()
    // {
    //     $credenciado = Credenciado::all();
    //     $estoques = Estoque::all();
    //     return view('credenciado.index', compact('credenciado','estoques'));
    // }

    public function index(Request $request)
    {
        $estoques = Estoque::all();
        if ($request->ajax()) {
            $data = Credenciado::latest()->get();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        return button_credenciado($row);
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }

        return view('credenciado.index', compact('estoques'));
    }

    public function store(Request $request)
    {

         $request->validate([
             'cnpj' => 'required|unique:credenciado,cnpj',
             'nome_fantasia' => 'required',
             'razao_social' => 'required',
             'cep' => 'required',
             'endereco' => 'required',
             'bairro' => 'required',
             'numero' => 'required',
             'cidade' => 'required',
             'estado' => 'required',
             'produto' => 'nullable|array',// Certifique-se de que é um array
         ]);
         $data = $request->all();

         $data['cnpj'] = preg_replace('/[^0-9]/', '', $data['cnpj']);

        // // Preencher status com 'Ativo' se não for fornecido no formulário
         $data['status'] = $data['status'] ?? 'Ativo';
         $data['produto'] = json_encode($request->input('produto', []));

         $credenciado = new Credenciado;

         $credenciado = Credenciado::create($data);



        return redirect()->route('credenciado.index')->with('success', 'Credenciado cadastrado com sucesso.');
    }

    // SeuControlador.php

    public function edit($id)
    {
        $credenciado = Credenciado::findOrFail($id);
        $estoques = Estoque::all();
        $terminal = Terminal_Vinculado::all();

        return view('credenciado.edit', compact('credenciado', 'estoques','terminal'));
    }

    // SeuControlador.php

    public function update(Request $request, $id)
    {

        $credenciado = Credenciado::findOrFail($id);


          $request->validate([

              'nome_fantasia' => 'required',
              'razao_social' => 'required',
              'cep' => 'required',
              'endereco' => 'required',
              'bairro' => 'required',
              'numero' => 'required',
              'cidade' => 'required',
              'estado' => 'required',
              'status' => 'required',
             'produto' => 'nullable|array',
          ]);

          $data = $request->all();

          if ($data['status'] == 'Inativo') {
            $terminaisVinculados = Terminal_Vinculado::where('id_credenciado', $id)
                ->where('status', 'Vinculado')
                ->get();

            if ($terminaisVinculados->isNotEmpty()) {
                return redirect()->route('credenciado.edit', $id)->with('error', 'Não é possível inativar o estabelecimento com terminais vinculados!');
            }
        }

        $data['produto'] = json_encode($request->input('produto', []));
        $credenciado->update($data);

        return redirect()->route('credenciado.index')->with('success', 'Cadastro do estabelecimento atualizado om sucesso!!.');
    }

}
