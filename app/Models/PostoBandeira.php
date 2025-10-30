<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostoBandeira extends Model
{
    use HasFactory;

    protected $table = 'posto_bandeira';

    protected $fillable = [
        'nome',
    ];

    public $timestamps = false;

    // Relacionamento: um posto/bandeira pode ter várias taxas
    public function empresaTaxas()
    {
        return $this->hasMany(EmpresaTaxas::class, 'posto_bandeira_id');
    }
}
