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
        Schema::create('lote_impressao', function (Blueprint $table) {
            $table->id();
            $table->string('lote')->unique();
            $table->string('cliente');
            $table->string('data_importacao');
            $table->string('data_alteracao');
            $table->string('status_impressao');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lote_impressao');
    }
};
