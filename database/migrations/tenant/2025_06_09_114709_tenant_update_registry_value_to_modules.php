<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantUpdateRegistryValueToModules extends Migration
{
    public function up()
    {
        Schema::table('modules', function (Blueprint $table) {
            //
        });

        DB::table('modules')
        ->where('value', 'items')
        ->update(['description' => 'Productos']);
    }
    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            //
        });
    }
}
