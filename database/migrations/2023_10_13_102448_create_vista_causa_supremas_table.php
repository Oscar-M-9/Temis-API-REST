<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVistaCausaSupremasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vista_causa_supremas', function (Blueprint $table) {
            $table->id();
            $table->integer('n_vista')->nullable();
            $table->dateTime('fecha_vista')->nullable();
            $table->date('fecha_programacion')->nullable();
            $table->text('sentido_resultado')->nullable();
            $table->text('observacion')->nullable();
            $table->text('tipo_vista')->nullable();
            $table->text('abog_virtual')->nullable();

            $table->text('metadata')->nullable();
            $table->string('code_company')->nullable();
            $table->string('code_user')->nullable();
            $table->date('update_date')->nullable();
            $table->unsignedBigInteger('id_exp');
            $table->foreign('id_exp')
				  ->references('id')
                  ->on('corte_supremas');
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
        Schema::dropIfExists('vista_causa_supremas');
    }
}
