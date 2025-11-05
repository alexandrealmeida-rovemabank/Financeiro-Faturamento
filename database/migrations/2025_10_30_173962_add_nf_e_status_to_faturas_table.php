<?php

// database/migrations/YYYY_MM_DD_HHMMSS_add_nf_e_status_to_faturas_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faturas', function (Blueprint $table) {
            $table->string('nota_fiscal')->nullable()->after('numero_fatura');
            
            // Altera o status para suportar os novos valores
            // 'pendente', 'recebida', 'recebida_parcial', 'cancelada'
            $table->string('status', 50)->default('pendente')->change();
        });
    }

    public function down(): void
    {
        Schema::table('faturas', function (Blueprint $table) {
            $table->dropColumn('nota_fiscal');
            $table->string('status', 50)->default('pendente')->change(); // Reverte
        });
    }
};