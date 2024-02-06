<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialDocumentosSinoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_documentos_sinoes', function (Blueprint $table) {
            $table->id();
            $table->string("n_expediente")->nullable();
            $table->integer("id_exp")->nullable();
            $table->string("n_escrito")->nullable();
            $table->string("distrito_judicial")->nullable();
            $table->string("organo_juris")->nullable();
            $table->string("tipo_doc")->nullable();
            $table->dateTime("fecha_presentacion")->nullable();
            $table->text("sumilla")->nullable();
            $table->text("file_doc")->nullable();
            $table->text("file_cargo")->nullable();
            $table->text("metadata")->nullable();
            $table->text("code_company")->nullable();
            $table->text("code_user")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historial_documentos_sinoes');
    }
}
