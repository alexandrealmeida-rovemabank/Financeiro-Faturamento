<?php

namespace App\Console\Commands;
//namespace App\Console\Commands\Faturamento; // Certifique-se que o namespace está correto

use Illuminate\Console\Command;
use App\Models\TransacaoFaturamento;
use App\Models\ParametroCliente;
use App\Models\ParametroGlobal;
use App\Models\ParametroTaxaAliquota;
use App\Models\Empresa;
use App\Models\Fatura;
use App\Models\FaturaItem; // Adicione este import
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GerarFaturas extends Command
{
    protected $signature = 'faturamento:gerar-faturas 
                            {--data-referencia= : Data de referência para o faturamento (YYYY-MM-DD). Padrão: hoje.}
                            {--cliente= : ID de um cliente específico para faturar.}';
    protected $description = 'Gera as faturas com base nas transações não processadas.';

    // Armazena os parâmetros carregados
    protected $paramGlobal;
    protected $taxasPorCategoria;

    public function __construct()
    {
        parent::__construct();
        // Cachear parâmetros globais e taxas no início
        $this->paramGlobal = ParametroGlobal::first();
        $this->taxasPorCategoria = ParametroTaxaAliquota::all()->keyBy(function ($item) {
            return $item->organizacao_id . '-' . $item->produto_categoria_id;
        });
    }

    public function handle()
    {
        $this->info('Iniciando geração de faturas...');
        $dataReferencia = $this->option('data-referencia') ? Carbon::parse($this->option('data-referencia')) : Carbon::now();
        
        // 1. Buscar IDs de clientes que têm transações pendentes
        $query = TransacaoFaturamento::whereNull('fatura_id')
                    ->where('data_transacao', '<=', $dataReferencia->endOfDay());

        $clienteIds = [];
        if ($this->option('cliente')) {
            // Se um cliente foi especificado, só processa ele
            $clienteIds = collect([$this->option('cliente')]);
        } else {
            // MUDANÇA PRINCIPAL: Em vez de ->get(), pegamos apenas os IDs distintos.
            // Isso é leve e rápido.
            $this->info('Mapeando clientes com transações pendentes...');
            $clienteIds = $query->select('cliente_id')->distinct()->pluck('cliente_id');
        }

        if ($clienteIds->isEmpty()) {
            $this->info('Nenhum cliente com transações novas para faturar.');
            return 0;
        }

        $this->info("Encontradas transações para {$clienteIds->count()} clientes. Processando um por um...");
        
        // Adiciona uma barra de progresso para feedback visual
        $bar = $this->output->createProgressBar($clienteIds->count());
        $bar->start();

        // MUDANÇA PRINCIPAL: Iteramos sobre os CLIENTES, não sobre as transações
        foreach ($clienteIds as $cliente_id) {
            
            // Usar DB Transaction por cliente. Se um falhar, não afeta os outros.
            DB::beginTransaction();
            try {
                // 2. Buscar Cliente e Parâmetros
                $cliente = Empresa::with('organizacao')->find($cliente_id);
                if (!$cliente) {
                    $this->error("\nCliente ID: {$cliente_id} não encontrado em public.empresa. Pulando.");
                    DB::rollBack(); // Anula a transação "vazia"
                    $bar->advance();
                    continue;
                }

                $paramCliente = ParametroCliente::firstOrNew(['empresa_id' => $cliente_id]);
                $usaGlobal = $paramCliente->ativar_parametros_globais;
                
                // 3. Calcular Data de Vencimento
                $diasVencimento = $usaGlobal 
                    ? ($cliente->organizacao->publica ? $this->paramGlobal->dias_vencimento_publico : $this->paramGlobal->dias_vencimento_privado)
                    : $paramCliente->dias_para_vencimento;
                
                $dataVencimento = $dataReferencia->copy()->addDays($diasVencimento);

                // 4. Criar a Fatura (Cabeçalho)
                $fatura = Fatura::create([
                    'cliente_id' => $cliente_id,
                    'data_emissao' => $dataReferencia->toDateString(),
                    'data_vencimento' => $dataVencimento->toDateString(),
                    'status' => 'pendente',
                    'valor_total' => 0, 'valor_impostos' => 0,
                    'valor_descontos' => 0, 'valor_liquido' => 0,
                ]);

                $totalFatura = 0;
                $totalImpostos = 0;
                $itensProcessados = 0;

                // 5. Processar Itens (Transações) - AGORA SÓ PARA ESTE CLIENTE
                // MUDANÇA: Usamos chunkById() para o caso de um único cliente ter milhões de transações.
                // Isso processa em lotes de 500 (ou o número que preferir).
                TransacaoFaturamento::whereNull('fatura_id')
                    ->where('cliente_id', $cliente_id)
                    ->where('data_transacao', '<=', $dataReferencia->endOfDay())
                    ->with('produto') // Eager load produto
                    // ... dentro do método handle() ...

                ->chunkById(500, function ($transacoes) use (&$fatura, &$totalFatura, &$totalImpostos, &$itensProcessados, $cliente) {
                        
                        foreach ($transacoes as $transacao) {
                            
                            if (!$transacao->produto) {
                                $this->error("\nTransação ID {$transacao->id} sem produto (ID: {$transacao->produto_id}). Pulando item.");
                                continue; 
                            }

                            $categoriaId = $transacao->produto->produto_categoria_id;
                            $organizacaoId = $cliente->organizacao_id;

                            $taxa = $this->taxasPorCategoria->get($organizacaoId . '-' . $categoriaId);
                            
                            $aliquota = $taxa ? $taxa->taxa_percentual : 0;
                            

                            // --- INÍCIO DA CORREÇÃO ---
                            // Garantimos que os valores não sejam nulos (usando 0 como padrão)
                            // O log do erro indica que sua transação tem 6 e 6, então isso vai funcionar.
                            $quantidade = $transacao->quantidade ?? 0;
                            $valor_unitario = $transacao->valor_unitario ?? 0;

                            // Calculamos o subtotal com base na Qtd e Vl. Unitário, em vez de usar $transacao->valor
                            $subtotal = $quantidade * $valor_unitario;
                            // --- FIM DA CORREÇÃO ---


                            $valorImposto = ($subtotal * $aliquota) / 100;
                            $totalItem = $subtotal + $valorImposto;

                            FaturaItem::create([
                                'fatura_id' => $fatura->id,
                                'transacao_faturamento_id' => $transacao->id,
                                'descricao_produto' => $transacao->produto->nome,
                                'produto_id' => $transacao->produto_id,
                                'produto_categoria_id' => $categoriaId,
                                'quantidade' => $quantidade,
                                'valor_unitario' => $valor_unitario,
                                'valor_subtotal' => $subtotal, // <-- Agora terá um valor (ex: 36.00)
                                'aliquota_aplicada' => $aliquota,
                                'valor_imposto' => $valorImposto,
                                'valor_total_item' => $totalItem,
                            ]);

                            $totalFatura += $totalItem;
                            $totalImpostos += $valorImposto;
                            $itensProcessados++;

                            // 6. Marcar transação como faturada
                            $transacao->fatura_id = $fatura->id;
                            $transacao->save();
                        }
                    });


                // 7. Se nenhum item foi válido para este cliente, reverte a fatura.
                if ($itensProcessados == 0) {
                    DB::rollBack();
                    $bar->advance();
                    continue; // Pula para o próximo cliente
                }

                // 8. Calcular Desconto IR (se aplicável)
                $descontoIR = 0;
                $isentoIR = $usaGlobal ? $this->paramGlobal->isento_ir_global : $paramCliente->isento_ir;
                
                if (!$isentoIR) {
                    // ... Sua lógica de cálculo de IR aqui ...
                }

                // 9. Atualizar Totais da Fatura
                $fatura->valor_total = $totalFatura;
                $fatura->valor_impostos = $totalImpostos;
                $fatura->valor_descontos = $descontoIR;
                $fatura->valor_liquido = $totalFatura - $descontoIR;
                $fatura->save();

                DB::commit();
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("\nFalha ao processar Cliente {$cliente_id}: " . $e->getMessage());
            }

            $bar->advance(); // Avança a barra de progresso
        }

        $bar->finish();
        $this->info("\n\nGeração de faturas concluída.");
        return 0;
    }
}