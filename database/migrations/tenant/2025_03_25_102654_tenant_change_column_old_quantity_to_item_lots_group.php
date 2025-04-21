<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantChangeColumnOldQuantityToItemLotsGroup extends Migration
{
    public function up()
    {
        Schema::table('item_lots_group', function (Blueprint $table) {
            $table->decimal('old_quantity', 12, 4)->nullable()->default(null)->change();
        });
    }

    public function down()
    {
        Schema::table('item_lots_group', function (Blueprint $table) {
            $table->decimal('old_quantity', 12, 4)->default(0)->nullable(false)->change();
        });
    }
}