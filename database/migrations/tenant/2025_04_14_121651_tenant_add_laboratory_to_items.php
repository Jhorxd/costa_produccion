<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddLaboratoryToItems extends Migration
{
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('laboratory')->nullable()->after('model');
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('laboratory');
        });
    }
}
