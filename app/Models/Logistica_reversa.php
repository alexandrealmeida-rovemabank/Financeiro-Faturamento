<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logistica_reversa extends Model
{
    use HasFactory;

    protected $table = 'postagem_reversa'; // ajuste o nome da tabela
    protected $fillable = [
        'id',
        'contrato',
        'num_cartao',
        'servico',
        'cnpj_remetente',
        'email_remetente',
        'nome_fantasia_remetente',
        'cep_remetente',
        'logradouro_remetente',
        'numero_remetente',
        'bairro_remetente',
        'cidade_remetente',
        'estado_remetente',
        'cnpj_destinatario',
        'email_destinatario',
        'nome_fantasia_destinatario',
        'cep_destinatario',
        'logradouro_destinatario',
        'numero_destinatario',
        'bairro_destinatario',
        'cidade_destinatario',
        'estado_destinatario',
        'tipo_coleta',
        'valor_declarado',
        'qtd_item',
        'descricao_obj',
        'num_coleta',
        'num_etiqueta',
        'status_objeto',
        'desc_status_objeto',
        'prazo',
        'data_solicitacao',
        'hora_solictacao',
        'produto',
        'ar',
        'complemento_remetente',
        'complemento_destinatario',
        'ddd_destinatario',
        'celular_destinatario',
        'dddt_destinatario',
        'telefone_destinatario',
        'ddd_remetente',
        'celular_remetente',
        'dddt_remetente',
        'telefone_remetente',
    ];
}
