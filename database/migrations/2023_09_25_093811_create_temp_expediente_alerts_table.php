<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempExpedienteAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_expediente_alerts', function (Blueprint $table) {
            $table->id();
            $table->integer('id_exp');
            $table->string('n_expediente');
            $table->string('entidad')->nullable();
            $table->string('date_ult_movi');
            $table->string('title_ult_movi');
            $table->integer('id_ult_movi');
            $table->integer('n_ult_movi');
            $table->text('update_information')->nullable();
            $table->text('data_last');
            $table->text('data_pending');
            $table->text('ids_pending');
            $table->string('estado')->nullable();
            $table->text('metadata')->nullable();
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
        Schema::dropIfExists('temp_expediente_alerts');
    }
}
