<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaTaxasEspeciais extends Model
{
    use HasFactory;

    protected $table = 'public.empresa_taxas_especiais';

    /**
     * Relacionamento para buscar o Cliente associado a esta taxa especial.
     */
    public function cliente()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
