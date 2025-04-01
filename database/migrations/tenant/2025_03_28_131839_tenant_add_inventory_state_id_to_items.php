<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddInventoryStateIdToItems extends Migration
{
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->integer('inventory_state_id')->after('status');
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('inventory_state_id');
        });
    }
}
