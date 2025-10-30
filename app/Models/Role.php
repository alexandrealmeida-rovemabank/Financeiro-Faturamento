<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends SpatieRole
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name'])
            ->useLogName('Perfil de Acesso (Role)')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $acao = match ($eventName) {
                    'created' => 'criado',
                    'updated' => 'atualizado',
                    'deleted' => 'excluído',
                    default => $eventName
                };

                return "Perfil de acesso '{$this->name}' foi {$acao}";
            });
    }

    /**
     * Força o log também quando as permissões forem alteradas,
     * mesmo que o nome não tenha mudado.
     */
    protected static function booted()
    {
        static::saved(function ($model) {
            if ($model->wasChanged() || request()->has('permissions')) {

                activity('Perfil de Acesso (Role)') // <-- Aqui define o log name
                    ->performedOn($model)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'permissions' => $model->permissions->pluck('name')->toArray()
                    ])
                    ->log("Permissões do perfil '{$model->name}' foram atualizadas");
            }
        });
    }

}
