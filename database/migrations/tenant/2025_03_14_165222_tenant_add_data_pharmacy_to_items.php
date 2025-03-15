<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddDataPharmacyToItems extends Migration
{
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('active_principle')->nullable();
            $table->string('concentration')->nullable();
            $table->unsignedInteger('sales_condition_id')->nullable();
            $table->foreign('sales_condition_id')->references('id')->on('item_sales_conditions');
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('active_principle');
            $table->dropColumn('concentration');
            $table->dropColumn('sales_condition_id');
        });
    }
}
