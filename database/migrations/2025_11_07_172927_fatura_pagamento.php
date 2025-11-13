<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Cria a tabela no schema 'contas_receber'
        Schema::create('contas_receber.fatura_pagamentos', function (Blueprint $table) {
            $table->id();
            
            // Chave estrangeira para contas_receber.faturas
            $table->foreignId('fatura_id')
                  ->constrained('contas_receber.faturas')
                  ->onDelete('cascade'); // Se a fatura for deletada, os pagamentos também são.

            $table->date('data_pagamento');
            $table->decimal('valor_pago', 15, 2);
            
            $table->string('comprovante_path')->nullable(); // Caminho para o arquivo
            
            // Chave estrangeira para public.users
            $table->foreignId('registrado_por_user_id')
                  ->nullable()
                  ->constrained('contas_receber.users')
                  ->onDelete('set null'); // Mantém o registro se o usuário for deletado

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contas_receber.fatura_pagamentos');
    }
};