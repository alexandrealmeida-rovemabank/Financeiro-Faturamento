<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class parametros_correios_cartao extends Model
{
    use HasFactory;


    protected $table = 'parametros_correios_cartao'; // ajuste o nome da tabela
    protected $fillable = [
        'id',
        'cnpj_contrato',
        'num_contrato',
        'num_cartao',
        'cod_administrativo',
    ];
}
