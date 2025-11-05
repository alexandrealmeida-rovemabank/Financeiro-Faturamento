<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_fatura_itens_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fatura_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fatura_id')->constrained('faturas')->onDelete('cascade');
            
            // Link para a transação original
            $table->unsignedBigInteger('transacao_faturamento_id')->unique(); // Garante que uma transação só entre em uma fatura
            
            $table->string('descricao_produto');
            $table->unsignedBigInteger('produto_id'); // Referência a public.produto
            $table->unsignedBigInteger('produto_categoria_id'); // Referência a public.produto_categoria

            $table->decimal('quantidade', 15, 4);
            $table->decimal('valor_unitario', 15, 4);
            $table->decimal('valor_subtotal', 15, 2); // qtd * valor_unitario
            
            $table->decimal('aliquota_aplicada', 5, 2)->nullable(); // % da taxa
            $table->decimal('valor_imposto', 15, 2)->default(0.00); // Valor calculado
            
            $table->decimal('valor_total_item', 15, 2); // subtotal + imposto

            $table->timestamps();

            $table->foreign('transacao_faturamento_id')->references('id')->on('transacao_faturamento');
            $table->foreign('produto_id')->references('id')->on('public.produto');
            $table->foreign('produto_categoria_id')->references('id')->on('public.produto_categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fatura_itens');
    }
};