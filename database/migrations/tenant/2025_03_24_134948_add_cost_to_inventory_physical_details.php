<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCostToInventoryPhysicalDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_physical_details', function (Blueprint $table) {
            $table->decimal('cost', 16, 6)->nullable()->after('category_id');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_physical_details', function (Blueprint $table) {
            $table->dropColumn('cost');
        });
    }
}
