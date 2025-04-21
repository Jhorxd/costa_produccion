<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddColumnStatusToItemLotsGroup extends Migration
{
    public function up()
    {
        Schema::table('item_lots_group', function (Blueprint $table) {
            $table->integer('status')->after('date_of_due');
        });
    }

    public function down()
    {
        Schema::table('item_lots_group', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
