<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkFlowTaskExpedientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_flow_task_expedientes', function (Blueprint $table) {
            $table->id();
            $table->integer('id_workflow');
            $table->integer('id_workflow_stage');
            $table->integer('id_workflow_task');
            $table->integer('id_exp');
            $table->string('nombre_etapa')->nullable();
            $table->string('nombre_flujo')->nullable();
            $table->string('nombre')->nullable();
            $table->text('descripcion')->nullable();
            $table->integer('dias_duracion')->nullable();
            $table->integer('dias_antes_venc')->nullable();
            $table->date('fecha_limite')->nullable();
            $table->date('fecha_alerta')->nullable();
            $table->dateTime('fecha_finalizada')->nullable();
            $table->text('attached_files')->nullable();
            $table->string('estado')->nullable();
            $table->string('prioridad')->nullable();
            $table->string('code_user');
            $table->string('code_company');
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
        Schema::dropIfExists('work_flow_task_expedientes');
    }
}
