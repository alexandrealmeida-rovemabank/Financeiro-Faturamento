<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaturaItem extends Model
{
    use HasFactory;

    protected $table = 'fatura_itens';
    
    protected $fillable = [
        'fatura_id',
        'transacao_faturamento_id',
        'descricao_produto',
        'produto_id',
        'produto_categoria_id',
        'quantidade',
        'valor_unitario',
        'valor_subtotal',
        'aliquota_aplicada',
        'valor_imposto',
        'valor_total_item',
    ];

    // Relação: Um item pertence a uma fatura
    public function fatura()
    {
        return $this->belongsTo(Fatura::class);
    }

    // Relação: Um item veio de uma transação
    public function transacao()
    {
        return $this->belongsTo(TransacaoFaturamento::class, 'transacao_faturamento_id', 'id');
    }
}