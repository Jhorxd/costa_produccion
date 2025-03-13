<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseLocationTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_warehouse_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('warehouse_id');
            //$table->foreign('warehouse_id')->references('id')->on('cash');
            $table->unsignedInteger('type_id');
            $table->foreign('type_id')->references('id')->on('warehouse_location_type');
            $table->string('name', 30);
            $table->string('code', 10);
            $table->integer('status');
            $table->integer('rows');
            $table->integer('columns');
            $table->integer('maximum_stock');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_warehouse_locations');
    }
}
