<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlertClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alert_clientes', function (Blueprint $table) {
            $table->id();
            $table->integer('id_client');
            $table->date('fecha_limite');
            $table->text('titulo');
            $table->text('descripcion');
            $table->text('code_company');
            $table->text('code_user');
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
        Schema::dropIfExists('alert_clientes');
    }
}
