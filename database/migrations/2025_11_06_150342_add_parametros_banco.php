<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parametros_globais', function (Blueprint $table) {
            $table->string('banco')->nullable()->after('dias_vencimento_privado'); 
            $table->string('agencia')->nullable()->after('banco'); 
            $table->string('conta')->nullable()->after('agencia'); 
            $table->string('chave_pix')->nullable()->after('conta'); 
            $table->string('cnpj')->nullable()->after('chave_pix'); 
            $table->string('razao_social')->nullable()->after('cnpj'); 
        });
    }

    public function down(): void
    {
        Schema::table('parametros_globais', function (Blueprint $table) {
            $table->dropColumn(['banco', 'agencia', 'conta', 'chave_pix', 'cnpj', 'razao_social']);
        });
    }
};
