<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentNumberSequencesTable extends Migration
{

    public function up()
    {
        Schema::create('document_number_sequences', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('serie');
            $table->unsignedInteger('next_number')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_number_sequences');
    }
}
