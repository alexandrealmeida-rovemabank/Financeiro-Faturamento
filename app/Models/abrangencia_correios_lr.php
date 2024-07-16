<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class abrangencia_correios_lr extends Model
{
    use HasFactory;
    protected $table = 'abrangencia_correios_lr';
    protected $fillable = ['unidade_coleta', 'dr', 'servico', 'cep_inicial', 'cep_final', 'prazo_coleta'];
}
