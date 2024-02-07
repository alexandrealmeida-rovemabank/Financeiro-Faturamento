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
use DataTables;
require_once 'actions.php';

class AbastecimentoImpressaoController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = lote_impressao::latest()->get();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                        return button_lote_cartoes($row);
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }

        return view('abastecimento.impressao.index');
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

        return redirect()->route('abastecimento.impressao.index')->with('success', 'Lote Criado e cartões importados com sucesso!.');
    }

    public function Editar_lote(Request $request, $id)
    {
        $lote = lote_impressao::findOrFail($id);
        $impressoes = $lote->impressoes;
        $status = $lote->status_impressao;
        if ($request->ajax()) {
            return Datatables::of($impressoes)
                    ->addIndexColumn()
                    ->addColumn('action', function($row) use ($status) {
                        return button_lote_cartoes_impressao_editar($row, $status);
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }

        return view('abastecimento.impressao.editar', compact('lote'));
    }



    public function edit_cartao($id, Request $request){

        $impressao = impressao::findOrFail($id);

         $data = $request->all();
         $impressao->update($data);

         $lote_impressao = lote_impressao::findOrFail($data['idlote']);
         $lote_impressao->updated_at = now();
         $lote_impressao->save();


       //return dd($data);
         return redirect()->route('abastecimento.impressao.edit', $data['idlote'])->with('success','Dados alterado com sucesso!');


    }

    public function edit_status($id){

        $lote_impressao = lote_impressao::findOrFail($id);

        //return dd($id);

           $lote_impressao->status_impressao = 'Impresso';

           $lote_impressao->save();

           return redirect()->route('abastecimento.impressao.index')->with('success','Status alterado com sucesso!');

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
        return redirect()->route('abastecimento.impressao.index')->with('success','Lote Excluído com sucesso!');
    } catch (\Exception $e) {
        // Rollback da transação em caso de erro
        \DB::rollback();


    }
}

}
