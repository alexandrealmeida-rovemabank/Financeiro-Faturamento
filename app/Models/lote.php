<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class lote extends Model
{
    use HasFactory;

    protected $table = 'lote'; // ajuste o nome da tabela
    protected $fillable = [
        'id',
        'lote',
        'nf',
        'quantidade',
        'status',
    ];


     public function estoque()
     {
         return $this->hasMany(Estoque::class);
     }

}
