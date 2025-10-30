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
        Schema::create('contas_receber.parametro_taxa_aliquota', function (Blueprint $table) {
            $table->id();

            // Chave estrangeira para organizacao (tipo de cliente)
            $table->foreignId('organizacao_id')
                  ->constrained('public.organizacao') // Tabela no schema public
                  ->onDelete('cascade');

            // ALTERAÇÃO: Chave estrangeira para categoria de produto
            $table->foreignId('produto_categoria_id')
                  ->constrained('public.produto_categoria') // Tabela no schema public
                  ->onDelete('cascade');

            // Taxa/Alíquota
            $table->decimal('taxa_aliquota', 8, 4); // Ex: 0.0150 para 1.5%

            $table->timestamps();

            // ALTERAÇÃO: Garante que a combinação de organização e CATEGORIA seja única
            $table->unique(['organizacao_id', 'produto_categoria_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_receber.parametro_taxa_aliquota');
    }
};

