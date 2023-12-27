<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class lote_impressao extends Model
{
    use HasFactory;

    protected $table = 'lote_impressao'; // ajuste o nome da tabela
    protected $fillable = [
        'id',
        'lote',
        'cliente',
        'data_importacao',
        'data_alteracao',
        'status_impressao',
    ];

    public function impressoes()
    {
        return $this->hasMany(Impressao::class, 'id_lote_impressao');
    }


}
