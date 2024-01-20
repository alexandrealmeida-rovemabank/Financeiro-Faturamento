<?php

namespace App\Http\Controllers;
use App\Models\Credenciado;
use App\Models\Estoque;
use App\Models\Terminal_vinculado;
use Illuminate\Http\Request;

class TerminalController extends Controller
{
    public function vincular($credenciado_id, Request $request)
    {
        $credenciado = Credenciado::findOrFail($credenciado_id);
        //dd($request);
        $request->validate([
             'id_estoque' => 'required',
             'id_chip' => 'required',
             'produto' =>'required'
            ]);



        $data = $request->all();

        if ($credenciado->status == "Ativo"){
            $data['id_credenciado'] = $credenciado_id;

            //procurando o id do terminal pelo s/n e mundando o status
            $estoque = Estoque::where('numero_serie', $data['id_estoque'])->first();
            $data['id_estoque'] = $estoque->id;
            $estoque->status = 'Operação';

            if($data['id_chip'] == 'Sem Chip'){
                $data['id_chip'] = $data['chip'] ?? 0;
            }else{
            //procurando o id do chip pelo s/n e mundando o status
            $chip = Estoque::where('numero_serie', $data['id_chip'])->first();
            $data['id_chip'] = $chip->id;
            $chip->status = 'Operação';
            }

            //Atribuindo status de vinculado
            $data['status'] = $data['status'] ?? 'Vinculado';
            //dd($data);
            //salvando informações
            $terminal = new terminal_vinculado;
            $estoque->save($data);
            $chip->save($data);
            $terminal->create($data);

            return redirect()->route('credenciado.edit',$credenciado_id)->with('success', 'Terminal Vinculado!');


        }else{
            return redirect()->route('credenciado.edit',$credenciado_id)->with('error', 'Não foi possivel vincular o terminal pois o Credenciado está inativo!');
        }



    }

    public function desvincular($id, Request $request)
    {
        $terminal_vinculado = terminal_vinculado::findOrFail($id);
        $estoque = Estoque::findOrFail($terminal_vinculado->id_estoque);
        $chip = Estoque::findOrFail($terminal_vinculado->id_chip);
        //dd($request);

        $data = $request->all();
        //alterar o status da vinculação
        $terminal_vinculado->status = 'Desvinculado';

        //alterar o status do chip e terminal
        $estoque->status = 'Disponível';
        $chip->status = 'Disponível';

        //salvando informações

        $estoque->save($data);
        $chip->save($data);
        $terminal_vinculado->save($data);

            return redirect()->route('credenciado.edit',$terminal_vinculado->id_credenciado)->with('success', 'Terminal desvinculado!');




    }
}
