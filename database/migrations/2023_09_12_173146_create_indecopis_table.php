<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndecopisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indecopis', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->string('numero');
            $table->string('oficina');
            $table->string('responsable');
            $table->string('via_presentacion');
            $table->date('fecha_inicio');
            $table->string('estado');
            $table->date('fecha');
            $table->string('forma_conclusion')->nullable();
            $table->text('partes_procesales1');
            $table->text('partes_procesales2');
            $table->text('acciones_realizadas')->nullable();
            $table->string('state');
            $table->date('date_state');
            $table->string('i_entidad');
            $table->string('entidad');
            $table->string('abogado_virtual');
            $table->unsignedBigInteger('id_client');
            $table->foreign('id_client')
				  ->references('id')
                  ->on('clientes');
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
        Schema::dropIfExists('indecopis');
    }
}
