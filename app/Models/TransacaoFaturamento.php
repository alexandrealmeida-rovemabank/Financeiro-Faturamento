<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransacaoFaturamento extends Model
{
    use HasFactory;

    /**
     * Tabela associada ao model, especificando o schema.
     */
    protected $table = 'contas_receber.transacao_faturamento';

    /**
     * Indica que o ID não é auto-incrementável, pois usamos o ID da transação original.
     */
    public $incrementing = false;

    /**
     * Define a chave primária da tabela.
     */
    protected $primaryKey = 'id';

    /**
     * Define os atributos que são preenchíveis em massa.
     * Usar $guarded = [] permite preencher todas as colunas. Cuidado em produção.
     * Alternativamente, liste todas as colunas em $fillable.
     */
    protected $guarded = [];

    /**
     * Define os casts de atributos para tipos específicos.
     */

    protected $fillable = [
        'data_transacao',
        'data_atualizacao_original',
        'data_sincronizacao_mapa',
        'informacao', // JSON
        'quantidade',
        'valor_unitario',
        'valor_total',
        'imposto_renda',
        'taxa_administrativa',
        'taxa_administrativa_credenciado',
        'desconto',
        'valor_liquido_cliente',
        'valor_taxa_cliente',
        'km_atual',
        'distancia_percorrida',
        'consumo_medio',
        'latitude',
        'longitude',
        'valor_faturado',
    ];


    protected $casts = [
        'data_transacao' => 'datetime',
        'data_atualizacao_original' => 'datetime',
        'data_sincronizacao_mapa' => 'datetime',
        'informacao' => 'array', // Converte a coluna JSON para array PHP
        'quantidade' => 'float',
        'valor_unitario' => 'float',
        'valor_total' => 'float',
        'imposto_renda' => 'float',
        'taxa_administrativa' => 'float',
        'taxa_administrativa_credenciado' => 'float',
        'desconto' => 'float',
        'valor_liquido_cliente' => 'float',
        'valor_taxa_cliente' => 'float',
        'km_atual' => 'float',
        'distancia_percorrida' => 'float',
        'consumo_medio' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'valor_faturado' => 'float',
    ];

    /**
     * Relacionamento com a Fatura (a ser criada).
     */
    // public function fatura()
    // {
    //     // Ajuste o nome do Model Fatura se for diferente
    //     return $this->belongsTo(Fatura::class, 'fatura_id');
    // }
    public function getValorPendenteAttribute()
    {
        // Garante que o valor pendente nunca seja negativo
        return max(0, $this->attributes['valor_total'] - $this->attributes['valor_faturado']);
    }

    /**
     * Relacionamento com o Cliente (Empresa).
     */
    public function cliente()
    {
        return $this->belongsTo(Empresa::class, 'cliente_id');
    }

    /**
     * Relacionamento com a Unidade (Empresa).
     */
    public function unidade()
    {
        return $this->belongsTo(Empresa::class, 'unidade_id');
    }

    /**
     * Relacionamento com o Credenciado (Empresa).
     */
    public function credenciado()
    {
        return $this->belongsTo(Empresa::class, 'credenciado_id');
    }

     /**
     * Relacionamento com o Produto.
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

     /**
     * Relacionamento com o Veículo.
     */
    public function veiculo()
    {
        // Certifique-se que o Model Veiculo existe
        return $this->belongsTo(Veiculo::class, 'veiculo_id');
    }

    /**
     * Relacionamento com o Motorista.
     */
    // public function motorista()
    // {
    //     // Certifique-se que o Model Motorista existe
    //     return $this->belongsTo(Motorista::class, 'motorista_id');
    // }

    /**
     * Relacionamento com o Empenho.
     */
    public function empenho()
    {
        return $this->belongsTo(Empenho::class, 'empenho_id');
    }

     /**
     * Relacionamento com o Contrato.
     */
    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    /**
     * Relacionamento com o Usuário que cadastrou originalmente.
     */
    public function usuarioCadastro()
    {
        return $this->belongsTo(User::class, 'usuario_cadastro_id');
    }

    /**
     * Relacionamento com o Usuário que atualizou originalmente.
     */
    public function usuarioAtualizacao()
    {
        return $this->belongsTo(User::class, 'usuario_atualizacao_id');
    }

    // Adicione outros relacionamentos conforme necessário (ex: cartao, pos)

    // ... dentro de App\Models\TransacaoFaturamento

    // Relação: Uma transação pode pertencer a uma fatura
    public function fatura()
    {
        return $this->belongsTo(Fatura::class);
    }

}

