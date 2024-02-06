<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history_movements', function (Blueprint $table) {
            $table->id();
            $table->integer('id_movimiento');
            $table->integer('id_exp');
            $table->integer('id_client');
            $table->string('entidad');
            $table->string('estado');
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
        Schema::dropIfExists('history_movements');
    }
}
