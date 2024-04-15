<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SoapClient;
use App\Models\parametros_correios_cartao;
use App\Models\Logistica_reversa;
use App\Models\statusLogisitca;
use Illuminate\Support\Facades\Log;

class AtualizarInformacoesLogistica extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:atualizar-informacoes-logistica';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ataulização do status das logisticas';

    /**
     * Execute the console command.
     */
    public function handle()

    {
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
                $result = $this->acompanharPedido($pedido, $logFile);

                // Salvar os dados na tabela status_logistica
                $this->salvarStatusLogistica($result, $logFile);

                // Atualizar registros na tabela Logistica_reversa
                $this->atualizarStatusLogisticaReversa($pedido, $result, $logFile);
            }

            $this->log('Atualização concluída com sucesso!', $logFile);



    }
        private function acompanharPedido($pedidos, $logFile)
        {
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


        private function salvarStatusLogistica($result, $logFile)
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

        private function salvarStatusIndividual($coleta, $logFile)
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


        private function atualizarStatusLogisticaReversa($pedido, $result, $logFile)
        {
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

        private function log($message, $logFile)
    {
        file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $message . "\n", FILE_APPEND);
    }
}
