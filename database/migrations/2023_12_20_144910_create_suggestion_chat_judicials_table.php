<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuggestionChatJudicialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suggestion_chat_judicials', function (Blueprint $table) {
            $table->id();
            $table->integer("id_movi")->nullable();
            $table->integer("id_exp")->nullable();
            $table->string("code_exp")->nullable();
            $table->text("chat_user")->nullable();
            $table->text("prompt")->nullable();
            $table->string("entidad")->nullable();
            $table->string("estado")->nullable();
            $table->text("code_user")->nullable();
            $table->text("code_company")->nullable();
            $table->text("metadata")->nullable();
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
        Schema::dropIfExists('suggestion_chat_judicials');
    }
}
