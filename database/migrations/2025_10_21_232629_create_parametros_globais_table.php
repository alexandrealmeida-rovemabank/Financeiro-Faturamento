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
        // Garante que a tabela seja criada no schema correto
        Schema::create('contas_receber.parametros_globais', function (Blueprint $table) {
            $table->id();

            // Parâmetros gerais
            $table->boolean('descontar_ir_fatura')->default(false)->comment('Define se o IR deve ser descontado globalmente.');

            // Novos campos de vencimento
            $table->integer('dias_vencimento_publico')->default(30)->comment('Prazo padrão em dias para clientes públicos.');
            $table->integer('dias_vencimento_privado')->default(15)->comment('Prazo padrão em dias para clientes privados.');

            // Campos removidos (se estava na migration original)
            // $table->boolean('vencimento_fatura_personalizado')->default(false); // Removido
            // $table->integer('dias_vencimento')->nullable(); // Removido

            $table->timestamps();
        });

        // Adiciona um registro inicial com os valores padrão (opcional, mas recomendado)
        \Illuminate\Support\Facades\DB::table('contas_receber.parametros_globais')->insert([
            'descontar_ir_fatura' => false,
            'dias_vencimento_publico' => 30,
            'dias_vencimento_privado' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametros_globais');
    }
};
