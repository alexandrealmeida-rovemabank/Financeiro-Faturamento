<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organizacao extends Model
{
    use HasFactory;

    /**
     * Especifica o schema e a tabela a ser utilizada.
     *
     * @var string
     */
    protected $table = 'public.organizacao'; // Presumi o nome da tabela, ajuste se for diferente

    /**
     * Define o relacionamento inverso: uma organização pode ter muitas empresas.
     */
    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'organizacao_id');
    }
}
