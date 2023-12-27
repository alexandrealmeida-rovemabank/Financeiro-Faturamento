<?php


namespace App\Http\Controllers;
use App\Models\AbastecimentoImpressao;
use App\Models\lote_impressao;
use App\Models\impressao;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\YourImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Readers\LaravelExcelReader;

class AbastecimentoImpressaoController extends Controller
{

    public function index()
    {
        $lote_impressao = lote_impressao::all();
        return view('abastecimento.impressao.index', compact('lote_impressao'));
    }

    public function importar()
    {
        //  $impressao = AbastecimentoImpressao::all();
        return view('abastecimento.impressao.import');
    }



    public function processamento(Request $request)
    {
        $request->validate([
             'lote' => 'required',
             'cliente' => 'required',
             'arquivo' => 'required',
              ]);

        $data = $request->all();

        $data['status_impressao'] = $data['status_impressao'] ?? 'Importado';
        $data['data_importacao'] = $data['data_importacao'] ?? now();
        $data['data_alteracao'] = $data['data_alteracao'] ?? now();

        $lote_impressao = new lote_impressao;
        $lote_impressao = lote_impressao::create($data);

        // Importar os dados usando YourImport
        $import = new YourImport($lote_impressao->id);
        Excel::import($import, request()->file('arquivo'));

        return redirect()->route('abastecimento.impressao.index')->with('success', 'Credenciado cadastrado com sucesso.');
    }

    public function Editar_lote($id)
    {
        $lote = lote_impressao::findOrFail($id);
        $impressoes = $lote->impressoes;

        return view('abastecimento.impressao.editar', compact('lote', 'impressoes'));
    }

    public function edit_cartao($id, Request $request){

        $impressao = impressao::findOrFail($id);


         $data = $request->all();
         $impressao->update($data);

         $lote_impressao = lote_impressao::findOrFail($data['idlote']);
         $lote_impressao->updated_at = now();
         $lote_impressao->save();


       //return dd($data);
         return redirect()->route('.abastecimento.impressao.edit', $data['idlote'])->withSuccess('Dados alterado com sucesso!');


    }

    public function edit_status($id){

        $lote_impressao = lote_impressao::findOrFail($id);

        //return dd($id);

           $lote_impressao->status_impressao = 'Impresso';

           $lote_impressao->save();

           return redirect()->route('abastecimento.impressao.index')->withSuccess('Status alterado com sucesso!');

    }

    public function excluir_lote($id)
{
    // Localize o lote pelo ID
    $lote = Lote_impressao::findOrFail($id);

    // Use o relacionamento para obter todas as impressões associadas ao lote
    $impressoes = $lote->impressoes;

    // Inicie uma transação para garantir consistência no banco de dados
    \DB::beginTransaction();

    try {
        // Exclua todas as impressões associadas ao lote
        foreach ($impressoes as $impressao) {
            $impressao->delete();
        }

        // Exclua o lote
        $lote->delete();

        // Commit da transação
        \DB::commit();

        return response()->json(['message' => 'Lote excluídos com sucesso']);
    } catch (\Exception $e) {
        // Rollback da transação em caso de erro
        \DB::rollback();

        return redirect()->route('abastecimento.impressao.index')->withSuccess('Lote Excluído com sucesso!');
    }
}

}
