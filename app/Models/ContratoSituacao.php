<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratoSituacao extends Model
{
    use HasFactory;

    /**
     * Especifica o schema e a tabela a ser utilizada.
     *
     * @var string
     */
    protected $table = 'public.contrato_situacao';

    /**
     * Define que não haverá timestamps (created_at, updated_at) nesta tabela.
     *
     * @var bool
     */
    public $timestamps = false;
}
