<?php
// database/migrations/YYYY_MM_DD_HHMMSS_create_faturamento_periodos_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faturamento_periodos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->string('periodo', 7); // Formato YYYY-MM
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('public.empresa');
            $table->unique(['cliente_id', 'periodo']); // Garante um registro por cliente/período
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faturamento_periodos');
    }
};