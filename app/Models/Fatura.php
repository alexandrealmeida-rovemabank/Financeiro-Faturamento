<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Casts\Attribute; 
use App\Models\FaturaPagamento; 

class Fatura extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'faturas'; // O search_path cuida do schema

    protected $fillable = [
        'cliente_id',
        'numero_fatura',
        'valor_total',
        'valor_impostos',
        'valor_descontos',
        'valor_liquido',
        'data_emissao',
        'data_vencimento',
        'status',
        'observacoes',
        'periodo_fatura'
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_vencimento' => 'date',
        'periodo_fatura' => 'date',
    ];

    // Log do Spatie
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'valor_liquido', 'data_vencimento'])
            ->setDescriptionForEvent(fn(string $eventName) => "Fatura {$this->id} foi {$eventName}");
    }

    // Relação: Uma fatura tem muitos itens
    public function itens()
    {
        return $this->hasMany(FaturaItem::class);
    }

    // Relação: Uma fatura pertence a um cliente (Empresa)
    public function cliente()
    {
        // Apontando para o Model Empresa que você já deve ter
        return $this->belongsTo(Empresa::class, 'cliente_id', 'id');
    }

     public function pagamentos()
    {
        return $this->hasMany(FaturaPagamento::class, 'fatura_id');
    }

    protected function saldoPendente(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                
                $totalFatura = $this->valor_liquido ?? 0;

                // 1. Se o status for 'pendente' ou 'aguardando_pagamento' (reaberta),
                //    o saldo é, por definição, o valor total, ignorando pagamentos órfãos.
                if (in_array($this->status, ['pendente', 'aguardando_pagamento'])) {
                    return round($totalFatura, 2);
                }

                // 2. Se o status for 'recebida', o saldo é 0.
                if ($this->status == 'recebida') {
                    return 0.00;
                }

                // 3. Se for 'recebida_parcial', calcula o valor real.
                //    (Usamos a relação já carregada se ela existir, ou fazemos a query)
                $totalPago = $this->relationLoaded('pagamentos')
                                ? $this->pagamentos->sum('valor_pago')
                                : $this->pagamentos()->sum('valor_pago');
                
                $saldo = $totalFatura - $totalPago;
                
                // Garante que o saldo nunca seja negativo
                return max(0, round($saldo, 2));
            }
        );
    }

    public function descontos()
    {
        return $this->hasMany(FaturaDesconto::class, 'fatura_id');
    }

    // <<<--- ADICIONE ESTE ACESSOR ---
    /**
     * Acessor: Calcula o valor TOTAL (R$) de todos os descontos manuais.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function valorDescontosManuais(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Se a relação 'descontos' já foi carregada, usa-a. Senão, carrega.
                $descontos = $this->relationLoaded('descontos') ? $this->descontos : $this->load('descontos')->descontos;
                
                // Itera e soma usando o acessor 'valor_calculado' de cada desconto
                return $descontos->sum(function ($desconto) {
                    return $desconto->valor_calculado;
                });
            }
        );
    }

}