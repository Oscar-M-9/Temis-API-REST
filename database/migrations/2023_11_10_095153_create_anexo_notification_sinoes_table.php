<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnexoNotificationSinoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('anexo_notification_sinoes', function (Blueprint $table) {
            $table->id();
            $table->string("tipo");
            $table->text("identificacion");
            $table->integer("n_paginas");
            $table->string("documento");
            $table->string("id_exp");
            $table->string("id_notification");
            $table->string("abog_virtual");
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
        Schema::dropIfExists('anexo_notification_sinoes');
    }
}
