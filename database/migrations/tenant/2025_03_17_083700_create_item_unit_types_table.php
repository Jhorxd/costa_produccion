<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemUnitTypesTable extends Migration
{
    public function up()
    {
        Schema::create('pharmaceutical_item_unit_types', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('active')->default(1);
            $table->string('description', 60);
            $table->timestamps();
        });

        DB::table('pharmaceutical_item_unit_types')->insert([
            ['id' => 1, 'description' => 'Tabletas' ],
            ['id' => 2, 'description' => 'Cápsulas' ],
            ['id' => 3, 'description' => 'Suspensiones'],
            ['id' => 4, 'description' => 'Jarabes'],
            ['id' => 5, 'description' => 'Gotas'],
            ['id' => 6, 'description' => 'Crema'],
            ['id' => 7, 'description' => 'Pomada'],
            ['id' => 8, 'description' => 'Loción'],
            ['id' => 9, 'description' => 'Inyecciones'],
            ['id' => 10, 'description' => 'Inhaladores'],
            ['id' => 11, 'description' => 'Parche trasdérmico'],
            ['id' => 12, 'description' => 'Solución'],
            ['id' => 13, 'description' => 'Espuma']
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('pharmaceutical_item_unit_types');
    }
}
