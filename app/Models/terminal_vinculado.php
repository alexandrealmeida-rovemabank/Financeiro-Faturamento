<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class terminal_vinculado extends Model
{
    use HasFactory;

    protected $table = 'terminal_vinculado'; // ajuste o nome da tabela
    protected $fillable = [
        'id',
        'id_estoque',
        'id_credenciado',
        'status',
        'id_chip',
        'produto',
    ];

    // Relacionamento muitos para um com Credenciado
    public function credenciado()
    {
        return $this->belongsTo(Credenciado::class,'id_credenciado');
    }

    // Relacionamento muitos para um com Estoque
    public function estoque()
    {
        return $this->belongsTo(Estoque::class,'id_estoque');
    }
    public function chip()
    {
        return $this->belongsTo(Estoque::class,'id_chip');
    }

}
