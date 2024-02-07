<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class historico_terminal extends Model
{
    use HasFactory;

    protected $table = 'historico_terminal'; // ajuste o nome da tabela
    protected $fillable = [
        'id',
        'id_estoque',
        'id_credenciado',
        'produto',
        'acao',
        'data',
        'usuario',
    ];
    public $timestamps = false;

    public function estoque()
    {
        return $this->belongsTo(Estoque::class, 'id_estoque');
    }

    public function credenciado()
    {
        return $this->belongsTo(Credenciado::class, 'id_credenciado');
    }


}
