<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddLengthWidthHeightResponsibleAddressColumnsToWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            //
            $table->decimal('length', 8, 2)->after('description')->nullable(); // Longitud
            $table->decimal('width', 8, 2)->after('length')->nullable(); // Ancho
            $table->decimal('height', 8, 2)->after('width')->nullable(); // Altura
            $table->string('responsible')->after('height')->nullable(); // Persona responsable
            $table->string('address')->after('responsible')->nullable(); // Dirección del almacén
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            //
            $table->dropColumn(['length', 'width', 'height', 'responsible', 'address']);

        });
    }
}
