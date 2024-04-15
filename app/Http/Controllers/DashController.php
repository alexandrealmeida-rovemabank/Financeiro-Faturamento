<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Logistica_reversa;
class DashController extends Controller
{

    public function index(){
        $resultados = DB::select('SELECT * FROM QuantidadeRegistrosTabelas');
        $estoquePorStatus = DB::table('quantidadeestoqueporstatus')->get();
        $estoquePorModelo = DB::table('quantidadeestoquepormodelo')->get();
        $estoquePorFabricante = DB::table('quantidadeestoqueporfabricante')->get();
        $estoquePorLote = DB::table('quantidadeestoqueporlote')->get();
        $resultado = $resultados[0];
        $logisticasPorTipo = Logistica_reversa::select('tipo_coleta', DB::raw('COUNT(*) as total'))
        ->groupBy('tipo_coleta')
        ->get();
        $logisticasPorContrato = Logistica_reversa::select('contrato', DB::raw('COUNT(*) as total'))
        ->groupBy('contrato')
        ->get();

        $solicitacoesPorMes = Logistica_reversa::selectRaw('EXTRACT(MONTH FROM TO_DATE(data_solicitacao, \'DD-MM-YYYY\')) AS mes, COUNT(*) AS quantidade')
        ->groupBy('mes')
        ->get();
    ;


        $user = auth()->user();
       //dd($solicitacoesPorMes);

        return view('home', compact('user', 'resultado','estoquePorStatus','estoquePorModelo','estoquePorFabricante','estoquePorLote','logisticasPorTipo','logisticasPorContrato','solicitacoesPorMes'));
    }



}
