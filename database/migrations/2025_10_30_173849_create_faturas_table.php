<?php

// database/migrations/YYYY_MM_DD_HHMMSS_create_faturas_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faturas', function (Blueprint $table) {
            $table->id();
            
            // Chave estrangeira para o cliente (da public.empresa)
            // Usamos unsignedBigInteger para referenciar 'id' (bigserial) do PostgreSQL
            $table->unsignedBigInteger('cliente_id'); 
            
            $table->string('numero_fatura')->nullable(); // Pode ser gerado depois
            $table->decimal('valor_total', 15, 2);
            $table->decimal('valor_impostos', 15, 2)->default(0.00);
            $table->decimal('valor_descontos', 15, 2)->default(0.00);
            $table->decimal('valor_liquido', 15, 2);

            $table->date('data_emissao');
            $table->date('data_vencimento');
            
            $table->string('status', 50)->default('pendente'); // ex: pendente, paga, cancelada, atrasada

            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('public.empresa');
            $table->index('status');
            $table->index('data_vencimento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faturas');
    }
};