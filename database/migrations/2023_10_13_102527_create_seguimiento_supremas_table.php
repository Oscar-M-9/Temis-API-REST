<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeguimientoSupremasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seguimiento_supremas', function (Blueprint $table) {
            $table->id();
            $table->integer('n_seguimiento')->nullable();
            $table->date('fecha')->nullable();
            $table->text('acto')->nullable();
            $table->string('resolucion')->nullable();
            $table->integer('fojas')->nullable();
            $table->text('sumilla')->nullable();
            $table->text('desc_usuario')->nullable();
            $table->text('presentante')->nullable();
            $table->string('abog_virtual')->nullable();
            $table->text('u_tipo')->nullable();
            $table->text('u_title')->nullable();
            $table->text('u_date')->nullable();
            $table->text('u_descripcion')->nullable();
            $table->text('metadata')->nullable();
            $table->text('documento')->nullable();
            $table->text('video')->nullable();
            $table->string('code_company')->nullable();
            $table->string('code_user')->nullable();
            $table->date('update_date')->nullable();
            $table->unsignedBigInteger('id_exp');
            $table->foreign('id_exp')
				  ->references('id')
                  ->on('corte_supremas');
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
        Schema::dropIfExists('seguimiento_supremas');
    }
}
