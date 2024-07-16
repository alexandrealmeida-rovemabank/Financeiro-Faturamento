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
        Schema::create('terminal_vinculado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_estoque');
            $table->unsignedBigInteger('id_credenciado');
            $table->string('chip');
            $table->string('produto');
            $table->string('status');
            $table->string('sistema')->nullable();
            $table->timestamps();
            $table->foreign('id_estoque')->references('id')->on('estoque')->onDelete('cascade');
            $table->foreign('id_credenciado')->references('id')->on('credenciado')->onDelete('cascade');
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
