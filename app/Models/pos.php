<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POS extends Model
{
    use HasFactory;

    protected $table = 'pos';

    protected $fillable = [
        'codigo',
        'modelo',
        'fornecedor',
        'numero_nota',
        'garantia',
        'ativo',
        'credenciado_id',
        'data_cadastro',
        'data_atualizacao',
        'usuario_cadastro_id',
        'usuario_atualizacao_id',
        'serial',
        'versao',
    ];

    public $timestamps = false;

    // Relacionamento com Credenciado
    public function credenciado()
    {
        return $this->belongsTo(\App\Models\Empresa::class, 'credenciado_id');
    }

    // Relacionamento com usuários
    public function usuarioCadastro()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'usuario_cadastro_id');
    }

    public function usuarioAtualizacao()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'usuario_atualizacao_id');
    }
}
