<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('faturas', function (Blueprint $table) {
        $table->date('periodo_fatura')->nullable()->after('cliente_id'); // formato YYYY-MM
    });
}

public function down()
{
    Schema::table('faturas', function (Blueprint $table) {
        $table->dropColumn('periodo_fatura');
    });
}

};
