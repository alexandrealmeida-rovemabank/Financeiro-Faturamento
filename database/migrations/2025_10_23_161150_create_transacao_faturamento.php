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
        Schema::create('contas_receber.transacao_faturamento', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();

            // Chave estrangeira para a futura tabela de faturas (COMENTADA POR ENQUANTO)
            // Descomente e ajuste o nome da tabela 'faturas' se necessário, em uma migration futura.
            // $table->foreignId('fatura_id')->nullable()->constrained('contas_receber.faturas')->onDelete('set null');
            $table->unsignedBigInteger('fatura_id')->nullable()->index(); // Cria a coluna sem a constraint por agora

            $table->string('status_faturamento')->default('pendente')->index();

            // --- Colunas copiadas/replicadas da public.transacao ---
            $table->unsignedBigInteger('cartao_id')->nullable();
            $table->unsignedBigInteger('cliente_id')->index();
            $table->unsignedBigInteger('unidade_id')->nullable();
            $table->unsignedBigInteger('veiculo_id')->index();
            $table->unsignedBigInteger('motorista_id');
            $table->unsignedBigInteger('credenciado_id')->index();
            $table->unsignedBigInteger('produto_id');
            $table->unsignedBigInteger('empenho_id')->nullable()->index();
            $table->unsignedBigInteger('contrato_id')->nullable()->index();
            $table->unsignedBigInteger('pos_id')->nullable();
            $table->unsignedBigInteger('faturamento_id_cliente')->nullable()->index();
            $table->unsignedBigInteger('faturamento_id_credenciado')->nullable()->index();
            $table->double('quantidade');
            $table->double('valor_unitario');
            $table->double('valor_total');
            $table->double('imposto_renda')->nullable();
            $table->double('taxa_administrativa');
            $table->double('taxa_administrativa_credenciado')->nullable();
            $table->integer('tipo_taxa_administrativa_credenciado')->nullable();
            $table->double('desconto')->nullable();
            $table->double('valor_liquido_cliente')->nullable();
            $table->double('valor_taxa_cliente')->nullable();
            $table->double('km_atual')->nullable();
            $table->double('distancia_percorrida')->nullable();
            $table->double('consumo_medio')->nullable();
            $table->integer('intervalo')->nullable();
            $table->string('nota_fiscal')->nullable();
            $table->string('terminal')->nullable();
            $table->string('status');
            $table->string('justificativa_cancelamento')->nullable();
            $table->unsignedBigInteger('usuario_cadastro_id')->nullable();
            $table->unsignedBigInteger('usuario_atualizacao_id')->nullable();
            $table->string('pos_rrn')->nullable();
            $table->string('pos_stan')->nullable();
            $table->jsonb('informacao')->nullable();
            $table->string('status_localizacao')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->timestamp('data_transacao');
            $table->timestamp('data_atualizacao_original')->nullable();
            $table->timestamp('data_sincronizacao_mapa')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_receber.transacao_faturamento');
    }
};

