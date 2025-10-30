<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    use HasFactory;

    /**
     * Tabela associada ao model.
     *
     * @var string
     */
    protected $table = 'public.contrato';

    /**
     * Define os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'data_inicio' => 'date',
        'data_termino' => 'date',
    ];

    /**
     * Relacionamento com a situação do contrato.
     */
    public function situacao()
    {
        return $this->belongsTo(ContratoSituacao::class, 'contrato_situacao_id');
    }

    /**
     * Relacionamento com a modalidade do contrato.
     */
    public function modalidade()
    {
        return $this->belongsTo(ContratoModalidade::class, 'contrato_modalidade_id');
    }

    /**
     * Relacionamento com os empenhos associados a este contrato.
     */
    public function empenhos()
    {
        return $this->hasMany(Empenho::class, 'contrato_id');
    }
}