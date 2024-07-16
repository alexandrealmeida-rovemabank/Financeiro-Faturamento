<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use SoapClient;
use App\Models\parametros_correios_cartao;
use App\Models\Logistica_reversa;
use App\Models\logisticaJuma;
use App\Models\statusLogistica;
use App\Models\abrangencia_correios_lr;
require_once 'actions.php';
use DataTables;
use Illuminate\Support\Facades\Http;
use App\Jobs\AcompanharPedidoJob;
use App\Services\LogisticaService;

class LogisticaController extends Controller
{
    protected $logisticaService;

    public function __construct(LogisticaService $logisticaService){
        $this->logisticaService = $logisticaService;
    }
      public function acompanharPedido(){
          AcompanharPedidoJob::dispatch(new LogisticaService());
          return redirect()->route('logistica.correios.index')->with('success', 'Status de solicitações atualizados manualmente!');
      }

    public function index_correios(Request $request)
    {
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
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
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

    public function buscarNumerosCartao($contratoSelecionado)
    {
        $numerosCartao = parametros_correios_cartao::where('cnpj_contrato', $contratoSelecionado)->pluck('num_cartao')->toArray();
        return response()->json($numerosCartao);
    }

    public function Verificar_Coleta($cep, $cod_servico)
    {
        try {
            // Remover traços do CEP para facilitar a comparação
            $cep = str_replace('-', '', $cep);
            if ($cod_servico = '03247') {
                $servico = "SEDEX Reverso";
            } elseif ($cod_servico = '03301') {
                $servico = "PAC Reverso";
            }

            // Pegar todas as unidades de coleta com o serviço especificado
            $unidades = abrangencia_correios_lr::where('servico', $servico)->get();

            foreach ($unidades as $unidade) {
                // Remover traços dos CEPs inicial e final
                $cep_inicial = str_replace('-', '', $unidade->cep_inicial);
                $cep_final = str_replace('-', '', $unidade->cep_final);

                // Verificar se o CEP está no intervalo
                if ($cep >= $cep_inicial && $cep <= $cep_final) {
                    return response()->json(['coletaDisponivel' => true]);
                }
            }

            return response()->json(['coletaDisponivel' => false]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function solicitarPostagemReversa(Request $request){
    // Validação dos dados do request
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

        // Verificação se os dados do remetente e destinatário são iguais
        if ($data['cnpj_remetente'] == $data['cnpj_destinatario']) {
            return redirect()->back()->with('error', 'Os dados do Destinatário não podem ser os mesmos que os do Remetente!')->withInput();
        }

        // Validação do número do cartão
        $logistica = parametros_correios_cartao::where('num_cartao', $data['num_cartao'])->first();
        if (!$logistica) {
            return redirect()->back()->with('error', 'O número de cartão informado não existe ou não está vinculado a um contrato!')->withInput();
        }

        if ($logistica->cnpj_contrato != $data['contrato']) {
            return redirect()->back()->with('error', 'O número de cartão informado não pertence ao contrato selecionado!')->withInput();
        }

        // Ajuste do tipo de coleta
        if ($data['tipo_coleta'] == 'CA') {
            $data['ar'] = '0';
        }

        // Definição das credenciais de acordo com o contrato
        if ($data['contrato'] == '05884660000104') {
            $user = config('variaveis.username_solucoes');
            $password = config('variaveis.password_solucoes');
        } else {
            $user = config('variaveis.username_ip');
            $password = config('variaveis.password_ip');
        }

        // Chamada à função de solicitação de postagem
        return $this->solicitarPostagem($data, $logistica, config('variaveis.link'), $user, $password);
    }

    private function solicitarPostagem($dados, $paramentros, $link, $username, $password){
        try {
            $credenciais = [
                'login' => $username,
                'password' => $password,
            ];

            $client = new SoapClient($link, $credenciais);

            list($tipo, $codigo, $descricao) = explode('-', $dados['embalagem']);

            $qtd = $tipo ? '1' : '';

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
                    'referencia' => '',
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
                    'descricao' => $dados['descricao_obj'],
                    'ar' => $dados['ar'],
                    'remetente' => [
                        'nome' => $dados['nome_fantasia_remetente'],
                        'logradouro' => $dados['logradouro_remetente'],
                        'numero' => $dados['numero_remetente'],
                        'complemento' => $dados['complemento_remetente'],
                        'bairro' => $dados['bairro_remetente'],
                        'referencia' => '',
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
                    'numero' => '',
                    'ag' => '',
                    'cartao' => '',
                    'servico_adicional' => '',
                    'obj_col' => [
                        [
                            'item' => $dados['qtd_item'],
                            'desc' => $dados['descricao_obj'],
                        ],
                    ],
                ],
            ];

            $result = $client->solicitarPostagemReversa($params);

            if ($result->solicitarPostagemReversa->cod_erro == '0') {
                if ($result->solicitarPostagemReversa->resultado_solicitacao->codigo_erro == '0') {
                    $dados['num_coleta'] = $result->solicitarPostagemReversa->resultado_solicitacao->numero_coleta;
                    $dados['num_etiqueta'] = $result->solicitarPostagemReversa->resultado_solicitacao->numero_etiqueta;
                    $dados['status_objeto'] = $result->solicitarPostagemReversa->resultado_solicitacao->status_objeto;
                    $dados['desc_status_objeto'] = 'A COLETAR';
                    $dados['prazo'] = $result->solicitarPostagemReversa->resultado_solicitacao->prazo;
                    $dados['data_solicitacao'] = $result->solicitarPostagemReversa->resultado_solicitacao->data_solicitacao;
                    $dados['hora_solicitacao'] = $result->solicitarPostagemReversa->resultado_solicitacao->hora_solicitacao;

                    Logistica_reversa::create($dados);

                    return redirect()->route('logistica.correios.index')->with('success', 'Solicitação Realizada com sucesso!');
                } else {
                    return redirect()->back()->with('error', $result->solicitarPostagemReversa->resultado_solicitacao->codigo_erro .' - ' . $result->solicitarPostagemReversa->resultado_solicitacao->descricao_erro)->withInput();
                }
            } else {
                return redirect()->back()->with('error', $result->solicitarPostagemReversa->cod_erro .' - ' . $result->solicitarPostagemReversa->msg_erro)->withInput();
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Houve algum erro na requisição: ' . $e->getMessage() . '. Tente novamente mais tarde')->withInput();
        }
    }

    public function consultarPedido(){
        try {
            // Credenciais de acesso ao serviço SOAP
            $credenciais = [
                'login' => 'cardideal',  // Substitua 'cardideal' pelo seu login
                'password' => '1yYrHozdNRkcIJFJCc4gZsAmlLsyEquyLrb5X3NL',  // Substitua pela sua senha
            ];

            // Instância do cliente SOAP com as credenciais e o WSDL do serviço
            $client = new SoapClient('https://apps.correios.com.br/logisticaReversaWS/logisticaReversaService/logisticaReversaWS?wsdl', $credenciais);

            // Faça a chamada para o método desejado (no caso, solicitarPostagemReversa)
            $result = $client->solicitarPostagemReversa($params);

            // Processamento adicional dos dados, se necessário...

            // Retorne os dados para a view
            return ['resultado' => $result];
        } catch (\Exception $e) {
            // Trate a exceção aqui (por exemplo, registre-a, exiba uma mensagem de erro, etc.)
            return ['mensagem' => $e->getMessage()];
        }
    }

    public function cancelarPedido($id){
        // Encontra o contrato de logística reversa pelo ID
        $contrato = Logistica_reversa::findOrFail($id);

        // Determina as credenciais e código administrativo com base no contrato
        if ($contrato->contrato == '05884660000104') {
            $user = config('variaveis.username_solucoes');
            $password = config('variaveis.password_solucoes');
            $cod_adm = config('variaveis.cod_adm_solucoes');
        } else {
            $user = config('variaveis.username_ip');
            $password = config('variaveis.password_ip');
            $cod_adm = config('variaveis.cod_adm_ip');
        }

        // Credenciais de acesso ao serviço SOAP
        $credenciais = [
            'login' => $user,
            'password' => $password,
        ];

        // Instância do cliente SOAP com as credenciais e o link do serviço
        $client = new SoapClient(config('variaveis.link'), $credenciais);

        // Determina o tipo de coleta (C ou A)
        $tipo = $contrato->tipo_coleta == 'CA' ? 'C' : 'A';

        // Parâmetros para cancelar o pedido
        $params = [
            'codAdministrativo' => $cod_adm,
            'numeroPedido' => $contrato->num_coleta,
            'tipo' => $tipo,
        ];

        try {
            // Chama o método cancelarPedido
            $result = $client->cancelarPedido($params);

            // Verifica se ocorreu algum erro no cancelamento
            if (property_exists($result->cancelarPedido, 'cod_erro')) {
                return redirect()->back()->with('error', $result->cancelarPedido->cod_erro . ' - ' . $result->cancelarPedido->msg_erro)->withInput();
            } else {
                // Atualiza o status do objeto no contrato de logística reversa
                $desc_status_objeto['desc_status_objeto'] = strtoupper($result->cancelarPedido->objeto_postal->status_pedido);
                $desc_status_objeto['status_objeto'] = '09'; // Status para cancelado

                $contrato->update($desc_status_objeto);

                // Retorna para a página de índice com mensagem de sucesso
                return redirect()->route('logistica.correios.index')->with('success', 'Pedido: ' . $result->cancelarPedido->objeto_postal->numero_pedido . " cancelado!" . " Motivo: " . $result->cancelarPedido->objeto_postal->status_pedido);
            }
        } catch (\Exception $e) {
            // Trate a exceção aqui (por exemplo, registre-a, exiba uma mensagem de erro, etc.)
            return redirect()->back()->with('error', 'Houve algum erro na requisição: ' . $e->getMessage())->withInput();
        }
    }

        public function view($id){
            // Encontra a solicitação de logística reversa pelo ID
            $solicitacao = Logistica_reversa::findOrFail($id);

            // Retorna a view com os dados da solicitação
            return view('logistica.correios.visualizar', compact('solicitacao'));
        }

//DEIXE CASO AS JOOBS NÃO FUNCIONAR, PODER ALTERAR PARA FUNCIONAR NO BOTÃO!!!
        // public function acompanharPedido(){
        //      // Caminho do arquivo de log
        //      $logFile = storage_path('logs/atualizacao_logistica.log');

        //       // Busca todas as solicitações pendentes com status específicos
        //       $pedidosPendentes = Logistica_reversa::whereIn('status_objeto', ['0', '00', '01', '1', '03', '3', '04', '4', '05', '5', '06', '6', '55'])->get();

        //       // Verifica se não há pedidos pendentes
        //       if ($pedidosPendentes->isEmpty()) {
        //           $this->log("Sem logística no banco", $logFile);
        //       } else {
        //           $pedidos = [];
        //           foreach ($pedidosPendentes as $pedido) {
        //               $pedidos[] = $pedido->num_coleta;
        //           }
        //           // Registra no log os pedidos a serem atualizados
        //           $this->log("Pedidos a serem atualizados: " . implode(", ", $pedidos), $logFile);

        //             // Itera sobre os pedidos pendentes
        //             foreach ($pedidosPendentes as $pedido) {
        //                 set_time_limit(600); // Define o limite de tempo de execução para 10 minutos
        //                 try {
        //                     // Chama o método acompanharPedido2 para cada pedido
        //                     $result = $this->acompanharPedido2($pedido, $logFile);

        //                     // Salva os dados na tabela status_logistica
        //                     $this->salvarStatusLogistica($result, $logFile);

        //                     // Atualiza os registros na tabela Logistica_reversa
        //                     $this->atualizarStatusLogisticaReversa($pedido, $result, $logFile);
        //                 } catch (\Exception $e) {
        //                     // Registra no log se houver erro ao acompanhar o pedido
        //                     $this->log("Erro ao acompanhar pedido {$pedido->num_coleta}: " . $e->getMessage(), $logFile);
        //                 }
        //             }

        //             // Registra no log a conclusão da atualização
        //             $this->log('Atualização concluída com sucesso!', $logFile);

        //             // Redireciona para a rota de índice com mensagem de sucesso
        //             return redirect()->route('logistica.correios.index')->with('success', 'Status de solicitações atualizados manualmente!');
        //         }
        // }

         public function acompanharPedido2($pedido, $logFile)
         {
             try {
                 // Determina as credenciais com base no contrato
                 if ($pedido->contrato == '05884660000104') {
                     $user = config('variaveis.username_solucoes');
                     $password = config('variaveis.password_solucoes');
                     $cod_adm = config('variaveis.cod_adm_solucoes');
                 } else {
                     $user = config('variaveis.username_ip');
                   $password = config('variaveis.password_ip');
                     $cod_adm = config('variaveis.cod_adm_ip');
                 }

                 // Credenciais de acesso ao serviço SOAP
                 $credenciais = [
                     'login' => $user,
                     'password' => $password,
                 ];

                 // Instância do cliente SOAP com as credenciais e o link do serviço
                 $client = new SoapClient(config('variaveis.link'), $credenciais);

                 // Determina o tipo de coleta (C ou A)
                 $tipo = $pedido->tipo_coleta == 'CA' ? 'C' : 'A';

                 // Parâmetros para acompanhar o pedido
                 $params = [
                     'codAdministrativo' => $cod_adm,
                     'tipoBusca' => 'H',
                     'numeroPedido' => $pedido->num_coleta,
                     'tipoSolicitacao' => $tipo,
                 ];

                 // Chama o método acompanharPedido
                 $result = $client->acompanharPedido($params);

                 // Registra no log o número de coleta
                 $this->log($pedido->num_coleta, $logFile);

                // Retorna o resultado obtido
                return $result;
            } catch (\Exception $e) {
                // Registra no log se houver erro ao acompanhar o pedido
                $this->log("Erro ao buscar dados do pedido {$pedido->num_coleta}: " . $e->getMessage(), $logFile);
                return "Erro ao buscar dados do pedido {$pedido->num_coleta}: " . $e->getMessage();
            }
        }

     public function salvarStatusLogistica($result, $logFile)
        {
            dd($result);
            try {
                // Verifica se há coletas para processar
                if (isset($result->acompanharPedido->coleta)) {
                    $coletas = $result->acompanharPedido->coleta;
                    // Verifica se $coletas é um array ou um objeto
                    if (is_array($coletas)) {
                        // Se for um array, itera sobre cada elemento
                        foreach ($coletas as $coleta) {


                             $this->salvarStatusIndividual($coleta, $logFile);
                         }
                    } elseif (is_object($coletas)) {

                        // Se for um objeto, chama a função salvarStatusIndividual diretamente
                        $this->salvarStatusIndividual($coletas, $logFile);
                    } else {
                        $this->log("O valor de coleta não é nem um array nem um objeto.", $logFile);
                    }
                } else {
                    $this->log("acompanharPedido ou coleta não está definido.", $logFile);
                }
            } catch (\Exception $e) {
                // Registra no log se houver erro ao salvar o status da logística
                $this->log("Erro ao buscar logística: {$e->getMessage()}", $logFile);
            }
        }

        public function salvarStatusIndividual($coleta, $logFile)
        {

            try {
                // Verifica se o status já existe na tabela status_logistica
                $statusExistente = statusLogistica::where('numero_pedido', $coleta->numero_pedido)
                    ->where('status', $coleta->historico->status)
                    ->first();

                // Se o status não existir na tabela status_logistica, salva-o
                if (!$statusExistente) {
                    $status = new statusLogistica();
                    $status->numero_pedido = $coleta->numero_pedido;
                    $status->status = $coleta->historico->status;
                    $status->descricao_status = $coleta->historico->descricao_status;
                    $status->data_atualizacao = $coleta->historico->data_atualizacao;
                    $status->hora_atualizacao = $coleta->historico->hora_atualizacao;
                    $status->observacao = $coleta->historico->observacao;
                    $status->save();
                    $this->log("Status salvo com sucesso.", $logFile);
                }
            } catch (\Exception $e) {
                // Registra no log se houver erro ao salvar o status individual
                $this->log("Erro ao salvar status: {$e->getMessage()}", $logFile);
            }
        }

        public function atualizarStatusLogisticaReversa($pedido, $result, $logFile)
        {
            try {
                // Determina o status do objeto com base no resultado obtido
                if (is_array($result->acompanharPedido->coleta)) {
                    $quantidadeItensColeta = count($result->acompanharPedido->coleta) - 1;
                    $atl['status_objeto'] = $result->acompanharPedido->coleta[$quantidadeItensColeta]->objeto->ultimo_status;
                    $atl['desc_status_objeto'] = $result->acompanharPedido->coleta[$quantidadeItensColeta]->objeto->descricao_status;
                     $etiqueta = $result->acompanharPedido->coleta[$quantidadeItensColeta]->objeto->numero_etiqueta;
                 } else {
                     $atl['status_objeto'] = $result->acompanharPedido->coleta->objeto->ultimo_status;
                     $atl['desc_status_objeto'] = $result->acompanharPedido->coleta->objeto->descricao_status;
                     $etiqueta = $result->acompanharPedido->coleta->objeto->numero_etiqueta;
                 }

                 // Atualiza o número de etiqueta, se houver
               if ($etiqueta) {
                     $atl['num_etiqueta'] = $etiqueta;
                 } else {
                  $atl['num_etiqueta'] = $pedido->num_etiqueta;
                }

             // Atualiza o registro na tabela Logistica_reversa
             $pedido->update($atl);

                // Registra no log a conclusão da atualização do pedido
                $this->log("Atualização do pedido {$pedido->num_coleta} concluída com sucesso.", $logFile);
            } catch (\Exception $e) {
                // Registra no log se houver erro ao atualizar o pedido
                $this->log("Erro ao atualizar pedido {$pedido->num_coleta}: {$e->getMessage()}", $logFile);
            }
        }

        public function log($message, $logFile)
        {
            // Registra a mensagem no arquivo de log com a data e hora atuais
            file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $message . "\n", FILE_APPEND);
        }

        public function rastrear(Request $request)
        {
            // Valida os dados do formulário
            $request->validate([
                'cod_rastreio' => 'required',
            ]);

            try {
                // Gera o token de acesso à API dos Correios
                $result = $this->gerarToken(config('variaveis.username_solucoes'), config('variaveis.password_solucoes'));

                // Token de acesso
                $token = $result;

                // Endpoint da API para rastreamento de objetos
                $url = 'https://api.correios.com.br/srorastro/v1/objetos?codigosObjetos=' . $request->cod_rastreio . '&resultado=T';

                // Faz a requisição GET com o token de autenticação no cabeçalho
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])->get($url);

                // Verifica se a requisição foi bem-sucedida
                if ($response->successful()) {
                    // Retorna os dados da resposta da API para a view de rastreamento
                    return view('logistica.correios.rastreio', ['result' => $response->json()]);
                } else {
                    // Se houver erro na requisição, retorna uma resposta com o status de erro
                    return response()->json(['error' => 'Erro ao fazer a requisição'], $response->status());
                }
            } catch (\Exception $e) {
                // Captura qualquer exceção que ocorra durante a requisição
                return response()->json(['error' => $e->getMessage()], 500); // Retorna um erro interno do servidor (status 500)
            }
        }

        public function rastrear_index($etiqueta)
        {


            try {
                // Gera o token de acesso à API dos Correios
                $result = $this->gerarToken(config('variaveis.username_solucoes'), config('variaveis.password_solucoes'));

                // Token de acesso
                $token = $result;

                // Endpoint da API para rastreamento de objetos
                $url = 'https://api.correios.com.br/srorastro/v1/objetos?codigosObjetos=' . $etiqueta. '&resultado=T';

                // Faz a requisição GET com o token de autenticação no cabeçalho
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])->get($url);

                // Verifica se a requisição foi bem-sucedida
                if ($response->successful()) {
                    // Retorna os dados da resposta da API para a view de rastreamento
                    return (['result' => $response->json()]);
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

                // Número do cartão de postagem
                $cartao = "0077498895";

                // Endpoint da API para obter o token de acesso
                $url = 'https://api.correios.com.br/token/v1/autentica/cartaopostagem';

                // Faz uma requisição POST para obter o token de acesso
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . $authString,
                ])->post($url, $cartao);

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

         public function rastreio_index(){
             // Busca todas as solicitações pendentes com status específicos
             $pedidosPendentes = Logistica_reversa::whereIn('status_objeto', ['0', '00', '01', '1', '03', '3', '04', '4', '05', '5', '06', '6', '55'])->get();
            // DD($pedidosPendentes[45]);
             return view('logistica.correios.rastreio',compact('pedidosPendentes'));
         }

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
