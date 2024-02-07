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
        Schema::create('estoque', function (Blueprint $table) {
            $table->id();
            $table->string('categoria');
            $table->string('fabricante');
            $table->string('modelo');
            $table->string('numero_serie')->unique();
            $table->string('status');
            $table->text('observacao');
            $table->text('metodo_cadastro');
            $table->unsignedBigInteger('id_lote');
            $table->timestamps();

            $table->foreign('id_lote')->references('id')->on('lote')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
