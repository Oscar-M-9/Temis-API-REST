<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpedienteEntidadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expediente_entidads', function (Blueprint $table) {
            $table->id();
            $table->string('code_company')->nullable();
            $table->string('code_user')->nullable();
            $table->string('entidad')->nullable();
            // $table->unsignedBigInteger('id_client');
            // $table->foreign('id_client')
			// 	  ->references('id')
            //       ->on('clientes');
            $table->string('id_client')->nullable();
            // $table->unsignedBigInteger('id_exp');
            // $table->foreign('id_exp')
			// 	  ->references('id')
            //       ->on('expedientes');
            $table->string('id_exp')->nullable();
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
        Schema::dropIfExists('expediente_entidads');
    }
}
