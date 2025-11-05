<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;
    protected $table = 'public.grupo';

    public function grupoPai()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id', 'id');
    }

    /**
     * Relação: Um Grupo (Pai) pode ter muitos Grupos (Subgrupos).
     */
    public function subgrupos()
    {
        return $this->hasMany(Grupo::class, 'grupo_id', 'id');
    }
}
