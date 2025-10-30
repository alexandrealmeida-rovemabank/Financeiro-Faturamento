<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratoModalidade extends Model
{
    use HasFactory;
    protected $table = 'public.contrato_modalidade';
    public $timestamps = false;
}
