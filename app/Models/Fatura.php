<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Fatura extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'faturas'; // O search_path cuida do schema

    protected $fillable = [
        'cliente_id',
        'numero_fatura',
        'valor_total',
        'valor_impostos',
        'valor_descontos',
        'valor_liquido',
        'data_emissao',
        'data_vencimento',
        'status',
        'observacoes',
        'periodo_fatura'
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_vencimento' => 'date',
    ];

    // Log do Spatie
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'valor_liquido', 'data_vencimento'])
            ->setDescriptionForEvent(fn(string $eventName) => "Fatura {$this->id} foi {$eventName}");
    }

    // Relação: Uma fatura tem muitos itens
    public function itens()
    {
        return $this->hasMany(FaturaItem::class);
    }

    // Relação: Uma fatura pertence a um cliente (Empresa)
    public function cliente()
    {
        // Apontando para o Model Empresa que você já deve ter
        return $this->belongsTo(Empresa::class, 'cliente_id', 'id');
    }
}