<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskSupremasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_supremas', function (Blueprint $table) {
            $table->id();
            $table->string('flujo_activo')->nullable();
            $table->integer('id_tarea_flujo')->nullable();
            $table->integer('etapa_flujo')->nullable();
            $table->integer('transicion_flujo')->nullable();
            $table->integer('data_flujo')->nullable();
            $table->integer('id_exp')->nullable();
            $table->string('nombre')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('prioridad')->nullable();
            $table->string('estado')->nullable();
            $table->date('fecha_limite')->nullable();
            $table->date('fecha_alerta')->nullable();
            $table->dateTime('fecha_finalizada')->nullable();
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
        Schema::dropIfExists('task_supremas');
    }
}
