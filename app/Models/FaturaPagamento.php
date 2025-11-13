<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaturaPagamento extends Model
{
    use HasFactory;

    /**
     * Tabela associada ao model, especificando o schema.
     *
     * @var string
     */
    protected $table = 'contas_receber.fatura_pagamentos';

    /**
     * Os atributos que são preenchíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fatura_id',
        'data_pagamento',
        'valor_pago',
        'comprovante_path',
        'registrado_por_user_id',
    ];

    /**
     * Define os casts de atributos.
     *
     * @var array
     */
    protected $casts = [
        'data_pagamento' => 'date',
        'valor_pago' => 'decimal:2',
    ];

    /**
     * Relacionamento: Um pagamento pertence a uma fatura.
     */
    public function fatura()
    {
        return $this->belongsTo(Fatura::class, 'fatura_id');
    }

    /**
     * Relacionamento: O usuário que registrou o pagamento.
     */
    public function usuario()
    {
        // Certifique-se que o model User está em 'App\Models\User'
        return $this->belongsTo(\App\Models\User::class, 'registrado_por_user_id');
    }
}