<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemFilesTable extends Migration
{
    public function up()
    {
        Schema::create('item_files', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id');
            $table->string('filename');
            $table->string('route');
            $table->string('user_created_at');
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items');
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_files');
    }
}
