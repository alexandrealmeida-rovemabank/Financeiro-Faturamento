<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ParametroTaxaAliquota extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Tabela associada ao model, especificando o schema.
     *
     * @var string
     */
    protected $table = 'contas_receber.parametro_taxa_aliquota';

    /**
     * Os atributos que são preenchíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'organizacao_id',
        'produto_categoria_id',
        'taxa_aliquota',
    ];

    /**
     * Relacionamento com a Organização (tipo de cliente).
     */
    public function organizacao()
    {
        return $this->belongsTo(Organizacao::class, 'organizacao_id');
    }

    /**
     * Relacionamento com a Categoria de Produto.
     */
    public function produtoCategoria()
    {
        return $this->belongsTo(ProdutoCategoria::class, 'produto_categoria_id');
    }

    /**
     * Configuração do Log de Atividade.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'organizacao_id',
                'produto_categoria_id',
                'taxa_aliquota',
            ])
            ->useLogName('Parâmetro Taxa/Aliquota')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $organizacao = $this->organizacao ? $this->organizacao->nome ?? "ID {$this->organizacao_id}" : "ID {$this->organizacao_id}";
                $categoria = $this->produtoCategoria ? $this->produtoCategoria->nome ?? "ID {$this->produto_categoria_id}" : "ID {$this->produto_categoria_id}";

                $acao = match ($eventName) {
                    'created' => 'criada',
                    'updated' => 'atualizada',
                    'deleted' => 'excluída',
                    default => $eventName
                };

                return "Taxa/Alíquota para a organização {$organizacao} e categoria {$categoria} foi {$acao}";
            });
    }
}
