<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoCategoria extends Model
{
    use HasFactory;

    /**
     * Tabela associada ao model, especificando o schema.
     *
     * @var string
     */
    protected $table = 'public.produto_categoria';

    /**
     * Define que não haverá timestamps (created_at, updated_at) nesta tabela.
     *
     * @var bool
     */
    public $timestamps = false; // Ajuste se a sua tabela tiver timestamps
}
