<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SoapClient;
use App\Models\parametros_correios_cartao;
use App\Models\Logistica_reversa;
use App\Models\statusLogisitca;
require_once 'actions.php';
use DataTables;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LogisticaController extends Controller
{


    public function index_correios(Request $request){
        $data = Logistica_reversa::latest()->get();

        // Mapear os valores de tipo_coleta para substituir "CA" por "COLETA DOMICILIAR" e "A" por "AUTORIZAÇÃO DE POSTAGEM"
        $data->transform(function ($item) {
            if ($item->tipo_coleta === 'CA') {
                $item->tipo_coleta = 'COLETA DOMICILIAR';
            }
            if ($item->tipo_coleta === 'A') {
                $item->tipo_coleta = 'AUTORIZAÇÃO DE POSTAGEM';
            }
            return $item;
        });

        if ($request->ajax()) {
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    return button_logistica_correios($row);
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $contrato = $data->pluck('contrato')->unique();
        $solicitacao = $data->pluck('tipo_coleta')->unique();
        $status = $data->pluck('desc_status_objeto')->unique();
        $produto = $data->pluck('produto')->unique();

        return view('logistica.correios.index', compact('contrato', 'solicitacao', 'status', 'produto'));
    }

    public function buscarNumerosCartao($contratoSelecionado){
        $numerosCartao = parametros_correios_cartao::where('cnpj_contrato', $contratoSelecionado)->pluck('num_cartao')->toArray();
        return response()->json($numerosCartao);
    }


    public function solicitarPostagemReversa(Request $request){
        $request->validate([
            'contrato' => 'required',
            'num_cartao' => 'required',
            'servico' => 'required',
            'cnpj_remetente' => 'required',
            'email_remetente' => 'required|email',
            'nome_fantasia_remetente' => 'required',
            'cep_remetente' => 'required',
            'logradouro_remetente' => 'required',
            'numero_remetente' => 'required',
            'bairro_remetente' => 'required',
            'cidade_remetente' => 'required',
            'estado_remetente' => 'required',
            'cnpj_destinatario' => 'required',
            'email_destinatario' => 'required|email',
            'nome_fantasia_destinatario' => 'required',
            'cep_destinatario' => 'required',
            'logradouro_destinatario' => 'required',
            'numero_destinatario' => 'required',
            'bairro_destinatario' => 'required',
            'cidade_destinatario' => 'required',
            'estado_destinatario' => 'required',
            'tipo_coleta' => 'required',
            'valor_declarado' => 'nullable|numeric|min:0.00|max:100000.00',
            'descricao_obj' => 'nullable',
            'produto' => 'required',
            'embalagem' => 'required'
        ]);

        $data = $request->all();


        if ($data['cnpj_remetente'] == $data['cnpj_destinatario']) {
            return redirect()->back()->with('error', 'Os dados do Destinatário não podem ser os mesmos que os do Remetente!')->withInput();
        }

        $logistica = parametros_correios_cartao::where('num_cartao', $data['num_cartao'])->first();

        if ($logistica) {
            if ($logistica->cnpj_contrato == $data['contrato']) {
               $status_validacao = "ok";
            } else {
                return redirect()->back()->with('error', 'O número de cartão informado não pertence ao contrato selecionado!')->withInput();
            }
        } else {
            return redirect()->back()->with('error', 'O número de cartão informado não existe ou não está vinculado a um contrato!')->withInput();
        }
        if($data['tipo_coleta'] == 'CA'){

            $data['ar'] = '0';
        }





        Function solicitar_postagem($dados, $paramentros, $link, $username, $password){
            try {
                $credenciais = [
                    'login' => $username,
                    'password' => $password,
                ];

                $client = new SoapClient($link, $credenciais);



                list($tipo, $codigo, $descricao) = explode('-', $dados['embalagem']);


                if($tipo == ''){
                    $qtd = '';
                }else{
                    $qtd = '1';
                }

                $params = [
                    'codAdministrativo' => $paramentros->cod_administrativo,
                    'codigo_servico' => $dados['servico'],
                    'cartao' => $dados['num_cartao'],
                    'destinatario' => [
                        'nome' => $dados['nome_fantasia_destinatario'],
                        'logradouro' => $dados['logradouro_destinatario'],
                        'numero' => $dados['numero_destinatario'],
                        'complemento' => $dados['complemento_destinatario'],
                        'bairro' => $dados['bairro_destinatario'],
                        'cidade' => $dados['cidade_destinatario'],
                        'referencia' => '', // Este campo está vazio no exemplo
                        'uf' => $dados['estado_destinatario'],
                        'cep' => $dados['cep_destinatario'],
                        'ddd' => $dados['ddd_destinatario'],
                        'telefone' => $dados['celular_destinatario'],
                        'email' => $dados['email_destinatario'],
                        'identificacao' => $dados['cnpj_destinatario'],
                    ],
                    'coletas_solicitadas' => [
                        'tipo' => $dados['tipo_coleta'],
                        'valor_declarado' => $dados['valor_declarado'],
                        'descricao' => $dados['descricao_obj'], // Este campo está vazio no exemplo
                        'ar' => $dados['ar'],

                        'remetente' => [
                            'nome' => $dados['nome_fantasia_remetente'],
                            'logradouro' => $dados['logradouro_remetente'],
                            'numero' => $dados['numero_remetente'],
                            'complemento' => $dados['complemento_remetente'],
                            'bairro' => $dados['bairro_remetente'],
                            'referencia' => '', // Este campo está vazio no exemplo
                            'cidade' => $dados['cidade_remetente'],
                            'uf' => $dados['estado_remetente'],
                            'cep' => $dados['cep_remetente'],
                            'ddd' => $dados['ddd_remetente'],
                            'telefone' => $dados['celular_remetente'],
                            'email' => $dados['email_remetente'],
                            'identificacao' => $dados['cnpj_remetente'],
                            'ddd_celular' => $dados['ddd_remetente'],
                            'celular' => $dados['celular_remetente'],
                            'sms' => 'S',
                        ],
                        'produto' => [
                            [
                                'codigo' => $codigo,
                                'tipo' => $tipo,
                                'qtd' => $qtd,
                            ],

                        ],
                        'numero' => '', // Este campo está vazio no exemplo
                        'ag' => '', // Este campo está vazio no exemplo
                        'cartao' => '', // Este campo está vazio no exemplo
                        'servico_adicional' => '', // Este campo está vazio no exemplo
                        'obj_col' => [
                            [
                                'item' => $dados['qtd_item'],
                                'desc' => $dados['descricao_obj'],

                            ],

                        ],
                    ],
                ];
            // dd($params);

            // return dd($params);

                    $result = $client->solicitarPostagemReversa($params);

                // return dd($result);
                    if($result->solicitarPostagemReversa->cod_erro == '0')
                    {
                        if($result->solicitarPostagemReversa->resultado_solicitacao->codigo_erro == '0'){

                            $postagem_reversa = new Logistica_reversa;
                            $dados['num_coleta'] = $result->solicitarPostagemReversa->resultado_solicitacao->numero_coleta;
                            $dados['num_etiqueta'] = $result->solicitarPostagemReversa->resultado_solicitacao->numero_etiqueta;
                            $dados['status_objeto'] = $result->solicitarPostagemReversa->resultado_solicitacao->status_objeto;
                            $dados['desc_status_objeto'] = 'A COLETAR';
                            $dados['prazo'] = $result->solicitarPostagemReversa->resultado_solicitacao->prazo;
                            $dados['data_solicitacao'] = $result->solicitarPostagemReversa->resultado_solicitacao->data_solicitacao;
                            $dados['hora_solictacao'] = $result->solicitarPostagemReversa->resultado_solicitacao->hora_solicitacao;

                            $postagem_reversa = Logistica_reversa::create($dados);


                            return redirect()->route('logistica.correios.index')->with('success', 'Solicitação Realizada com sucesso!');
                        }
                        else{
                            return redirect()->back()->with('error', $result->solicitarPostagemReversa->resultado_solicitacao->codigo_erro .' - ' . $result->solicitarPostagemReversa->resultado_solicitacao->descricao_erro)->withInput();
                        };
                    }else{
                        return redirect()->back()->with('error', $result->solicitarPostagemReversa->cod_erro .' - ' . $result->solicitarPostagemReversa->msg_erro)->withInput();
                    }

            } catch (\Exception $e) {

                return redirect()->back()->with('error','houve algum erro na requisição: '. $e->getMessage(). 'Tente novamente mais tarde')->withInput();

            }

        }

        if($data['contrato'] == '05884660000104'){
            $user = Config('variaveis.username_solucoes');
            $password = config('variaveis.password_solucoes');
        }else
        {
            $user = Config('variaveis.username_ip');
            $password = config('variaveis.password_ip');
        }

       return solicitar_postagem($data, $logistica, config('variaveis.link'), $user, $password);

      // return $request;

    }


    public function consultarPedido(){

        $client = new SoapClient('https://apps.correios.com.br/logisticaReversaWS/logisticaReversaService/logisticaReversaWS?wsdl', [
            'login' => 'cardideal',  // Substitua 'empresacws' pelo seu login
            'password' => '1yYrHozdNRkcIJFJCc4gZsAmlLsyEquyLrb5X3NL',  // Substitua '123456' pela sua senha
        ]);


        // Faça a chamada para o método acompanharPedido
        try {
            // Chame o método acompanharPedido
            $result = $client->solicitarPostagemReversa($params);

            // Processamento adicional dos dados, se necessário...

            // Retorne os dados para a view
            return ['resultado' => $result];
        } catch (\Exception $e) {
            // Trate a exceção aqui (por exemplo, registre-a, exiba uma mensagem de erro, etc.)
            return  ['mensagem' => $e->getMessage()];
        }
    }


    public function cancelarPedido($id){
        $contrato = Logistica_reversa::findOrFail($id);

        if($contrato->contrato == '05884660000104'){
            $user = Config('variaveis.username_solucoes');
            $password = config('variaveis.password_solucoes');
            $cod_adm = config('variaveis.cod_adm_solucoes');
        }else
        {
            $user = Config('variaveis.username_ip');
            $password = config('variaveis.password_ip');
            $cod_adm = config('variaveis.cod_adm_ip');
        }

        $credenciais = [
            'login' => $user,
            'password' => $password,
        ];

        $client = new SoapClient(config('variaveis.link'), $credenciais);


        if( $contrato->tipo_coleta == 'CA'){
            $tipo = 'C';
        }else $tipo = 'A';

        $params = [
            'codAdministrativo' => $cod_adm,
            'numeroPedido' => $contrato->num_coleta,
            'tipo' => $tipo,
        ];

        //return $params;



        // Faça a chamada para o método acompanharPedido
         try {
             // Chame o método acompanharPedido
             $result = $client->cancelarPedido($params);
             //return $result;
             if(property_exists($result->cancelarPedido,'cod_erro')){

                return redirect()->back()->with('error', $result->cancelarPedido->cod_erro .' - ' . $result->cancelarPedido->msg_erro)->withInput();

             }else{

                $desc_status_objeto['desc_status_objeto']= strtoupper($result->cancelarPedido->objeto_postal->status_pedido);
                $desc_status_objeto['status_objeto'] = '09';
                $contrato->update($desc_status_objeto);

                return redirect()->route('logistica.correios.index')->with('success','Pedido: '. $result->cancelarPedido->objeto_postal->numero_pedido . " cancelado!".
                " Motivo: ". $result->cancelarPedido->objeto_postal->status_pedido );


             }

         } catch (\Exception $e) {
             // Trate a exceção aqui (por exemplo, registre-a, exiba uma mensagem de erro, etc.)
             return redirect()->back()->with('error','Houve algum erro na requisição: '. $e->getMessage())->withInput();
            }
    }


    public function view($id){

        $solicitacao = Logistica_reversa::findOrFail($id);

        return view('logistica.correios.visualizar', compact('solicitacao'));
    }

    public function acompanharPedido(){
        $logFile = storage_path('logs/atualizacao_logistica.log');
        $pedidosPendentes = Logistica_reversa::whereIn('status_objeto', ['0','00','01','1', '03','3', '04','4', '05','5', '06','6','55'])->get();

        if ($pedidosPendentes->isEmpty()) {
            $this->log("Sem logística no banco", $logFile);
        } else {
            $pedidos = []; // Inicialize o array de pedidos aqui
            foreach ($pedidosPendentes as $pedido) {
                $pedidos[] = $pedido->num_coleta; // Adicione o número de coleta ao array
            }
            $this->log("Pedidos a serem atualizados: " . implode(", ", $pedidos), $logFile);
        }

            foreach ($pedidosPendentes as $pedido) {
                // Executar acompanharPedido
                $result = $this->acompanharPedido2($pedido, $logFile);

                // Salvar os dados na tabela status_logistica
                $this->salvarStatusLogistica($result, $logFile);

                // Atualizar registros na tabela Logistica_reversa
                $this->atualizarStatusLogisticaReversa($pedido, $result, $logFile);
            }

            $this->log('Atualização concluída com sucesso!', $logFile);


        return redirect()->route('logistica.correios.index')->with('success','Status de Solicitações atualizados manualmente!' );
    }



    public function acompanharPedido2($pedidos,$logFile){
        if($pedidos->contrato == '05884660000104'){
            $user = Config('variaveis.username_solucoes');
            $password = config('variaveis.password_solucoes');
            $cod_adm = config('variaveis.cod_adm_solucoes');
        }else
        {
            $user = Config('variaveis.username_ip');
            $password = config('variaveis.password_ip');
            $cod_adm = config('variaveis.cod_adm_ip');
        }

        $credenciais = [
            'login' => $user,
            'password' => $password,
        ];

        $client = new SoapClient(config('variaveis.link'), $credenciais);

        if( $pedidos->tipo_coleta == 'CA'){
            $tipo = 'C';
        }else $tipo = 'A';

        $params = [
            'codAdministrativo' => $cod_adm,
            'tipoBusca' => 'H',
            'numeroPedido' => $pedidos->num_coleta,
            'tipoSolicitacao' => $tipo,
        ];


        try {
            $result = $client->acompanharPedido($params);
            return $result;
        } catch (\Exception $e) {
            $this->log($e->getMessage(), $logFile);
            return null;
        }
    }


    public function salvarStatusLogistica($result,$logFile)
    {
            $coletas = $result->acompanharPedido->coleta;

            // Verifica se $coletas é um array ou um objeto
            if (is_array($coletas)) {
                // Se for um array, iteramos sobre cada elemento
                foreach ($coletas as $coleta) {
                    $this->salvarStatusIndividual($coleta, $logFile);
                }
            } elseif (is_object($coletas)) {
                // Se for um objeto, chamamos a função salvarStatusIndividual diretamente
                $this->salvarStatusIndividual($coletas, $logFile);
            } else {
                $this->log("O valor de coleta não é nem um array nem um objeto.", $logFile);
            }
        }

    public function salvarStatusIndividual($coleta,$logFile)
    {
            try {
                // Verifica se o status já existe na tabela status_logistica
                $statusExistente = statusLogisitca::where('numero_pedido', $coleta->numero_pedido)
                    ->where('status',  $coleta->historico->status)
                    ->first();

                // Se o status não existir na tabela status_logistica, salva-o
                if (!$statusExistente) {
                    $status = new statusLogisitca();
                    $status->numero_pedido =    $coleta->numero_pedido;
                    $status->status =           $coleta->historico->status;
                    $status->descricao_status = $coleta->historico->descricao_status;
                    $status->data_atualizacao = $coleta->historico->data_atualizacao;
                    $status->hora_atualizacao = $coleta->historico->hora_atualizacao;
                    $status->observacao =       $coleta->historico->observacao;
                    $status->save();
                    $this->log("Status salvo com sucesso.", $logFile);
                }
            } catch (\Exception $e) {
                $this->log("Erro ao salvar status. {$e->getMessage()}", $logFile);
            }
        }


    public function atualizarStatusLogisticaReversa($pedido, $result,$logFile){
        if (is_array($result->acompanharPedido->coleta)) {
            $quantidadeIntensColeta = count($result->acompanharPedido->coleta) - 1;
            $atl['status_objeto'] = $result->acompanharPedido->coleta[$quantidadeIntensColeta]->objeto->ultimo_status;
            $atl['desc_status_objeto'] = $result->acompanharPedido->coleta[$quantidadeIntensColeta]->objeto->descricao_status;
            $etiqueta = $result->acompanharPedido->coleta[$quantidadeIntensColeta]->objeto->numero_etiqueta;
        }else{
            $atl['status_objeto'] = $result->acompanharPedido->coleta->objeto->ultimo_status;
            $atl['desc_status_objeto'] = $result->acompanharPedido->coleta->objeto->descricao_status;
            $etiqueta = $result->acompanharPedido->coleta->objeto->numero_etiqueta;
        }

        if($etiqueta){
        $atl['num_etiqueta'] = $etiqueta;

        }else{
            $atl['num_etiqueta'] = $pedido->num_etiqueta;
        }

        $pedido->update($atl);

        if ($pedido) {
            $this->log("Atualização do pedido {$pedido->num_coleta} concluída com sucesso.", $logFile);
        } else {
            $this->log("Erro ao atualizar o pedido {$pedido->num_coleta}.", $logFile);
        }


    }

    public function log($message, $logFile)
{
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $message . "\n", FILE_APPEND);
}
    public function rastrear(Request $request){

        $request->validate([
            'cod_rastreio' => 'required',

        ]);
        $data = $request->all();

        $result = $this->gerarToken(Config('variaveis.username_solucoes'), config('variaveis.password_solucoes'));
         //return $result;
        try {
            // Token de acesso
            $token = $result;
            // Endpoint da API
            $url = 'https://api.correios.com.br/srorastro/v1/objetos?codigosObjetos='.$data['cod_rastreio'].'&resultado=T';



            // Faz a requisição GET com o token de autenticação no cabeçalho
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url);

          //  return $response;
            // Verifica se a requisição foi bem-sucedida
            if ($response->successful()) {
                // Retorna os dados da resposta da API
                return $response->json();
                return view('logistica.correios.rastreio', ['result' => $result]);
            } else {
                // Se houver erro na requisição, retorna uma resposta com o status de erro
                return response()->json(['error' => 'Erro ao fazer a requisição'], $response->status());
            }
        } catch (\Exception $e) {
            // Captura qualquer exceção que ocorra durante a requisição
            return response()->json(['error' => $e->getMessage()], 500); // Retorna um erro interno do servidor (status 500)
        }

    }

    public function gerarToken($username, $password)
    {
        try {
            // Codifica os dados de autenticação no formato esperado para Authorization: Basic
            $authString = base64_encode("$username:$password");
            $cartao = "0077498895";
            // Endpoint da API para obter o token de acesso
            $url = 'https://api.correios.com.br/token/v1/autentica/cartaopostagem';


            // Faz uma requisição POST para obter o token de acesso
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $authString,
            ])->post($url, $cartao);


          // return $response;

            // Verifica se a requisição foi bem-sucedida
            if ($response->successful()) {
                // Retorna o token de acesso
                return $response->json('token');
            } else {
                // Se houver erro na requisição, retorna uma resposta com o status de erro
                return response()->json(['error' => 'Falha ao gerar token de acesso'], $response->status());
            }
        } catch (\Exception $e) {
            // Captura qualquer exceção que ocorra durante a requisição
            return response()->json(['error' => $e->getMessage()], 500); // Retorna um erro interno do servidor (status 500)
        }
    }

}
