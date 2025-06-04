<?php

namespace App\Http\Controllers\TestesInternos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Root_Logistica_Controller extends Controller
{
    //DEIXE CASO AS JOOBS NÃO FUNCIONAR, PODER ALTERAR PARA FUNCIONAR NO BOTÃO!!!
    public function acompanharPedido3()
    {
        // Caminho do arquivo de log
        $logFile = storage_path('logs/atualizacao_logistica.log');
        // Busca todas as solicitações pendentes com status específicos
        //  $quatroMesesAtras = Carbon::now()->subMonths(4);
        //  $pedidosPendentes = Logistica_reversa::whereIn('status_objeto', ['0', '00', '01', '1', '03', '3', '04', '4', '05', '5', '06', '6', '55'])
        //      ->where('created_at', '>=', $quatroMesesAtras)
        //      ->get();

        $pedidosPendentes = Logistica_reversa::where('num_coleta', '286068816')->get();

        // Verifica se não há pedidos pendentes
        if ($pedidosPendentes->isEmpty()) {
            $this->log("Sem logística no banco", $logFile);
        } else {
            $pedidos = [];
            foreach ($pedidosPendentes as $pedido) {
                $pedidos[] = $pedido->num_coleta;
            }
            // Registra no log os pedidos a serem atualizados
            $this->log("Pedidos a serem atualizados: " . implode(", ", $pedidos), $logFile);


            // Itera sobre os pedidos pendentes
            foreach ($pedidosPendentes as $pedido) {
                set_time_limit(600); // Define o limite de tempo de execução para 10 minutos
                try {
                    // Chama o método acompanharPedido2 para cada pedido
                    $result = $this->acompanharPedido2($pedido, $logFile);
                    dd($result);
                    // Salva os dados na tabela status_logistica
                    $this->salvarStatusLogistica($result, $logFile);
                    // Atualiza os registros na tabela Logistica_reversa
                    $this->atualizarStatusLogisticaReversa($pedido, $result, $logFile);
                } catch (\Exception $e) {
                    // Registra no log se houver erro ao acompanhar o pedido
                    $this->log("Erro ao acompanhar pedido {$pedido->num_coleta}: " . $e->getMessage(), $logFile);
                }

                // Registra no log a conclusão da atualização
                $this->log('Atualização concluída com sucesso!', $logFile);
                // Redireciona para a rota de índice com mensagem de sucesso
                return redirect()->route('logistica.correios.index')->with('success', 'Status de solicitações atualizados manualmente!');
            }
        }
    }

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

}
