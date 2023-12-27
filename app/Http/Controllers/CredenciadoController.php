<?php

namespace App\Http\Controllers;
use App\Models\Credenciado;
use Illuminate\Http\Request;

class CredenciadoController extends Controller
{
    public function index()
    {
        $credenciado = Credenciado::all();
        return view('credenciado.index', compact('credenciado'));
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

        return view('credenciado.edit', compact('credenciado'));
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
             'produto' => 'nullable|array',// Certifique-se de que é um array
          ]);



         $data = $request->all();

         $data['produto'] = json_encode($request->input('produto', []));



         $credenciado->update($data);
         


         return redirect()->route('credenciado.index')->withSuccess('Credenciado cadastrado com sucesso.');
         return 'atualizado';

    }

}
