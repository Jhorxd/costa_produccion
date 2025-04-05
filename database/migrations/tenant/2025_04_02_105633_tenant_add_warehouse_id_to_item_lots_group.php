<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddWarehouseIdToItemLotsGroup extends Migration
{
    public function up()
    {
        Schema::table('item_lots_group', function (Blueprint $table) {
            $table->unsignedInteger('warehouse_id')->after('item_id')->nullable();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
        });
    }

    public function down()
    {
        Schema::table('item_lots_group', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
    }
}
