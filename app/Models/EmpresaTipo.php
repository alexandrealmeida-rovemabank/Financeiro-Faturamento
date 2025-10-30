<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaTipo extends Model
{
    use HasFactory;

    /**
     * Especifica o schema e a tabela a ser utilizada.
     *
     * @var string
     */
    protected $table = 'public.empresa_tipo'; // Presumi o nome da tabela, ajuste se for diferente

    /**
     * Define o relacionamento inverso: um tipo pode ser usado por muitas empresas.
     */
    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'empresa_tipo_id');
    }
}
