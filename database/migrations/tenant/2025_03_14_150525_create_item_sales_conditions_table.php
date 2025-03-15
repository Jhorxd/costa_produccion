<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateItemSalesConditionsTable extends Migration
{
    public function up()
    {
        Schema::create('item_sales_conditions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->timestamps();
        });

        DB::table('item_sales_conditions')->insert([
            ['id' => 1, 'description' => 'Sin Receta Médica' ],
            ['id' => 2, 'description' => 'Con Receta Médica' ],
            ['id' => 3, 'description' => 'Con Receta Médica Retenida']
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('item_sales_conditions');
    }
}
