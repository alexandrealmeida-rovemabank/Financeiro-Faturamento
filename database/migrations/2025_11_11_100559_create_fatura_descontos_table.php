<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Cria a tabela no schema 'contas_receber'
        Schema::create('contas_receber.fatura_descontos', function (Blueprint $table) {
            $table->id();
            
            // Chave estrangeira para contas_receber.faturas
            $table->foreignId('fatura_id')
                  ->constrained('contas_receber.faturas')
                  ->onDelete('cascade'); // Se a fatura for deletada, os descontos também são.

            // Chave estrangeira para public.users
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null'); // Mantém o registro se o usuário for deletado

            $table->enum('tipo', ['fixo', 'percentual']);
            
            // Armazena 100.00 (para R$ 100) ou 10.00 (para 10%)
            $table->decimal('valor', 15, 2); 
            
            $table->string('justificativa')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contas_receber.fatura_descontos');
    }
};