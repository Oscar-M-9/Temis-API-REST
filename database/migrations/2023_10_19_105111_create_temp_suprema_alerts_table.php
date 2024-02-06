<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempSupremaAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_suprema_alerts', function (Blueprint $table) {
            $table->id();
            $table->integer('id_suprema');
            $table->string('n_expediente')->nullable();
            $table->string('entidad')->nullable();
            $table->integer('id_ult_movi')->nullable();
            $table->integer('count_movi')->nullable();
            $table->integer('n_ult_movi')->nullable();
            $table->text('vista_causa')->nullable();
            $table->text('ids_vista_causa')->nullable();
            $table->text('update_information')->nullable();
            $table->string('estado')->nullable();
            $table->text('url')->nullable();
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
        Schema::dropIfExists('temp_suprema_alerts');
    }
}
