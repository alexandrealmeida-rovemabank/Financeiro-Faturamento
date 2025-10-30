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
        Schema::create('contas_receber.parametro_credenciado', function (Blueprint $table) {
            $table->id();

            // Relacionamento 1-para-1 com a tabela 'empresa'
            $table->foreignId('empresa_id')
                  ->constrained('public.empresa') // Aponta para a tabela no schema 'public'
                  ->onUpdate('cascade')
                  ->onDelete('cascade')
                  ->unique(); // Garante 1-para-1

            // Parâmetro específico
            $table->boolean('isencao_irrf')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contas_receber.parametro_credenciado');
    }
};
