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
        Schema::create('impressao', function (Blueprint $table) {
            $table->id();
            $table->string('placa');
            $table->string('modelo');
            $table->string('combustivel');
            $table->string('trilha');
            $table->string('numero_cartao');
            $table->string('cliente');
            $table->string('gruposubgrupo');
            $table->unsignedBigInteger('id_lote_impressao');
            $table->timestamps();

            $table->foreign('id_lote_impressao')->references('id')->on('lote_impressao')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impressao');
    }
};
