<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Use o schema correto se necessário
        Schema::table('contas_receber.faturas', function (Blueprint $table) {
            
            // Coluna para armazenar a % da taxa (ex: 5.00 ou -2.00)
            $table->decimal('taxa_adm_percent', 15, 2)->default(0)->after('valor_descontos');
            
            // Coluna para armazenar o valor (R$) calculado
            $table->decimal('taxa_adm_valor', 15, 2)->default(0)->after('taxa_adm_percent');
        });
    }

    public function down()
    {
        Schema::table('contas_receber.faturas', function (Blueprint $table) {
            $table->dropColumn(['taxa_adm_percent', 'taxa_adm_valor']);
        });
    }
};