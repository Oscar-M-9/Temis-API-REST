<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempDocumentPresentadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_document_presentados', function (Blueprint $table) {
            $table->id();
            $table->string("id_exp");
            $table->string("uid");
            $table->string("n_expediente");
            $table->string("entidad");
            $table->string('estado')->nullable();
            $table->text('metadata')->nullable();
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
        Schema::dropIfExists('temp_document_presentados');
    }
}
