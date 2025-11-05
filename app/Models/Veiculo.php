<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    use HasFactory;

    protected $table = 'public.veiculo';

    /**
     * Relação: Um Veículo pertence a um Grupo (o "Subgrupo" do seu exemplo).
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id', 'id');
    }
}