<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaturaDesconto extends Model
{
    use HasFactory;

    /**
     * Tabela associada ao model, especificando o schema.
     */
    protected $table = 'contas_receber.fatura_descontos';

    protected $fillable = [
        'fatura_id',
        'user_id',
        'tipo',
        'valor',
        'justificativa',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    /**
     * Relacionamento: O usuário que aplicou o desconto.
     */
    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Relacionamento: Pertence a uma Fatura.
     */
    public function fatura()
    {
        return $this->belongsTo(Fatura::class, 'fatura_id');
    }

    /**
     * Acessor: Retorna o valor (R$) calculado do desconto.
     * Ex: Se for 10% de uma fatura de R$ 1000, retorna 100.00
     * Ex: Se for Fixo de R$ 50, retorna 50.00
     */
    public function getValorCalculadoAttribute()
    {
        if ($this->tipo == 'fixo') {
            return $this->valor;
        }

        if ($this->tipo == 'percentual') {
            // Precisamos carregar a fatura (se já não estiver)
            $fatura = $this->fatura ?? $this->load('fatura');
            
            if (!$fatura) {
                return 0;
            }
            
            // Percentual é calculado sobre o Valor TOTAL (Bruto), não o líquido.
            // Isso evita descontos sobre descontos.
            $baseCalculo = $fatura->valor_total; 
            return ($baseCalculo * $this->valor) / 100;
        }

        return 0;
    }
}