<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametroCredenciado extends Model
{
    use HasFactory;

    protected $table = 'contas_receber.parametro_credenciado';

    protected $fillable = [
        'empresa_id',
        'isencao_irrf',
    ];

    protected $casts = [
        'isencao_irrf' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
