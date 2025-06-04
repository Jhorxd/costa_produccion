<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTokenApirucToConfigurations extends Migration
{
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            DB::table('configurations')->update([
                'id' => '1',
                'url_apiruc' => '',
                'token_apiruc' => 'false'
            ]);
        });
    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            //
        });
    }
}
