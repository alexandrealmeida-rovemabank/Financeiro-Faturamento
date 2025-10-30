<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ParametroCliente extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'contas_receber.parametro_cliente';

    protected $fillable = [
        'empresa_id',
        'ativar_parametros_globais',
        'descontar_ir_fatura',
        'isento_ir',
        'dias_vencimento',
    ];

    protected $casts = [
        'ativar_parametros_globais' => 'boolean',
        'descontar_ir_fatura' => 'boolean',
        'isento_ir' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'ativar_parametros_globais',
                'descontar_ir_fatura',
                'isento_ir',
                'dias_vencimento',
            ])
            ->useLogName('Parametro Cliente')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $nomeEmpresa = $this->empresa ? $this->empresa->nome : "ID {$this->empresa_id}";
                $acao = match($eventName) {
                    'created' => 'criados',
                    'updated' => 'atualizados',
                    'deleted' => 'excluídos',
                    default => $eventName
                };
                return "Parâmetros do cliente {$nomeEmpresa} foram {$acao}";
            });
    }
}
