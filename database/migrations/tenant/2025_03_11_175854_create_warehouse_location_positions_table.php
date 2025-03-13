<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseLocationPositionsTable extends Migration
{

    public function up()
    {
        Schema::create('warehouse_location_positions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('location_id');
            $table->integer('row');
            $table->integer('column');
            $table->string('status');
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('inventory_warehouse_locations');
        });
    }

    public function down()
    {
        Schema::dropIfExists('warehouse_location_positions');
    }
}
