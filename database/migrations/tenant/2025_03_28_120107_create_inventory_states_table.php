<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateInventoryStatesTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_states', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->boolean('is_available_to_sale');
            $table->timestamps();
        });

        DB::table('inventory_states')->insert([
            ['id' => 1, 'name' => 'Available', 'description' => 'Disponible', 'is_available_to_sale' => true],
            ['id' => 2, 'name' => 'Unavailable', 'description' => 'No disponible', 'is_available_to_sale' => false],
            ['id' => 3, 'name' => 'Blocked', 'description' => 'Bloqueado', 'is_available_to_sale' => false],
            ['id' => 4, 'name' => 'Damaged', 'description' => 'Dañado', 'is_available_to_sale' => false],
            ['id' => 5, 'name' => 'Quarantine', 'description' => 'En cuarentena', 'is_available_to_sale' => false],
            ['id' => 6, 'name' => 'Reserved', 'description' => 'Reservado', 'is_available_to_sale' => false],
            ['id' => 7, 'name' => 'In Transit', 'description' => 'En tránsito', 'is_available_to_sale' => false],
            ['id' => 8, 'name' => 'Pending Receipt', 'description' => 'Pendiente de recepción', 'is_available_to_sale' => false],
            ['id' => 9, 'name' => 'In Processing', 'description' => 'En procesamiento', 'is_available_to_sale' => false],
            ['id' => 10, 'name' => 'Returned', 'description' => 'Devuelto', 'is_available_to_sale' => false],
            ['id' => 11, 'name' => 'Picking', 'description' => 'En picking', 'is_available_to_sale' => false],
            ['id' => 12, 'name' => 'Packing', 'description' => 'En embalaje', 'is_available_to_sale' => false],
            ['id' => 13, 'name' => 'Dispatching', 'description' => 'En despacho', 'is_available_to_sale' => false],
            ['id' => 14, 'name' => 'Obsolete', 'description' => 'Obsoleto', 'is_available_to_sale' => false],
            ['id' => 15, 'name' => 'Under Review', 'description' => 'Stock bajo revisión', 'is_available_to_sale' => false],
            ['id' => 16, 'name' => 'Preallocated', 'description' => 'Preasignado', 'is_available_to_sale' => false],
            ['id' => 17, 'name' => 'Short Expiry', 'description' => 'Corta Caducidad', 'is_available_to_sale' => false],
            ['id' => 18, 'name' => 'Out of Stock', 'description' => 'Fuera de inventario', 'is_available_to_sale' => false],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('inventory_states');
    }
}
