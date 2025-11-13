<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Assumindo schema 'contas_receber.faturas'
        Schema::table('contas_receber.faturas', function (Blueprint $table) {
            // Garante que a constraint existe antes de dropar
            if (Schema::hasColumn('contas_receber.faturas', 'desconto_manual_user_id')) {
                 // Nomes de constraint podem variar, mas o Laravel default é: table_column_foreign
                 try {
                    $table->dropForeign(['desconto_manual_user_id']);
                 } catch (\Exception $e) {
                    // Ignora se a FK tiver um nome diferente e não puder ser dropada
                 }
            }
            
            $table->dropColumn([
                'desconto_manual_valor',
                'desconto_manual_justificativa',
                'desconto_manual_user_id'
            ]);
        });
    }

    public function down()
    {
        Schema::table('contas_receber.faturas', function (Blueprint $table) {
            $table->decimal('desconto_manual_valor', 15, 2)->default(0);
            $table->string('desconto_manual_justificativa')->nullable();
            $table->foreignId('desconto_manual_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
        });
    }
};