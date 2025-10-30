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
        // Altera a tabela no schema 'contas_receber'
        Schema::table('contas_receber.processamento_log', function (Blueprint $table) {
            // Adiciona uma coluna JSON para guardar os parâmetros
            $table->json('parametros')->nullable()->after('comando');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contas_receber.processamento_log', function (Blueprint $table) {
            $table->dropColumn('parametros');
        });
    }
};
