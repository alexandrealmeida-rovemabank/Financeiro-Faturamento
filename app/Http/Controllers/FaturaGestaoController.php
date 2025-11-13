<?php

namespace App\Http\Controllers;

use App\Models\Fatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\FaturaPagamento;
use App\Models\FaturaDesconto; // Adicionado
use Carbon\Carbon;
use Illuminate\Validation\Rule; // Adicionado

/**
 * Controller Faturamento (Parte 02)
 * Focado na Gestão de Faturas Individuais (Editar, Pagar, Refaturar)
 */
class FaturaGestaoController extends Controller
{
    /**
     * Busca os detalhes de uma fatura para os modais.
     */
    public function getFaturaDetalhes(Fatura $fatura)
    {
        $fatura->refresh(); // Força a releitura dos dados
        $fatura->load('pagamentos'); 

        return response()->json([
            'id' => $fatura->id,
            'data_vencimento' => $fatura->data_vencimento ? Carbon::parse($fatura->data_vencimento)->format('Y-m-d') : null,
            'nota_fiscal' => $fatura->nota_fiscal,
            'status' => $fatura->status,
            'valor_liquido' => $fatura->valor_liquido,
            'valor_liquido_formatado' => 'R$ ' . number_format($fatura->valor_liquido, 2, ',', '.'),
            'saldo_pendente' => (float)($fatura->saldo_pendente ?? 0), 
            'saldo_pendente_formatado' => 'R$ ' . number_format($fatura->saldo_pendente, 2, ',', '.'),
        ]);
    }

