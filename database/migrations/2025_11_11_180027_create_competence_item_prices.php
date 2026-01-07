<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetenceItemPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competence_item_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('competence_item_id');
            $table->string('unit_type_id');
            $table->integer('factor')->nullable();
            $table->decimal('competence_unit_price1', 12, 4)->default(0);
            $table->string('competence_label1')->nullable();
            $table->unsignedInteger('competence_id1')->nullable();
            $table->decimal('competence_unit_price2', 12, 4)->default(0);
            $table->string('competence_label2')->nullable();
            $table->unsignedInteger('competence_id2')->nullable();
            $table->decimal('competence_unit_price3', 12, 4)->default(0);
            $table->string('competence_label3')->nullable();
            $table->unsignedInteger('competence_id3')->nullable();
            $table->decimal('competence_unit_price4', 12, 4)->default(0);
            $table->string('competence_label4')->nullable();
            $table->unsignedInteger('competence_id4')->nullable();
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
        Schema::dropIfExists('competence_item_prices');
    }
}
