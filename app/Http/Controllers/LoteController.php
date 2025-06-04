<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\lote;

class LoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:visualizar estoque')->only(['index']);
        $this->middleware('permission:criar estoque')->only(['create']);
        $this->middleware('permission:editar estoque')->only(['edit']);
    }

    public function index()
    {
        $lote = lote::all();
        return view('estoque.lote.index', compact('lote'));
    }

    public function create(Request $request)
    {

         $request->validate([
             'lote' => 'required',
             'nf' => 'required',
             'quantidade' => 'required',

         ]);
         $data = $request->all();
         $data['status'] = $data['status'] ?? 'Ativo';
         $data['lote'] = str_pad($data['lote'], 4, '0', STR_PAD_LEFT);

         $lote = lote::create($data);



        return redirect()->route('estoque.lote.index')->with('success', 'Lote cadastrado com sucesso.');
    }


    public function edit($id, Request $request)
    {
        $lote = lote::findOrFail($id);

        $request->validate([
              'lote' => 'required',
              'nf' => 'required',
              'quantidade' => 'required',
              'status' => 'required',

        ]);
        $data = $request->all();

        $data['lote'] = str_pad($data['lote'], 4, '0', STR_PAD_LEFT);
        $lote->update($data);

        return redirect()->route('estoque.lote.index')->with('success', 'Lote Atualizado com sucesso.');
    }

}
