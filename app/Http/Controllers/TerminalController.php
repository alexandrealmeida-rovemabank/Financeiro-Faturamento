<?php

namespace App\Http\Controllers;
use App\Models\Credenciado;
use App\Models\Estoque;
use App\Models\Terminal_vinculado;
use App\Models\historico_terminal;
use App\Models\user;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TerminalController extends Controller
{
    public function vincular($credenciado_id, Request $request)
    {
        $credenciado = Credenciado::findOrFail($credenciado_id);
        //dd($request);
        $request->validate([
             'id_estoque' => 'required',
             'chip' => 'required',
             'produto' =>'required',
             'sistema' =>'required'
            ]);


            $data = $request->all();
            //dd($data);

        if ($credenciado->status == "Ativo"){
            $data['id_credenciado'] = $credenciado_id;

            function salvarHistorico($id_credenciado, $id_estoque, $produto, $acao, $usuario, $sistema)
            {
                $data = [
                    'id_credenciado' => $id_credenciado,
                    'id_estoque' => $id_estoque,
                    'produto' => $produto,
                    'acao' => $acao,
                    'data' => now(),
                    'usuario' => $usuario,
                    'sistema' => $sistema,
                ];

                historico_terminal::create($data);
            }
            //procurando o id do terminal pelo s/n e mundando o status
            $estoque = Estoque::where('id', $data['id_estoque'])->first();
            //dd($estoque);
            // $data['id_estoque'] = $estoque->id;
            $estoque->status = 'Operação';

            if($data['chip'] == 'Sem Chip'){
                $data['chip'] = $data['chip'] ?? "Sem Chip";
            }else{
            //procurando o id do chip pelo s/n e mundando o status
            $chip = Estoque::where('id', $data['chip'])->first();
            $chip->status = 'Operação';
            $chip->save($data);
            //dd($da);
            salvarHistorico($credenciado_id,$chip->id, $data['produto'], 'Vinculado', auth()->user()->name ?? 'Usuário Desconhecido',$data['sistema']);
            }

            //Atribuindo status de vinculado
            $data['status'] = $data['status'] ?? 'Vinculado';
            //dd($data);
            //salvando informações

            $data['id_credenciado'] = $credenciado_id;
            $data['acao'] = 'Vinculado';
            $data['data'] = now();
            $user = auth()->user();
            $data['usuario'] = $user->name ?? 'Usuário Desconhecido';

            //dd($data['produto']);
            $terminal = new terminal_vinculado;
            $historico = new historico_terminal;
            
            $historico->create($data);
            $estoque->save($data);
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

        // Função para criar e salvar um registro de histórico
        function salvarHistorico($terminal_vinculado, $acao, $usuario)
        {
            $data = [
                'id_credenciado' => $terminal_vinculado->id_credenciado,
                'id_estoque' => $terminal_vinculado->id_estoque,
                'produto' => $terminal_vinculado->produto,
                'acao' => $acao,
                'data' => now(),
                'usuario' => $usuario,
            ];
           // dd($data);
            historico_terminal::create($data);
        }
        function salvarHistorico_chip($terminal_vinculado,$chip, $acao, $usuario)
        {
            $data = [
                'id_credenciado' => $terminal_vinculado->id_credenciado,
                'id_estoque' => $chip,
                'produto' => $terminal_vinculado->produto,
                'acao' => $acao,
                'data' => now(),
                'usuario' => $usuario,
            ];
           //dd($data);
            historico_terminal::create($data);
        }

        // Verifica se o terminal possui um chip
        if ($terminal_vinculado->chip != "Sem Chip") {
            $chip = Estoque::where('numero_serie', $terminal_vinculado->chip)->first();
            $chip->status = 'Disponível';
            $chip->save();
            salvarHistorico_chip($terminal_vinculado, $chip->id, 'Desvinculado', auth()->user()->name ?? 'Usuário Desconhecido');
        }

        // Alterar o status da vinculação e do estoque
        $terminal_vinculado->status = 'Desvinculado';
        $estoque->status = 'Disponível';

        // Salvar histórico para o terminal desvinculado
        salvarHistorico($terminal_vinculado, 'Desvinculado', auth()->user()->name ?? 'Usuário Desconhecido');

        // // Salvar histórico para o estoque desvinculado
        // salvarHistorico($estoque->id_credenciado, $estoque->produto, 'Desvinculado', auth()->user()->name ?? 'Usuário Desconhecido');

        // Salvar alterações
        $terminal_vinculado->save();
        $estoque->save();

        return redirect()->route('credenciado.edit', $terminal_vinculado->id_credenciado)->with('success', 'Terminal desvinculado!');
    }

}
