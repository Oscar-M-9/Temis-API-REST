<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificacionSeguimientosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notificacion_seguimientos', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('destinatario')->nullable();
            $table->dateTime('fecha_envio')->nullable();
            $table->string('anexos')->nullable();
            $table->string('forma_entrega')->nullable();
            $table->string('abog_virtual')->nullable();
            $table->text('metadata')->nullable();
            $table->string('code_company')->nullable();
            $table->string('code_user')->nullable();
            $table->integer('id_exp')->nullable();
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
        Schema::dropIfExists('notificasion_seguimientos');
    }
}
