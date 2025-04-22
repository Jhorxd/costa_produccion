<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantChangeColumnSalePriceToItems extends Migration
{
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('sale_price');
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('sale_price', 8, 2)->nullable();
        });
    }
}