<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddPharmaceuticalItemUnitTypesToItems extends Migration
{
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedInteger('pharmaceutical_unit_type_id')->after('unit_type_id');
            $table->foreign('pharmaceutical_unit_type_id')->references('id')->on('pharmaceutical_item_unit_types');
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['pharmaceutical_unit_type_id']);
            $table->dropColumn('pharmaceutical_unit_type_id');
        });
    }
}
