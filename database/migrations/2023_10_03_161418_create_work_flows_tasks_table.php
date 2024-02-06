<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkFlowsTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_flows_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->nullable();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->integer('dias_duracion')->nullable();
            $table->integer('dias_antes_venc')->nullable();
            $table->text('attached_files')->nullable();
            $table->text('estado')->nullable();
            $table->text('prioridad')->nullable();
            $table->integer('id_workflow');
            $table->integer('id_workflow_stage');
            $table->text('code_user');
            $table->text('code_company');
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
        Schema::dropIfExists('work_flows_tasks');
    }
}
