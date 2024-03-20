<?php

namespace App\Http\Controllers;
use App\Models\Credenciado;
use App\Models\Estoque;
use App\Models\terminal_vinculado;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $crend = Credenciado::all();
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

        return view('credenciado.index', compact('estoques','crend'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cnpj' => 'required|cnpj_unique',
            'nome_fantasia' => 'required',
            'razao_social' => 'required',
            'cep' => 'required',
            'endereco' => 'required',
            'bairro' => 'required',
            'numero' => 'required',
            'cidade' => 'required',
            'estado' => 'required',
            'produto' => 'nullable|array',
        ], [
            'cnpj.required' => 'O CNPJ é obrigatório.',
            'cnpj_unique' => 'Este CNPJ já está cadastrado.',
            'nome_fantasia.required' => 'O nome fantasia é obrigatório.',
            'razao_social.required' => 'A razão social é obrigatória.',
            'cep.required' => 'O CEP é obrigatório.',
            'endereco.required' => 'O endereço é obrigatório.',
            'bairro.required' => 'O bairro é obrigatório.',
            'numero.required' => 'O número é obrigatório.',
            'cidade.required' => 'A cidade é obrigatória.',
            'estado.required' => 'O estado é obrigatório.',
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
    public function view($id)
    {
        $credenciado = Credenciado::findOrFail($id);
        $estoques = Estoque::all();
        $terminal = Terminal_Vinculado::all();

        return view('credenciado.view', compact('credenciado', 'estoques','terminal'));
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
          ],
          [
            'nome_fantasia.required' => 'O nome fantasia é obrigatório.',
            'razao_social.required' => 'A razão social é obrigatória.',
            'cep.required' => 'O CEP é obrigatório.',
            'endereco.required' => 'O endereço é obrigatório.',
            'bairro.required' => 'O bairro é obrigatório.',
            'numero.required' => 'O número é obrigatório.',
            'cidade.required' => 'A cidade é obrigatória.',
            'estado.required' => 'O estado é obrigatório.',
            'status.required' => 'O status do cliente é obrigatorio.',
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




    // public function gerarPDF($id)
    // {
    //     $credenciado = Credenciado::findOrFail($id);
    //     $estoques = Estoque::all();
    //     $terminal = Terminal_Vinculado::all();

    //     // Renderizar a view do PDF como uma string
    //     $html = View::make('credenciado.pdf', compact('credenciado', 'terminal', 'estoques'))->render();

    //     // Criar uma instância do Dompdf
    //     $dompdf = new Dompdf(["enable_remote" => true]);

    //     // Carregar o HTML no Dompdf
    //     $dompdf->loadHtml($html);

    //     // Definindo o tamanho do papel e a orientação
    //     $dompdf->setPaper('A4');

    //     // Renderizando o PDF
    //     $dompdf->render();

    //     // Retornando o PDF como uma resposta HTTP
    //     return $dompdf->stream('credenciado.pdf', ["Attachment" => false]);
    // }


    public function gerarPDF($id)
    {
        $credenciado = Credenciado::findOrFail($id);
        $estoques = Estoque::all();
        $terminal = Terminal_Vinculado::all();


        // Renderizar a view do PDF como uma string
        $html = View::make('credenciado.pdf', compact('id','credenciado', 'terminal', 'estoques'))->render();

        // Criar uma instância do Snappy
        //$pdf = PDF::loadView('credenciado.pdf', compact('credenciado', 'terminal', 'estoques'));
        return $html;


    }
    public function gerarPDF2($id)
    {
        $pdf = PDF::loadView('credenciado.pdf', compact('id'));

        return $pdf->download('listar_contas.pdf');
    }

    public function buscarcnpj($cnpj)

{



    // Aqui você pode usar a biblioteca GuzzleHttp para fazer a requisição para a API
    $client = new \GuzzleHttp\Client();
    $response = $client->request('GET', 'https://brasilapi.com.br/api/cnpj/v1/' . $cnpj);

    // Verifique se a requisição foi bem sucedida
    if ($response->getStatusCode() == 200) {
        // Retorne os dados em formato JSON
        return response()->json(json_decode($response->getBody()->getContents()));
    } else {
        // Retorne um erro se a requisição falhar
        return response()->json(['error' => 'Não foi possível buscar os dados do CNPJ.'], $response->getStatusCode());

    }
}



}
