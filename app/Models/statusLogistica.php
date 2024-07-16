<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class statusLogistica extends Model
{
    use HasFactory;

    protected $table = 'statusLogistica'; // ajuste o nome da tabela
    protected $fillable = [
        'id',
        'status',
        'descricao_status',
        'data_atualizacao',
        'hora_atualizacao',
        'observacao',
        'numero_pedido',

    ];
}
