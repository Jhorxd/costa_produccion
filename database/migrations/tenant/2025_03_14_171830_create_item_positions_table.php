<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemPositionsTable extends Migration
{
    public function up()
    {
        Schema::create('item_positions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('stock');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('position_id');
            $table->unsignedInteger('location_id');
            $table->unsignedInteger('warehouse_id');
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('position_id')->references('id')->on('warehouse_location_positions');
            $table->foreign('location_id')->references('id')->on('inventory_warehouse_locations');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_positions');
    }
}
