<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantDocumentResponseTable extends Migration
{
    public function up()
    {
        Schema::create('document_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('document_id');
            $table->string('tipo_de_comprobante', 10)->nullable();
            $table->string('serie', 20)->nullable();
            $table->string('numero', 20)->nullable();
            $table->text('enlace')->nullable();
            $table->tinyInteger('aceptada_por_sunat')->nullable();
            $table->text('sunat_description')->nullable();
            $table->text('sunat_note')->nullable();
            $table->string('sunat_responsecode', 10)->nullable();
            $table->text('sunat_soap_error')->nullable();
            $table->longText('pdf_zip_base64')->nullable();
            $table->longText('xml_zip_base64')->nullable();
            $table->longText('cdr_zip_base64')->nullable();
            $table->text('enlace_del_pdf')->nullable();
            $table->text('enlace_del_xml')->nullable();
            $table->text('enlace_del_cdr')->nullable();
            $table->string('codigo_hash', 255)->nullable();
            $table->string('cadena_para_codigo_qr', 255)->nullable();
            $table->longText('respuesta_json')->nullable();
            $table->timestamps();

            $table->foreign('document_id')
                  ->references('id')
                  ->on('documents')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_responses');
    }
}
