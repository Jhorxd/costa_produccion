<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddPositionDestinationIdToInventoriesTransfer extends Migration
{

    public function up()
    {
        Schema::table('inventories_transfer', function (Blueprint $table) {
            $table->unsignedInteger('location_destination_id')->after('warehouse_destination_id')->nullable();
            $table->unsignedInteger('position_destination_id')->after('location_destination_id')->nullable();
            $table->foreign('location_destination_id')->references('id')->on('inventory_warehouse_locations');
            $table->foreign('position_destination_id')->references('id')->on('warehouse_location_positions');
        });
    }

    public function down()
    {
        Schema::table('inventories_transfer', function (Blueprint $table) {
            $table->dropForeign(['location_destination_id']);
            $table->dropColumn('location_destination_id');
            $table->dropForeign(['position_destination_id']);
            $table->dropColumn('position_destination_id');
        });
    }
}
