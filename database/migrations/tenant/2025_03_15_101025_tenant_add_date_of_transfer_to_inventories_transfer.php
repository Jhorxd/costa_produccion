<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddDateOfTransferToInventoriesTransfer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventories_transfer', function (Blueprint $table) {
            //
            $table->date('date_of_transfer')->nullable()->after('filename');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventories_transfer', function (Blueprint $table) {
            //
            $table->dropColumn('date_of_transfer');
        });
    }
}
