<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'imagem_perfil',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function adminlte_image()
    {
        $user_auth = auth()->user();
        return $user_auth->imagem_perfil
            ? asset('storage/' . $user_auth->imagem_perfil)
            : asset('vendor/adminlte/dist/img/user.png');
    }

    public function adminlte_profile_url()
    {
        return 'profile';
    }

    /**
     * Configuração do Log de Atividade.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'imagem_perfil'])
            ->useLogName('Usuário')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $acao = match ($eventName) {
                    'created' => 'criado',
                    'updated' => 'atualizado',
                    'deleted' => 'excluído',
                    'edited' => 'editado',
                    default => $eventName
                };
                return "Usuário {$this->name} foi {$acao}";
            });
    }
}
