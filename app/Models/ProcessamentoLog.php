<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessamentoLog extends Model
{
    use HasFactory;

    /**
     * Tabela associada ao model, especificando o schema.
     */
    protected $table = 'contas_receber.processamento_log';

    /**
     * Atributos que são preenchíveis em massa.
     */
    protected $fillable = [
        'comando',
        'inicio_execucao',
        'fim_execucao',
        'status',
        'ultimo_id_processado_antes',
        'ultimo_id_processado_depois',
        'transacoes_copiadas',
        'mensagem_erro',
        'parametros'
    ];

    /**
     * Define os casts de atributos para tipos específicos.
     */
    protected $casts = [
        'inicio_execucao' => 'datetime',
        'fim_execucao' => 'datetime',
        'parametros' => 'array',

    ];
}
