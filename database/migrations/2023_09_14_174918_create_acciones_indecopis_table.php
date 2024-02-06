<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccionesIndecopisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acciones_indecopis', function (Blueprint $table) {
            $table->id();
            $table->integer('n_accion');
            $table->date('fecha');
            $table->text('accion_realizada');
            $table->text('anotaciones')->nullable();
            $table->string('abog_virtual');
            $table->text('metadata')->nullable();
            $table->text('documento')->nullable();
            $table->text('video')->nullable();
            $table->string('code_user');
            $table->string('code_company');
            $table->date('update_date')->nullable();
            $table->integer('id_indecopi');
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
        Schema::dropIfExists('acciones_indecopis');
    }
}
