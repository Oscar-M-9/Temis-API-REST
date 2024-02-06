<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentSupremasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment_supremas', function (Blueprint $table) {
            $table->id();
            $table->text('comment')->nullable();
            $table->text('code_user')->nullable();
            $table->text('code_company')->nullable();
            $table->integer('id_exp')->nullable();
            $table->integer('id_user')->nullable();
            $table->integer('id_seguimiento')->nullable();
            $table->dateTime('date')->nullable();
            $table->text('type')->nullable();
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
        Schema::dropIfExists('comment_supremas');
    }
}
