<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateWarehouseLocationTypeTable extends Migration
{
    public function up()
    {
        Schema::create('warehouse_location_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        if (DB::table('warehouse_location_type')->get()->count() == 0) {
            DB::table('warehouse_location_type')->insert([
                ['id'=> 1, 'name' => 'Anaquel'],
                ['id'=> 2, 'name' => 'Estante'],
                ['id'=> 3, 'name' => 'Caja'],
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('warehouse_location_type');
    }
}
