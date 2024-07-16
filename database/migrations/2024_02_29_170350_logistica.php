<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('parametros_correios_cartao', function (Blueprint $table) {
            $table->id();
            $table->text('cnpj_contrato');
            $table->text('num_contrato')->unique();
            $table->text('num_cartao')->unique();
            $table->text('cod_administrativo');
            $table->timestamps();
        });


        Schema::create('postagem_reversa', function (Blueprint $table) {
            $table->id();
            $table->text('contrato')->nullable();
            $table->text('num_cartao')->nullable();
            $table->text('produto')->nullable();
            $table->text('servico')->nullable();
            $table->text('cnpj_remetente')->nullable();
            $table->text('email_remetente')->nullable();
            $table->text('nome_fantasia_remetente')->nullable();
            $table->text('cep_remetente')->nullable();
            $table->text('logradouro_remetente')->nullable();
            $table->text('numero_remetente')->nullable();
            $table->text('complemento_remetente')->nullable();
            $table->text('bairro_remetente')->nullable();
            $table->text('cidade_remetente')->nullable();
            $table->text('estado_remetente')->nullable();
            $table->text('ddd_remetente')->nullable();
            $table->text('celular_remetente')->nullable();
            $table->text('dddt_remetente')->nullable();
            $table->text('telefone_remetente')->nullable();
            $table->text('cnpj_destinatario')->nullable();
            $table->text('email_destinatario')->nullable();
            $table->text('nome_fantasia_destinatario')->nullable();
            $table->text('cep_destinatario')->nullable();
            $table->text('logradouro_destinatario')->nullable();
            $table->text('numero_destinatario')->nullable();
            $table->text('complemento_destinatario')->nullable();
            $table->text('bairro_destinatario')->nullable();
            $table->text('cidade_destinatario')->nullable();
            $table->text('estado_destinatario')->nullable();
            $table->text('ddd_destinatario')->nullable();
            $table->text('celular_destinatario')->nullable();
            $table->text('dddt_destinatario')->nullable();
            $table->text('telefone_destinatario')->nullable();
            $table->text('tipo_coleta')->nullable();
            $table->text('valor_declarado')->nullable();
            $table->text('ar')->nullable();
            $table->text('qtd_item')->nullable();
            $table->text('descricao_obj')->nullable();
            $table->text('num_coleta')->nullable();
            $table->text('num_etiqueta')->nullable();
            $table->text('status_objeto')->nullable();
            $table->text('desc_status_objeto')->nullable();
            $table->text('prazo')->nullable()->nullable();
            $table->text('data_solicitacao')->nullable();
            $table->text('hora_solictacao')->nullable();
            $table->text('caixa')->nullable();
            $table->timestamps();
        });

        DB::table('parametros_correios_cartao')->insert([
            'cnpj_contrato' => '05884660000104',
            'num_contrato' => '9912402878',
            'num_cartao' => '0077498895',
            'cod_administrativo' => '16265297',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('parametros_correios_cartao')->insert([
            'cnpj_contrato' => '32192325000100',
            'num_contrato' => '9912610936',
            'num_cartao' => '0077786939',
            'cod_administrativo' => '23175737',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('statusLogistica', function (Blueprint $table) {
            $table->id();
            $table->text('status');
            $table->text('descricao_status');
            $table->text('data_atualizacao');
            $table->text('hora_atualizacao');
            $table->text('observacao')->nullable();
            $table->text('numero_pedido');
            $table->timestamps();
        });


    }




    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametros_correios_cartao');
    }
};
