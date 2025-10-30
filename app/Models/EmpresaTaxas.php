<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaTaxas extends Model
{
    use HasFactory;

    /**
     * Tabela associada ao model, especificando o schema.
     *
     * @var string
     */
    protected $table = 'public.empresa_taxas';
    protected $fillable = [
        'periodo_recebimento',
        'taxa_transferencia',
        'taxa_administrativa',
        'taxa_antecipacao_automatica',
        'taxa_antecipacao',
        'aluguel_pos',
        'ciclo',
        'qtd_dias',
        'prazo_nota_fiscal',
        'simples_nacional',
        'condicao_recebimento',
        'dias_antecedencia',
        'data_inicio_ciclo',
        'termino_periodo_gratuito',
        'aluguel',
        'preco',
        'data_cadastro',
        'data_atualizacao',
        'ciclo_asto',
        'periodo_recebimento_asto',
        'taxa_adesao',
        'taxa_manutencao',
    ];
    public $timestamps = false;

    /**
     * Relacionamento 1-para-M com EmpresaMultitaxas
     * (Necessário para a tela de 'show' que você criou)
     */
    public function multitaxas()
    {
        return $this->hasMany(EmpresaMultitaxas::class, 'empresa_taxa_id');
    }

    /**
     * Relacionamento 1-para-M com EmpresaTaxasEspeciais
     * (Necessário para a tela de 'show' que você criou)
     */
    public function taxasEspeciais()
    {
        return $this->hasMany(EmpresaTaxasEspeciais::class, 'empresa_taxa_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

}