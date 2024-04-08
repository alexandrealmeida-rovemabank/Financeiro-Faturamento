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
            $pedidosPendentes = Logistica_reversa::whereIn('status_objeto', ['0','01', '03', '04', '05', '06'])->get();

            if ($pedidosPendentes->isEmpty()) {
                $this->log("Sem logística no banco", $logFile);
            }

            foreach ($pedidosPendentes as $pedido) {
                // Executar acompanharPedido
                $result = $this->acompanharPedido($pedido);

                // Salvar os dados na tabela status_logistica
                $this->salvarStatusLogistica($result, $logFile);

                // Atualizar registros na tabela Logistica_reversa
                $this->atualizarStatusLogisticaReversa($pedido, $result, $logFile);
            }

            $this->log('Atualização concluída com sucesso!', $logFile);



    }
        private function acompanharPedido($pedidos)
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
                'numeroPedido' =>  $pedidos->num_coleta,
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
            foreach ($result->acompanharPedido->coleta as $coleta) {
                // Verifica se o status já existe na tabela status_logistica
                $statusExistente = statusLogisitca::where('numero_pedido', $coleta->numero_pedido)
                                                ->where('status',  $coleta->historico->status)
                                                ->first();
                //return $statusExistente;
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
            }
        }

        private function atualizarStatusLogisticaReversa($pedido, $result, $logFile)
        {

            $atl['status_objeto'] = $result->acompanharPedido->coleta->objeto->ultimo_status;
            $atl['desc_status_objeto'] = $result->acompanharPedido->coleta->objeto->descricao_status;

            //return $atl;
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
