<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Assumindo que sua tabela faturas está no schema 'contas_receber'
        // Se não estiver, remova o prefixo 'contas_receber.'
        Schema::table('contas_receber.faturas', function (Blueprint $table) {
            
            // Valor do desconto manual
            $table->decimal('desconto_manual_valor', 15, 2)->default(0)->after('valor_descontos');
            
            // Justificativa do desconto
            $table->string('desconto_manual_justificativa')->nullable()->after('desconto_manual_valor');
            
            // Usuário que aplicou o desconto
            // Aponta para a tabela 'public.users'
            $table->foreignId('desconto_manual_user_id')
                  ->nullable()
                  ->after('desconto_manual_justificativa')
                  ->constrained('users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('contas_receber.faturas', function (Blueprint $table) {
            $table->dropForeign(['desconto_manual_user_id']);
            $table->dropColumn([
                'desconto_manual_valor',
                'desconto_manual_justificativa',
                'desconto_manual_user_id'
            ]);
        });
    }
};