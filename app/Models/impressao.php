<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class impressao extends Model
{
    use HasFactory;
    protected $table = 'impressao'; // ajuste o nome da tabela
    protected $fillable = [
       'id',
       'placa',
       'modelo',
       'combustivel',
       'trilha',
       'numero_cartao',
       'cliente',
       'gruposubgrupo',
      'id_lote_impressao',
    ];



    public function loteImpressao()
    {
        return $this->belongsTo(LoteImpressao::class, 'id_lote_impressao');
    }
}
