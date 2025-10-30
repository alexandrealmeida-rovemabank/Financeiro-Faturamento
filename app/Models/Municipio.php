<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    use HasFactory;

    /**
     * Especifica o schema e a tabela a ser utilizada, seguindo o padrão do seu model Empresa.
     *
     * @var string
     */
    protected $table = 'public.municipio';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'uf',
        // Adicione outras colunas da sua tabela 'municipio' aqui, se houver
    ];

    /**
     * Define o relacionamento inverso: um município pode ter muitas empresas.
     * Isso é útil para futuras consultas.
     */
    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'municipio_id');
    }

        public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }
}
