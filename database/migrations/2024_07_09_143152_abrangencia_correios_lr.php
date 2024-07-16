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
        Schema::create('abrangencia_correios_lr', function (Blueprint $table) {
            $table->id();
            $table->text('unidade_coleta');
            $table->text('dr');
            $table->text('servico');
            $table->text('cep_inicial');
            $table->text('cep_final');
            $table->text('prazo_coleta');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
