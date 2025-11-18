<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUniqueConstraintFromFaturaItensTable extends Migration
{
    /**
     * O nome da sua restrição.
     * Verifique se é este mesmo no seu banco.
     */
    private $constraintName = 'fatura_itens_transacao_faturamento_id_unique';

    public function up()
    {
        Schema::table('fatura_itens', function (Blueprint $table) {
            // Remove a restrição que impede faturamento parcial
            $table->dropUnique($this->constraintName);
        });
    }

    public function down()
    {
        Schema::table('fatura_itens', function (Blueprint $table) {
            // Adiciona de volta se precisar reverter
            $table->unique('transacao_faturamento_id', $this->constraintName);
        });
    }
}