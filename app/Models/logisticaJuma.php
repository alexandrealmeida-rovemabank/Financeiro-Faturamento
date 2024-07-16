<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class logisticaJuma extends Model
{
    use HasFactory;

    protected $table = 'logistica_juma'; // ajuste o nome da tabela
    protected $fillable = [
        'id',
        'num_pedido',
        'num_os',
        'qtd_destino',
        'valor',
        'produto',
        'retorno',
        'observacao',
        'recibo',
    ];
}
