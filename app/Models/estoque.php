<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class estoque extends Model
{
    protected $table = 'estoque'; // ajuste o nome da tabela
    protected $fillable = [
        'id',
        'id_lote',
        'categoria',
        'fabricante',
        'modelo',
        'numero_serie',
        'status',
        'observacao',
        'metodo_cadastro',
        'created_at',
        'updated_at',
    ];

     public function lote()
     {
         return $this->belongsTo(Lote::class,'id_lote');
     }

     public function terminaisVinculados()
    {
        return $this->hasMany(TerminaisVinculados::class);
    }

    public function historicos()
    {
        return $this->hasMany(HistoricoTerminal::class, 'id_estoque');
    }

}
