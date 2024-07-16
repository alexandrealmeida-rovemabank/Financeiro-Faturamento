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
        Schema::create('logistica_juma', function (Blueprint $table) {
            $table->id();
            $table->text('num_pedido')->unique();
            $table->text('num_os')->unique();
            $table->text('qtd_destino');
            $table->float('valor');
            $table->text('produto');
            $table->text('retorno');
            $table->text('observacao');
            $table->text('recibo');
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
