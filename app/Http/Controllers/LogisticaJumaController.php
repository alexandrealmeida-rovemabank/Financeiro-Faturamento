<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogisticaJumaController extends Controller
{
    public function index_juma(Request $request)
    {
        // Obtém todos os dados de logística Juma ordenados pela data mais recente
        $data = LogisticaJuma::latest()->get();

        // Verifica se a requisição é AJAX
        if ($request->ajax()) {
            // Retorna os dados formatados para DataTables
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    // Adiciona o botão de ação para cada linha
                    return button_logistica_juma($row);
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // Retorna a view do índice de logística Juma
        return view('logistica.juma.index');
    }
}
