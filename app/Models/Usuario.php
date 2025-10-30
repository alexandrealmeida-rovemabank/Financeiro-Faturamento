<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Para login/autenticação
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'public.usuario'; // ajuste o schema se necessário

    protected $fillable = [
        'apelido',
        'nome',
        'email',
        'telefone',
        'funcao',
        'senha',
        'ativo',
        'auth_key',
        'departamento_id',
        'email_confirmado',
        'codigo_recuperacao',
        'novo_email',
        'data_cadastro',
        'data_atualizacao',
        'usuario_cadastro_id',
        'usuario_atualizacao_id',
        'perfil_id',
        'data_aceite_termo'
    ];

    protected $hidden = [
        'senha',
        'auth_key',
        'codigo_recuperacao',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'email_confirmado' => 'boolean',
        'data_cadastro' => 'datetime',
        'data_atualizacao' => 'datetime',
        'data_aceite_termo' => 'datetime',
    ];

    /**
     * Ajuste o campo de senha para bcrypt automático
     */
    protected function senha(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => !empty($value) ? bcrypt($value) : null
        );
    }

    /**
     * Relacionamentos
     */
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'perfil_id');
    }

    public function usuarioCadastro()
    {
        return $this->belongsTo(Usuario::class, 'usuario_cadastro_id');
    }

    public function usuarioAtualizacao()
    {
        return $this->belongsTo(Usuario::class, 'usuario_atualizacao_id');
    }
}
