<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhysicalInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('physical_inventories', function (Blueprint $table) {
            $table->increments('id'); // Clave primaria con autoincremento
            $table->date('date')->nullable(); // Fecha del ajuste
            
            // Tipo de ajuste (ajuste selectivo o global)
            $table->unsignedInteger('adjustment_type_id');
            $table->foreign('adjustment_type_id')->references('id')->on('physical_inventory_adjustment_types');

            // Sucursal
            $table->unsignedInteger('establishment_id');
            $table->foreign('establishment_id')->references('id')->on('establishments');

            // Almacén
            $table->unsignedInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->string('comment')->nullable(); // Comentario opcional
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
        Schema::dropIfExists('physical_inventories');
    }
}
