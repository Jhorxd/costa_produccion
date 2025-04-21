<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddJsonPositionToInventoryPhysicalDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_physical_details', function (Blueprint $table) {
            $table->json('json_position')->nullable()->after('cost');  // Agregamos la columna después de 'cost'
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
            $table->dropColumn('json_position');
        });
    }
}
