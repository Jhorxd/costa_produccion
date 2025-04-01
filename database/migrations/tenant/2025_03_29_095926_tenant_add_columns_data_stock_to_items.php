<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddColumnsDataStockToItems extends Migration
{
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->integer('stock_max')->nullable()->after('stock_min');
            $table->integer('average_usage')->nullable()->after('stock_max');
            $table->integer('days_to_alert')->nullable()->after('average_usage');
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('stock_max');
            $table->dropColumn('average_usage');
            $table->dropColumn('days_to_alert');
        });
    }
}
