<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreatePhysicalInventoryCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('physical_inventory_categories', function (Blueprint $table) {
            $table->increments('id');  // Clave primaria con auto-incremento
            $table->string('name')->unique()->nullable(); // Permitir valores nulos en name
        });
        DB::table('physical_inventory_categories')->insert([
            ['name' => 'Errores conteo'],        
            ['name' => 'Daño'],
            ['name' => 'Pérdida'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('physical_inventory_categories');
    }
}
