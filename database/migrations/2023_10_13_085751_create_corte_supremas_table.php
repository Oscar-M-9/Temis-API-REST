<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorteSupremasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corte_supremas', function (Blueprint $table) {
            $table->id();
            $table->text('n_expediente');
            $table->text('instancia');
            $table->text('recurso_sala');
            $table->dateTime('fecha_ingreso');
            $table->text('organo_procedencia');
            $table->text('relator');
            $table->text('distrito_judicial');
            $table->text('numero_procedencia');
            $table->text('secretario');
            $table->text('delito');
            $table->text('ubicacion');
            $table->text('estado');
            $table->date('update_date')->nullable();
            $table->text('url_suprema')->nullable();
            //
            $table->string('state')->nullable();
            $table->date('date_state')->nullable();
            //
            $table->text('partes_procesales')->nullable();
            $table->text('vista_causas')->nullable();
            $table->text('abogado_virtual')->nullable();
            $table->text('entidad')->nullable();
            $table->text('code_user')->nullable();
            $table->text('code_company')->nullable();
            $table->text('metadata')->nullable();
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
        Schema::dropIfExists('corte_supremas');
    }
}
