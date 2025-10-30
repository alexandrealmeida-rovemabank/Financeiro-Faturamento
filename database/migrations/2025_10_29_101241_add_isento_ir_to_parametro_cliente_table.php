<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contas_receber.parametro_cliente', function (Blueprint $table) {
            $table->boolean('isento_ir')->default(false)->after('descontar_ir_fatura');
        });
    }

    public function down(): void
    {
        Schema::table('contas_receber.parametro_cliente', function (Blueprint $table) {
            $table->dropColumn('isento_ir');
        });
    }
};
