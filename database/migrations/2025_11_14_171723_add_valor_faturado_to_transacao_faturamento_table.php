<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValorFaturadoToTransacaoFaturamentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transacao_faturamento', function (Blueprint $table) {
            // Adiciona a coluna para rastrear o valor cumulativo faturado
            $table->decimal('valor_faturado', 15, 2)
                  ->default(0.00)
                  ->after('valor_total')
                  ->comment('Valor cumulativo que já foi faturado desta transação');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transacao_faturamento', function (Blueprint $table) {
            $table->dropColumn('valor_faturado');
        });
    }
}