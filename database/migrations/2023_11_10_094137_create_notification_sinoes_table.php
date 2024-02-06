<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationSinoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_sinoes', function (Blueprint $table) {
            $table->id();
            $table->string("tipo")->nullable();
            $table->string("n_notificacion")->nullable();
            $table->string("n_expediente")->nullable();
            $table->text("sumilla")->nullable();
            $table->text("oj")->nullable();
            $table->dateTime("fecha")->nullable();
            $table->string("id_exp")->nullable();
            $table->string("uid_credenciales_sinoe")->nullable();
            $table->dateTime("update_date")->nullable();
            $table->string("abog_virtual")->nullable();
            $table->text('u_tipo')->nullable();
            $table->text('u_title')->nullable();
            $table->text('u_date')->nullable();
            $table->text('u_descripcion')->nullable();
            $table->text('metadata')->nullable();
            $table->text('documento')->nullable();
            $table->text('video')->nullable();
            $table->string("code_user");
            $table->string("code_company");
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
        Schema::dropIfExists('notification_sinoes');
    }
}
