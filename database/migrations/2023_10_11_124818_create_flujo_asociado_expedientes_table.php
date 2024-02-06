<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlujoAsociadoExpedientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flujo_asociado_expedientes', function (Blueprint $table) {
            $table->id();
            $table->string('estado')->nullable();
            $table->integer('id_exp')->nullable();
            $table->integer('id_workflow')->nullable();
            $table->integer('id_workflow_stage')->nullable();
            $table->dateTime('date_time')->nullable();
            $table->string('code_user')->nullable();
            $table->string('code_company')->nullable();

            $table->string('etapa')->nullable();
            $table->string('condicion')->nullable();
            $table->string('estado_transition')->nullable();
            $table->string('table_pertenece')->nullable();

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
        Schema::dropIfExists('flujo_asociado_expedientes');
    }
}
