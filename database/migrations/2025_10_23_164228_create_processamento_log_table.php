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
        // Cria a tabela no schema 'contas_receber'
        Schema::create('contas_receber.processamento_log', function (Blueprint $table) {
            $table->id();
            $table->string('comando')->default('faturamento:processar-transacoes'); // Nome do comando executado
            $table->timestamp('inicio_execucao');
            $table->timestamp('fim_execucao')->nullable();
            $table->string('status')->default('iniciado'); // Ex: iniciado, sucesso, falha
            $table->bigInteger('ultimo_id_processado_antes')->nullable(); // ID da última transação antes desta execução
            $table->bigInteger('ultimo_id_processado_depois')->nullable(); // ID da última transação após esta execução
            $table->integer('transacoes_copiadas')->default(0);
            $table->text('mensagem_erro')->nullable(); // Para registrar exceções
            $table->timestamps(); // created_at e updated_at do log
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_receber.processamento_log');
    }
};
