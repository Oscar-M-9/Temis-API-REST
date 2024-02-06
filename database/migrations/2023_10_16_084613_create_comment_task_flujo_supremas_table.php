<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentTaskFlujoSupremasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment_task_flujo_supremas', function (Blueprint $table) {
            $table->id();
            $table->text('comment')->nullable();
            $table->integer('id_exp')->nullable();
            $table->integer('id_task')->nullable();
            $table->integer('id_flujo')->nullable();
            $table->integer('id_stage')->nullable();
            $table->dateTime('date');
            $table->text('entidad');
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
        Schema::dropIfExists('comment_task_flujo_supremas');
    }
}
