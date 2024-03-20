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
            $pedidosPendentes = Logistica_reversa::whereIn('status_objeto', ['0','1', '3', '4', '5', '6'])->get();

            if ($pedidosPendentes->isEmpty()) {
                $this->info("Sem logística no banco");
            }

            foreach ($pedidosPendentes as $pedido) {
                // Executar acompanharPedido
                $result = $this->acompanharPedido($pedido);

                // Salvar os dados na tabela status_logistica
                $this->salvarStatusLogistica($result);

                // Atualizar registros na tabela Logistica_reversa
                 $this->atualizarStatusLogisticaReversa($pedido, $result);
            }

            $this->info('Atualização concluída com sucesso!');



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
                return $e->getMessage();
            }
        }


        private function salvarStatusLogistica($result)
        {
            foreach ($result->acompanharPedido->coleta as $coleta) {
                // Verifica se o status já existe na tabela status_logistica
                $statusExistente = statusLogisitca::where('numero_pedido', $result->acompanharPedido->coleta->numero_pedido)
                                                ->where('status',  $result->acompanharPedido->coleta->historico->status)
                                                ->first();
                //return $statusExistente;
                // Se o status não existir na tabela status_logistica, salva-o
                if (!$statusExistente) {
                    $status = new statusLogisitca();
                    $status->numero_pedido =    $result->acompanharPedido->coleta->numero_pedido;
                    $status->status =           $result->acompanharPedido->coleta->historico->status;
                    $status->descricao_status = $result->acompanharPedido->coleta->historico->descricao_status;
                    $status->data_atualizacao = $result->acompanharPedido->coleta->historico->data_atualizacao;
                    $status->hora_atualizacao = $result->acompanharPedido->coleta->historico->hora_atualizacao;
                    $status->observacao =       $result->acompanharPedido->coleta->historico->observacao;
                    $status->save();
                }
            }
        }

        private function atualizarStatusLogisticaReversa($pedido, $result)
        {

            $atl['status_objeto'] = $result->acompanharPedido->coleta->objeto->ultimo_status;
            $atl['desc_status_objeto'] = $result->acompanharPedido->coleta->objeto->descricao_status;

            //return $atl;
            $pedido->update($atl);

            if ($pedido) {
                $this->info("Atualização do pedido {$pedido->num_coleta} concluída com sucesso.");
            } else {
                $this->error("Erro ao atualizar o pedido {$pedido->num_coleta}.");
            }


        }
}
