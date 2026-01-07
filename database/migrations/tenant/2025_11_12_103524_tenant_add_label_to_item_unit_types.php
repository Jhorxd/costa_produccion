<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddLabelToItemUnitTypes extends Migration
{
    public function up()
    {
        Schema::table('item_unit_types', function (Blueprint $table) {
            $table->decimal('price4', 12, 2)->nullable();
            $table->decimal('price5', 12, 2)->nullable();;
            $table->string('label1')->nullable();
            $table->string('label2')->nullable();
            $table->string('label3')->nullable();
            $table->string('label4')->nullable();
            $table->string('label5')->nullable();
        });
    }

    public function down()
    {
        Schema::table('item_unit_types', function (Blueprint $table) {
            $table->dropColumn('price4');
            $table->dropColumn('price5');
            $table->dropColumn('label1');
            $table->dropColumn('label2');
            $table->dropColumn('label3');
            $table->dropColumn('label4');
            $table->dropColumn('label5');
        });
    }
}
