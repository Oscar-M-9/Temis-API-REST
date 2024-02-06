<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentosPresentadosSinoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documentos_presentados_sinoes', function (Blueprint $table) {
            $table->id();
            $table->integer("id_exp")->nullable();
            $table->integer("id_historial")->nullable();
            $table->string("descripcion")->nullable();
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
        Schema::dropIfExists('documentos_presentados_sinoes');
    }
}