    /**
     * 1. Editar Fatura (NF e Vencimento)
     */
    public function update(Request $request, Fatura $fatura)
    {
        $validator = Validator::make($request->all(), [
            'data_vencimento' => 'required|date',
            'nota_fiscal' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Dados inválidos.', 'errors' => $validator->errors()], 422);
        }
        
        $old_data = [
            'data_vencimento' => $fatura->data_vencimento ? $fatura->data_vencimento->format('Y-m-d') : null,
            'nota_fiscal' => $fatura->nota_fiscal,
        ];

        $fatura->data_vencimento = $request->data_vencimento;
        $fatura->nota_fiscal = $request->nota_fiscal;
        $fatura->save();
        
        $new_data = [
            'data_vencimento' => $request->data_vencimento,
            'nota_fiscal' => $request->nota_fiscal,
        ];

        activity()
           ->performedOn($fatura)
           ->causedBy(Auth::user())
           ->UseLog('faturamento') 
           ->event('updated') 
           ->withProperties(['old' => $old_data, 'new' => $new_data]) 
           ->log("Editou dados da fatura (NF: {$request->nota_fiscal}, Venc: {$request->data_vencimento})");

        return response()->json(['success' => true, 'message' => 'Fatura atualizada com sucesso.']);
    }

    /**
     * 2. Reabrir Fatura (Refaturamento)
     */
    public function reabrirFatura(Request $request, Fatura $fatura)
    {
        $validator = Validator::make($request->all(), [
            'motivo_reabertura' => 'required|string|min:10|max:500',
            'novo_status' => 'required|string|in:aguardando_pagamento', // Força 'aguardando_pagamento'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'O motivo é obrigatório (mínimo 10 caracteres).', 'errors' => $validator->errors()], 422);
        }
        
        if ($fatura->status != 'recebida') {
             return response()->json(['success' => false, 'message' => 'Apenas faturas recebidas podem ser reabertas.'], 403);
        }

        $motivo = $request->motivo_reabertura;
        $old_status = $fatura->status;
        
        // Deleta todos os pagamentos e descontos associados
        $fatura->pagamentos()->delete();
        $fatura->descontos()->delete();

        // Define novo status
        $fatura->status = 'aguardando_pagamento';
        
        // Recalcula o valor líquido (removendo descontos)
        $this->recalcularTotaisFatura($fatura); // Isso vai salvar a fatura
        
        activity()
           ->performedOn($fatura)
           ->causedBy(Auth::user())
           ->UseLog('faturamento')
           ->event('reopened')
           ->withProperties([
                'old_status' => $old_status,
                'new_status' => $fatura->status,
                'reason' => $motivo,
                'action' => 'Pagamentos e Descontos zerados (reset)'
           ])
           ->log("Fatura reaberta (Reset). Novo status: {$fatura->status}. Motivo: {$motivo}");

        return response()->json(['success' => true, 'message' => "Fatura reaberta com status '{$fatura->status}'. Pagamentos e descontos foram zerados."]);
    }

    /**
     * 3. & 4. Registrar Pagamento (Parcial ou Total)
     */
    public function addPagamento(Request $request, Fatura $fatura)
    {
        $fatura->refresh(); 
        $saldoPendente = $fatura->saldo_pendente;

        $validator = Validator::make($request->all(), [
            'data_pagamento' => 'required|date',
            'valor_pago' => "required|numeric|min:0.01|max:".($saldoPendente + 0.01),
            'comprovante' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'valor_pago.max' => "O valor pago não pode ser maior que o saldo pendente (R$ " . number_format($saldoPendente, 2, ',', '.') . ").",
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Dados inválidos.', 'errors' => $validator->errors()], 422);
        }

        $path = null;
        if ($request->hasFile('comprovante')) {
            // <<<--- ESTA É A CORREÇÃO DO CAMINHO ---
            // Salva no disco 'public', na pasta 'faturamento/comprovantes'
            // O caminho salvo no banco será: 'faturamento/comprovantes/HASH.ext'
            $path = $request->file('comprovante')->store('faturamento/comprovantes', 'public');
            // --- FIM DA CORREÇÃO ---
        }

        $pagamento = new FaturaPagamento([
            'fatura_id' => $fatura->id,
            'data_pagamento' => $request->data_pagamento,
            'valor_pago' => $request->valor_pago,
            'comprovante_path' => $path,
            'registrado_por_user_id' => Auth::id(),
        ]);
        $pagamento->save();

        // CORREÇÃO DO STATUS: Recalcula o status manualmente
        $fatura->refresh()->load('pagamentos'); 
        $totalLiquido = $fatura->valor_liquido ?? 0;
        $totalPago = $fatura->pagamentos->sum('valor_pago');
        $novoSaldoReal = round($totalLiquido - $totalPago, 2);
        $old_status = $fatura->status;

        if ($novoSaldoReal <= 0.001) {
            $fatura->status = 'recebida';
        } else {
            $fatura->status = 'recebida_parcial';
        }
        $fatura->save();
        
        activity()
           ->performedOn($fatura)
           ->causedBy(Auth::user())
           ->UseLog('faturamento')
           ->event('payment_added')
           ->withProperties([
                'payment_id' => $pagamento->id,
                'payment_value' => $pagamento->valor_pago,
                'payment_date' => $pagamento->data_pagamento->format('Y-m-d'),
                'has_voucher' => !empty($path),
                'old_status' => $old_status,
                'new_status' => $fatura->status,
           ])
           ->log("Registrou pagamento de R$ " . number_format($request->valor_pago, 2, ',', '.') . ". Novo status: {$fatura->status}.");

        return response()->json(['success' => true, 'message' => 'Pagamento registrado com sucesso.']);
    }

    /**
     * (HELPER) Recalcula Valor Líquido e Status da Fatura.
     */
    private function recalcularTotaisFatura(Fatura $fatura)
    {
        $fatura->load(['descontos', 'pagamentos']);

        $totalDescontoManual = $fatura->valor_descontos_manuais; // Acessor (R$)

        $fatura->valor_liquido = $fatura->valor_total 
                               - $fatura->valor_descontos 
                               - $totalDescontoManual;

        $totalPago = $fatura->pagamentos->sum('valor_pago');
        $novoSaldoReal = round($fatura->valor_liquido - $totalPago, 2);

        // CORREÇÃO DE STATUS (para quando o desconto quita a fatura)
        if ($novoSaldoReal <= 0.001) {
            $fatura->status = 'recebida';
        } else {
            if ($totalPago > 0) {
                $fatura->status = 'recebida_parcial';
            } else {
                if ($fatura->status != 'aguardando_pagamento') {
                     $fatura->status = 'pendente';
                }
            }
        }
        
        $fatura->save();
    }

    /**
     * (ROTA) Busca a lista de descontos (HTML) para o modal.
     */
    public function getDescontosLista(Fatura $fatura)
    {
        $descontos = $fatura->descontos()->with('usuario')->orderBy('created_at', 'desc')->get();
        return view('admin.faturamento._lista_descontos', compact('descontos', 'fatura'));
    }

    /**
     * (ROTA) Adiciona um novo desconto (fixo ou percentual).
     */
    public function addDesconto(Request $request, Fatura $fatura)
    {
        $fatura->refresh()->load('pagamentos');
        $saldoPendente = $fatura->saldo_pendente;

        $validator = Validator::make($request->all(), [
            'tipo' => ['required', Rule::in(['fixo', 'percentual'])],
            'valor' => 'required|numeric|min:0.01',
            'justificativa' => 'nullable|string|max:500',
            'force_quit' => 'nullable|boolean', // Valida '1' ou '0'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Dados inválidos.', 'errors' => $validator->errors()], 422);
        }
        
        if ($fatura->status == 'recebida') {
             return response()->json(['success' => false, 'message' => 'Não é possível aplicar desconto em faturas já recebidas.'], 403);
        }

        $valorDescontoInput = $request->valor;
        $valorCalculado = 0;
        $justificativa = $request->justificativa;

        // 1. Calcula o valor REAL (R$) do desconto
        if ($request->tipo == 'fixo') {
            $valorCalculado = $valorDescontoInput;
        } else {
            // Percentual é sobre o Saldo Pendente
            $valorCalculado = ($saldoPendente * $valorDescontoInput) / 100;
            $justificativa = "{$request->valor}% de R$ " . number_format($saldoPendente, 2, ',', '.') . ". " . $justificativa;
        }
        
        $valorCalculado = round($valorCalculado, 2);

        // 2. Validação de Saldo
        if ($valorCalculado > ($saldoPendente + 0.01)) {
            return response()->json(['success' => false, 'message' => 'O valor do desconto (R$ ' . number_format($valorCalculado, 2, ',', '.') . ') não pode ser maior que o saldo pendente (R$ ' . number_format($saldoPendente, 2, ',', '.') . ').'], 422);
        }
        
        // 3. Verifica se vai quitar a fatura e pede confirmação
        $quaseZerado = abs($valorCalculado - $saldoPendente) < 0.01;
        
        if ($quaseZerado && !$request->input('force_quit', false)) {
            return response()->json([
                'success' => false, 
                'needs_confirmation' => true, 
                'message' => 'Este desconto de R$ ' . number_format($valorCalculado, 2, ',', '.') . ' irá quitar a fatura. O status será alterado para "Recebida". Deseja continuar?'
            ], 422);
        }

        // 4. Cria o desconto (SEMPRE como 'fixo')
        $desconto = $fatura->descontos()->create([
            'user_id' => Auth::id(),
            'tipo' => 'fixo',
            'valor' => $valorCalculado,
            'justificativa' => trim($justificativa),
        ]);

        // 5. Recalcula o valor_liquido e status da fatura
        $this->recalcularTotaisFatura($fatura);
        
        activity()
           ->performedOn($fatura)
           ->causedBy(Auth::user())
           ->UseLog('faturamento')
           ->event('discount_added')
           ->withProperties($desconto->toArray())
           ->log("Aplicou desconto ({$request->tipo}) de R$ {$valorCalculado}.");

        return response()->json(['success' => true, 'message' => 'Desconto aplicado com sucesso.']);
    }

    /**
     * (ROTA) Remove um desconto manual.
     */
    public function removerDesconto(FaturaDesconto $desconto)
    {
        $fatura = $desconto->fatura;

        if ($fatura->status == 'recebida') {
             return response()->json(['success' => false, 'message' => 'Não é possível remover descontos de faturas já recebidas.'], 403);
        }

        $desconto->delete();
        $this->recalcularTotaisFatura($fatura);

        activity()
           ->performedOn($fatura)
           ->causedBy(Auth::user())
           ->UseLog('faturamento')
           ->event('discount_removed')
           ->withProperties($desconto->toArray())
           ->log("Removeu desconto ID {$desconto->id}.");

        return response()->json(['success' => true, 'message' => 'Desconto removido.']);
    }

    /**
     * (ROTA) Busca a lista de pagamentos (HTML) para o modal.
     */
    public function getPagamentosLista(Fatura $fatura)
    {
        $pagamentos = $fatura->pagamentos()->with('usuario')->orderBy('data_pagamento', 'desc')->get();
        return view('admin.faturamento._lista_pagamentos', compact('pagamentos', 'fatura'));
    }

    /**
     * (ROTA) Remove um pagamento manual.
     */
    public function removerPagamento(FaturaPagamento $pagamento)
    {
        $fatura = $pagamento->fatura;

        if ($fatura->status == 'recebida') {
             return response()->json(['success' => false, 'message' => 'Não é possível remover pagamentos de faturas já recebidas.'], 403);
        }

        if ($pagamento->comprovante_path) {
            Storage::delete($pagamento->comprovante_path);
        }
        
        $pagamento->delete();
        $this->recalcularTotaisFatura($fatura);

        activity()
           ->performedOn($fatura)
           ->causedBy(Auth::user())
           ->UseLog('faturamento')
           ->event('payment_removed')
           ->withProperties($pagamento->toArray())
           ->log("Removeu pagamento ID {$pagamento->id} no valor de R$ {$pagamento->valor_pago}.");

        return response()->json(['success' => true, 'message' => 'Pagamento removido.']);
    }
}