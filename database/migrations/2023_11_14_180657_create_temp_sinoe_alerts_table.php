<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempSinoeAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_sinoe_alerts', function (Blueprint $table) {
            $table->id();
            $table->integer('id_exp');
            $table->string('n_expediente');
            $table->string('entidad')->nullable();
            $table->string('uid');
            $table->string('fecha_hora');
            $table->integer('id_ult_movi');
            $table->text('update_information')->nullable();
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
        Schema::dropIfExists('temp_sinoe_alerts');
    }
}
