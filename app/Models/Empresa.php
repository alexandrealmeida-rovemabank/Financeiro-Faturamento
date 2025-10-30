<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; // Esta é a "ferramenta" que faz o log.
use Spatie\Activitylog\LogOptions;          // Esta ajuda a configurar o que será logado.

class Empresa extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'public.empresa';

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

}
