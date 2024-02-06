<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowUpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->integer('n_seguimiento');
            $table->dateTime('fecha_ingreso')->nullable();
            $table->date('fecha_resolucion')->nullable();
            $table->string('resolucion')->nullable();
            $table->string('type_notificacion')->nullable();
            $table->string('acto')->nullable();
            $table->integer('folios')->nullable();
            $table->integer('fojas')->nullable();
            $table->date('proveido')->nullable();
            $table->text('obs_sumilla')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('file')->nullable();
            $table->text('noti')->nullable();
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
                  ->on('expedientes');
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
        Schema::dropIfExists('follow_ups');
    }
}
