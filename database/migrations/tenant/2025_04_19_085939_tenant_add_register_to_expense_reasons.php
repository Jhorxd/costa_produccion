<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantAddRegisterToExpenseReasons extends Migration
{
    public function up()
    {
        Schema::table('expense_reasons', function (Blueprint $table) {
            DB::table('expense_reasons')->insert([
                ['id' => '4', 'description' => 'Devolución Clientes']
            ]);
        });
    }

    public function down()
    {
        Schema::table('expense_reasons', function (Blueprint $table) {
            //
        });
    }
}
