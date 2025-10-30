<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // CORREÇÃO: Cria a tabela no schema 'contas_receber'
        Schema::create('parametro_cliente', function (Blueprint $table) {
            $table->id();

            // Chave estrangeira para a tabela de empresas (clientes) no schema 'public'
            $table->foreignId('empresa_id')
                  ->constrained('public.empresa') // Aponta para a tabela 'empresa' no schema 'public'
                  ->onUpdate('cascade')
                  ->onDelete('cascade')
                  ->unique(); // Garante o relacionamento 1-para-1

            // Parâmetros do cliente
            $table->boolean('ativar_parametros_globais')->default(true);
            $table->boolean('descontar_ir_fatura')->default(false);
            $table->boolean('vencimento_fatura_personalizado')->default(false);
            $table->integer('dias_vencimento')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // CORREÇÃO: Remove a tabela do schema 'contas_receber'
        Schema::dropIfExists('parametro_cliente');
    }
};
