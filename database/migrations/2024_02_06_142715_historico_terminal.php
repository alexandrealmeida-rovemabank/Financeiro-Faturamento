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
        Schema::create('historico_terminal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_estoque');
            $table->unsignedBigInteger('id_credenciado');
            $table->string('produto');
            $table->string('acao')->nullable();
            $table->string('data')->nullable();
            $table->string('usuario');
            $table->foreign('id_estoque')->references('id')->on('estoque')->onDelete('cascade');
            $table->foreign('id_credenciado')->references('id')->on('credenciado')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
