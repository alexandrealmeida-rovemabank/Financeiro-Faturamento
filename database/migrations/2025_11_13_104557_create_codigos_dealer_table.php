<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // ...
    public function up(): void
    {
        Schema::create('codigos_dealer', function (Blueprint $table) {
            $table->id();
            
            // Adicione ->nullable()
            $table->string('cod_dealer')->nullable(); 

            $table->string('cnpj')->nullable()->index(); 
            $table->timestamps();
        });
    }
    // ...

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codigos_dealer');
    }
};