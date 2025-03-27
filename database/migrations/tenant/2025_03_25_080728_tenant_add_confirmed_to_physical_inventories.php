<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddConfirmedToPhysicalInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('physical_inventories', function (Blueprint $table) {
            $table->boolean('confirmed')->nullable()->after('comment');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('physical_inventories', function (Blueprint $table) {
            $table->dropColumn('confirmed');
        });
    }
}
