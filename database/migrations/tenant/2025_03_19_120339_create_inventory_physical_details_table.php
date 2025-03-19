<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryPhysicalDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_physical_details', function (Blueprint $table) {
            $table->increments('id'); // Primary key
            $table->unsignedInteger('physical_inventory_id'); // Foreign key to physical_inventories
            $table->unsignedInteger('item_id'); // Referencia al ítem
            $table->integer('counted_quantity'); // Manually counted quantity
            $table->integer('system_quantity'); // System quantity
            $table->integer('difference'); // Difference between counted and system quantity
            $table->unsignedInteger('category_id')->nullable(); // Category (nullable if using global category)

            // Foreign key constraints
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('physical_inventory_id')->references('id')->on('physical_inventories')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('physical_inventory_categories')->onDelete('set null');
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_physical_details');
    }
}
