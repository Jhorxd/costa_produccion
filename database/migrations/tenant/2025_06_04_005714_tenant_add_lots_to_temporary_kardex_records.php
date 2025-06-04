<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddLotsToTemporaryKardexRecords extends Migration
{
    public function up()
    {
        Schema::table('temporary_kardex_records', function (Blueprint $table) {
            $table->text('lots')->nullable();
        });
    }

    public function down()
    {
        Schema::table('temporary_kardex_records', function (Blueprint $table) {
            $table->dropColumn('lots');
        });
    }
}
