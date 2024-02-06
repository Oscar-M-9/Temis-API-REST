<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpedientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->text('n_expediente');
            $table->string('o_jurisdicional');
            $table->string('d_judicial');
            $table->string('juez')->nullable();
            $table->string('ubicacion');
            $table->string('e_procesal');
            $table->text('sumilla');
            $table->string('proceso');
            $table->string('especialidad');
            $table->string('observacion');
            $table->string('estado');
            $table->text('materia');
            $table->string('demanding')->nullable();
            $table->string('defendant')->nullable();
            $table->string('lawyer_responsible');
            $table->date('update_date')->nullable();
            //
            $table->string('state')->nullable();
            $table->date('date_state')->nullable();
            //
            $table->date('date_initial')->nullable();
            $table->date('date_conclusion')->nullable();
            $table->text('motivo_conclusion')->nullable();
            $table->text('partes_procesales')->nullable();
            $table->text('abogado_virtual')->nullable();
            $table->text('entidad')->nullable();
            $table->text('code_user')->nullable();
            $table->text('code_company')->nullable();
            //
            $table->unsignedBigInteger('id_client');
            $table->foreign('id_client')
				  ->references('id')
                  ->on('clientes');
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
        Schema::dropIfExists('expedientes');
    }
}
