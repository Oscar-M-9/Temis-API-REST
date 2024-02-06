<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentTaskSupremasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment_task_supremas', function (Blueprint $table) {
            $table->id();
            $table->text('comment')->nullable();
            $table->text('code_user');
            $table->text('code_company');
            $table->integer('id_exp');
            $table->integer('id_task');
            $table->dateTime('date');
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
        Schema::dropIfExists('comment_task_supremas');
    }
}
