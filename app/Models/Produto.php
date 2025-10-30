<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    /**
     * Tabela associada ao model, especificando o schema.
     *
     * @var string
     */
    protected $table = 'public.produto';

    /**
     * Os atributos que são preenchíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'atalho',
        'produto_categoria_id',
        'ativo',
        'aliquota_ir',
        'empresa_id',
        'numero',
        'emissao_carbono',
        // Adicione 'usuario_cadastro_id' e 'usuario_atualizacao_id' se forem gerenciados pelo Eloquent
    ];

    /**
     * Define os casts de atributos para tipos específicos.
     *
     * @var array
     */
    protected $casts = [
        'ativo' => 'boolean',
        'aliquota_ir' => 'float',
        'emissao_carbono' => 'decimal:2', // Ajuste a precisão conforme necessário
    ];

    /**
     * Relacionamento com a Categoria do Produto.
     */
    public function categoria()
    {
        // Certifique-se que o Model ProdutoCategoria existe
        return $this->belongsTo(ProdutoCategoria::class, 'produto_categoria_id');
    }

    /**
     * Relacionamento com as Taxas/Alíquotas.
     */
    public function taxasAliquota()
    {
        return $this->hasMany(ParametroTaxaAliquota::class, 'produto_id');
    }

    // Se a tabela 'produto' não usar os timestamps padrão 'created_at' e 'updated_at',
    // descomente a linha abaixo. Pelo schema, ela usa 'data_cadastro' e 'data_atualizacao'.
    // public $timestamps = false;

    // Se você quiser que o Eloquent gerencie 'data_cadastro' e 'data_atualizacao' automaticamente,
    // adicione as constantes abaixo. Caso contrário, você terá que preenchê-las manualmente.
    // const CREATED_AT = 'data_cadastro';
    // const UPDATED_AT = 'data_atualizacao';
}
