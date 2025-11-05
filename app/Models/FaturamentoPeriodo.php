<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaturamentoPeriodo extends Model
{
    use HasFactory;

    protected $table = 'faturamento_periodos';
    protected $fillable = ['cliente_id', 'periodo', 'observacoes'];

    public function cliente()
    {
        return $this->belongsTo(Empresa::class, 'cliente_id', 'id');
    }
}