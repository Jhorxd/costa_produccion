<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddColumnLotsGroupIdToItemPositions extends Migration
{
    public function up()
    {
        Schema::table('item_positions', function (Blueprint $table) {
            $table->unsignedInteger('lots_group_id')->after('stock')->nullable();
            $table->foreign('lots_group_id')->references('id')->on('item_lots_group');
        });
    }

    public function down()
    {
        Schema::table('item_positions', function (Blueprint $table) {
            $table->dropForeign(['lots_group_id']);
            $table->dropColumn('lots_group_id');
        });
    }
}
