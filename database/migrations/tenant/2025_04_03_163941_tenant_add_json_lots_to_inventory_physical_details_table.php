<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddJsonLotsToInventoryPhysicalDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('inventory_physical_details', function (Blueprint $table) {
            $table->json('json_lots')->nullable()->after('json_position');
        });
    }

    public function down()
    {
        Schema::table('inventory_physical_details', function (Blueprint $table) {
            $table->dropColumn('json_lots');
        });
    }
}
