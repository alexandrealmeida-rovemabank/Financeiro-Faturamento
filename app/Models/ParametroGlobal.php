<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ParametroGlobal extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Tabela associada ao model, especificando o schema.
     *
     * @var string
     */
    protected $table = 'contas_receber.parametros_globais';

    /**
     * Os atributos que são preenchíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'descontar_ir_fatura',
        'dias_vencimento_publico',
        'dias_vencimento_privado',
    ];

    /**
     * Define os casts de atributos para tipos específicos.
     *
     * @var array
     */
    protected $casts = [
        'descontar_ir_fatura' => 'boolean',
    ];

    /**
     * Configuração do Log de Atividade.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'descontar_ir_fatura',
                'dias_vencimento_publico',
                'dias_vencimento_privado',
            ])
            ->useLogName('Parâmetro Global')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $acao = match ($eventName) {
                    'created' => 'criados',
                    'updated' => 'atualizados',
                    'deleted' => 'excluídos',
                    default => $eventName
                };
                return "Parâmetros globais foram {$acao}";
            });
    }
}
