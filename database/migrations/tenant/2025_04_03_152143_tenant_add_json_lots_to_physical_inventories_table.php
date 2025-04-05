<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddJsonLotsToPhysicalInventoriesTable extends Migration
{
    public function up()
    {
        Schema::table('physical_inventories', function (Blueprint $table) {
            $table->json('json_lots')->nullable()->after('json_positions');
        });
    }

    public function down()
    {
        Schema::table('physical_inventories', function (Blueprint $table) {
            $table->dropColumn('json_lots');
        });
    }
}
