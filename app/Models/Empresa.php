<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; // Esta é a "ferramenta" que faz o log.
use Spatie\Activitylog\LogOptions;          // Esta ajuda a configurar o que será logado.

use App\Models\Municipio;
use App\Models\Organizacao;
use App\Models\Contrato;
use App\Models\Empenho;
use App\Models\ParametroCliente;
use App\Models\TransacaoFaturamento;
use App\Models\Fatura;
class Empresa extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'public.empresa';
    /**
     * A chave primária associada à tabela.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indica se os IDs são auto-incremento.
     *
     * @var bool
     */
    public $incrementing = true;


    // ... (Seu método getActivitylogOptions existente)
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([ 'nome', 'razao_social', 'cnpj' ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $acao = match($eventName) {
                    'created' => 'criado',
                    'updated' => 'atualizado',
                    'deleted' => 'excluído',
                    default => $eventName
                };
                return "Cliente {$this->nome} foi {$acao}";
            })
            ->useLogName('Cliente');
    }

    // --- RELACIONAMENTOS EXISTENTES ---

    public function unidades()
    {
        return $this->hasMany(Empresa::class, 'empresa_id');
    }

    public function matriz()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    public function organizacao()
    {
        return $this->belongsTo(Organizacao::class, 'organizacao_id');
    }

    public function empresaTipo()
    {
        return $this->belongsTo(EmpresaTipo::class, 'empresa_tipo_id');
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class, 'empresa_id');
    }

    /**
     * Corrigido: Relacionamento 1-para-1 com os parâmetros do CLIENTE.
     * (O seu arquivo estava com 'ParametroCliente' em maiúsculo)
     */
    public function parametros()
    {
        return $this->hasOne(ParametroCliente::class, 'empresa_id');
    }

    // App/Models/Empresa.php

    public function parametroCliente()
    {
        return $this->hasOne(ParametroCliente::class, 'empresa_id', 'id');
    }

    // --- RELACIONAMENTOS FALTANTES (A CAUSA DO ERRO) ---

    /**
     * ADICIONADO: Relacionamento 1-para-1 com a tabela de taxas/tarifas.
     * (Baseado no schema public.empresa que tem a coluna 'taxas_id')
     */
    public function taxas()
    {
        // O Model EmpresaTaxas que você enviou está correto.
        return $this->belongsTo(EmpresaTaxas::class, 'taxas_id');
    }

    /**
     * ADICIONADO: Relacionamento 1-para-1 com os parâmetros específicos do CREDENCIADO.
     * (Necessário para o método 'show' do seu CredenciadoController)
     */
    public function parametroCredenciado()
    {
        // O Model ParametroCredenciado que você enviou está correto.
        return $this->hasOne(ParametroCredenciado::class, 'empresa_id');
    }


    public function multitaxas()
    {
        return $this->hasManyThrough(
            EmpresaMultitaxas::class,
            EmpresaTaxas::class,
            'empresa_id',      // FK da empresa_taxas
            'empresa_taxa_id', // FK da empresa_multitaxas
            'id',
            'id'
        );
    }

    public function taxasEspeciais()
    {
        return $this->hasMany(EmpresaTaxasEspeciais::class, 'empresa_id');
    }


    public function pos()
    {
        return $this->hasMany(\App\Models\POS::class, 'credenciado_id');
    }


    /**
     * Relação para buscar as transações PENDENTES de faturamento.
     * (Usada para calcular o VALOR na tabela index)
     */
    public function transacoesPendentes()
    {
        return $this->hasMany(TransacaoFaturamento::class, 'cliente_id', 'id')
                    // CORREÇÃO 1: Usando a coluna 'status' (string)
                    ->whereIn('status', ['confirmada', 'liquidada'])
                    ->whereNull('fatura_id');
    }

    /**
     * Relação para buscar faturas JÁ GERADAS.
     * (Usada para definir o STATUS na tabela index)
     */
    public function faturas()
    {
        return $this->hasMany(Fatura::class, 'cliente_id', 'id');
    }

    // --- INÍCIO DAS NOVAS RELAÇÕES (Req 1) ---

    /**
     * Transações PENDENTES onde esta empresa é a MATRIZ
     * (cliente_id = 1, unidade_id = NULL)
     */
    public function transacoesPendentesMatriz()
    {
        return $this->hasMany(TransacaoFaturamento::class, 'cliente_id', 'id');
    }

    /**
     * Transações PENDENTES onde esta empresa é a UNIDADE
     * (unidade_id = 2)
     */
    public function transacoesPendentesUnidade()
    {
        return $this->hasMany(TransacaoFaturamento::class, 'unidade_id', 'id')
                    ->whereIn('status', ['confirmada', 'liquidada'])
                    ->whereNull('fatura_id');
    }





}
