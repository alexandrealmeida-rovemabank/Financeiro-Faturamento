<?php

// app/Models/Credenciado.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credenciado extends Model
{
    protected $table = 'credenciado'; // ajuste o nome da tabela
    protected $fillable = [
        'id',
        'cnpj',
        'nome_fantasia',
        'razao_social',
        'cep',
        'endereco',
        'bairro',
        'numero',
        'cidade',
        'estado',
        'status',
        'produto',
        'telefone',
        'celular',
    ];
    protected $casts = ['produto' => 'json'];

    public function getCnpjFormattedAttribute()
    {
        $cnpj = $this->attributes['cnpj'];
        return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
    }
    public function setCnpjAttribute($value)
    {
        $this->attributes['cnpj'] = preg_replace('/[^0-9]/', '', $value);
    }
    public function terminaisVinculados()
    {
        return $this->hasMany(TerminaisVinculados::class);
    }
    public function historicos()
    {
        return $this->hasMany(HistoricoTerminal::class, 'id_credenciado');
    }
}
