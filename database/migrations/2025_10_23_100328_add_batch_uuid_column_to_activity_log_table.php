<?php

// Migration "..._add_batch_uuid_column_to_activity_log_table.php"

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// O nome da classe pode variar ligeiramente, use o que foi gerado no seu arquivo
class AddBatchUuidColumnToActivityLogTable extends Migration
{
    public function up()
    {
        // Usa a conexão e o nome da tabela configurados
        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name'), function (Blueprint $table) {
            $table->uuid('batch_uuid')->nullable()->after('properties'); // Apenas adiciona a coluna
        });
    }

    public function down()
    {
        Schema::connection(config('activitylog.database_connection'))->table(config('activitylog.table_name'), function (Blueprint $table) {
            $table->dropColumn('batch_uuid');
        });
    }
}