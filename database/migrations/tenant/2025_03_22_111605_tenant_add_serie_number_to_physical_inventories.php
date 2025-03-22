<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddSerieNumberToPhysicalInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('physical_inventories', function (Blueprint $table) {
            $table->char('series', 4)->nullable(false); 
            $table->integer('number')->length(11)->nullable(false); 
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
            $table->dropColumn(['series', 'number']);
        });
    }
}
